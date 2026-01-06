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
        /* ===== MODAL SIZE ===== */
        .modal-fullscreen-width {
            width: 90vw;
            max-width: 90vw;

            /* fleksibel */
            max-height: 90vh;
            margin: auto;
        }
    </style>
@endsection

@section('content')
    <!-- Modal Detail -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-fullscreen-width">
            <div class="modal-content">

                <!-- Header -->
                <div class="modal-header bg-sb text-white">
                    <h5 class="modal-title" id="detailModalLabel">
                        <i class="fas fa-list"></i> Detail Data
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <!-- Body -->
                <div class="modal-body">
                    <form id="formDetailDefect">
                        <div class="row mb-3">
                            <div class="col-12 col-md-3">
                                <label class="form-label small fw-bold"> No. Transaksi</label>
                                <input type="text" id="txtno_form" name="txtno_form"
                                    class="form-control form-control-sm border-primary" readonly>
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label small fw-bold"> Supplier</label>
                                <input type="text" id="txtsupplier" name="txtsupplier"
                                    class="form-control form-control-sm border-primary" readonly>
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label small fw-bold"> Berat Set Panel</label>
                                <input type="text" id="txtberat_panel" name="txtberat_panel"
                                    class="form-control form-control-sm border-primary" readonly>
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label small fw-bold"> Berat Karung</label>
                                <input type="text" id="txtberat_karung" name="txtberat_karung"
                                    class="form-control form-control-sm border-primary" readonly>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <!-- BOX -->
                                <div class="border border-dark rounded">
                                    <!-- HEADER -->
                                    <div class="px-3 py-2 fw-bold text-dark border-bottom border-dark bg-light">
                                        Detail Transaksi
                                    </div>
                                    <!-- BODY -->
                                    <div class="p-2">
                                        <div class="table-responsive">
                                            <table id="datatable_modal_trans"
                                                class="table table-bordered table-hover align-middle text-nowrap w-100 mb-0">
                                                <thead class="table-dark">
                                                    <tr class="text-center align-middle">
                                                        <th>WS</th>
                                                        <th>Item Name</th>
                                                        <th>Qty</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <!-- END BOX -->
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <!-- BOX -->
                                <div class="border border-dark rounded">
                                    <!-- HEADER -->
                                    <div class="px-3 py-2 fw-bold text-dark border-bottom border-dark bg-light">
                                        Detail Stocker
                                    </div>
                                    <!-- BODY -->
                                    <div class="p-2">
                                        <div class="table-responsive">
                                            <table id="datatable_modal_stocker"
                                                class="table table-bordered table-hover align-middle text-nowrap w-100 mb-0">
                                                <thead class="table-dark">
                                                    <tr class="text-center align-middle">
                                                        <th>No. Karung</th>
                                                        <th>No. Stocker</th>
                                                        <th>WS</th>
                                                        <th>Style</th>
                                                        <th>Color</th>
                                                        <th>Size</th>
                                                        <th>Qty</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <!-- END BOX -->
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                        Close
                    </button>
                    {{-- <button class="btn btn-outline-success btn-sm" id="KonfButton" onclick="konfirmasi()">
                        Konfirmasi
                    </button> --}}
                </div>

            </div>
        </div>
    </div>

    <!-- Master Table -->
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> Loading Out / WIP Out</h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between mb-3">
                <a href="{{ route('input_loading_out') }}" class="btn btn-outline-primary position-relative btn-sm">
                    <i class="fas fa-plus"></i>
                    New
                </a>
            </div>
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Awal</b></small></label>
                    <input type="date" class="form-control form-control-sm " id="tgl-awal" name="tgl_awal"
                        value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Akhir</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir"
                        value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <a class="btn btn-outline-primary position-relative btn-sm"
                        onclick="dataTableReload();dataTableDetReload();">
                        <i class="fas fa-search"></i>
                        Cari
                    </a>
                </div>

            </div>

            <ul class="nav nav-tabs" id="tabMenu" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="list-tab" data-bs-toggle="tab" data-bs-target="#tabList"
                        type="button" role="tab">List</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="detail-tab" data-bs-toggle="tab" data-bs-target="#tabDetail"
                        type="button" role="tab">Detail</button>
                </li>
            </ul>

            <div class="tab-content" id="tabContent">
                <!-- TAB LIST -->
                <div class="tab-pane fade show active pt-3" id="tabList" role="tabpanel">
                    <div class="table-responsive">
                        <table id="datatable_list"
                            class="table table-bordered table-hover align-middle text-nowrap w-100">
                            <thead class="bg-sb">
                                <tr style="text-align:center; vertical-align:middle">
                                    <th>Act</th>
                                    <th>No. Trans</th>
                                    <th>Tgl Pengeluaran</th>
                                    <th>PO</th>
                                    <th>Supplier</th>
                                    <th>Jenis Dok</th>
                                    <th>Jenis Pengeluaran</th>
                                    <th>Total Qty</th>
                                    <th>Berat Panel</th>
                                    <th>Berat Karung</th>
                                    <th>Ket</th>
                                    <th>User</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>

                <!-- TAB DETAIL -->
                <div class="tab-pane fade pt-3" id="tabDetail" role="tabpanel">
                    <div class="table-responsive">
                        <table id="datatable_detail"
                            class="table table-bordered table-hover align-middle text-nowrap w-100">
                            <thead class="bg-sb">
                                <tr style="text-align:center; vertical-align:middle">
                                    <th>No. Trans</th>
                                    <th>Tgl Pengeluaran</th>
                                    <th>PO</th>
                                    <th>Supplier</th>
                                    <th>Qty</th>
                                    <th>Buyer</th>
                                    <th>WS</th>
                                    <th>Style</th>
                                    <th>Color</th>
                                    <th>Size</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
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
            dataTableReload();
        });

        $('button[data-bs-target="#tabDetail"]').on('shown.bs.tab', function() {
            datatableDetail.ajax.reload();
        });
        $('button[data-bs-target="#tabList"]').on('shown.bs.tab', function() {
            datatableList.ajax.reload();
        });

        // Datatable
        // TAB LIST
        let datatableList = $('#datatable_list').DataTable({
            ordering: true,
            processing: true,
            serverSide: false,
            paging: true,
            searching: true,
            scrollY: '600px',
            scrollX: true,
            scrollCollapse: true,
            autoWidth: true,

            ajax: {
                url: '{{ route('loading_out') }}',
                type: "GET",
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },

            columns: [{
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return `
        <button class="btn btn-outline-primary position-relative btn-sm"
                onclick="openModal('${data.no_form}')"
                title="Detail">Detail
        </button>`;
                    }
                },
                {
                    data: 'no_form'
                },
                {
                    data: 'tgl_form_fix'
                },
                {
                    data: 'pono'
                },
                {
                    data: 'supplier'
                },
                {
                    data: 'jns_dok'
                },
                {
                    data: 'jns_pengeluaran'
                },
                {
                    data: 'tot_qty'
                },
                {
                    data: 'berat_panel'
                },
                {
                    data: 'berat_karung'
                },
                {
                    data: 'ket'
                },
                {
                    data: 'created_by'
                },
            ],
            initComplete: function() {
                this.api().columns.adjust();
            }

        });

        // TAB DETAIL (kosong awalnya, diisi saat klik "View Detail")
        let datatableDetail = $('#datatable_detail').DataTable({
            ordering: true,
            processing: true,
            serverSide: false,
            paging: true,
            searching: true,
            scrollY: '600px',
            scrollX: true,
            scrollCollapse: true,
            autoWidth: true,

            ajax: {
                url: '{{ route('loading_out_det') }}',
                type: "GET",
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },

            columns: [{
                    data: 'no_form'
                },
                {
                    data: 'tgl_form_fix'
                },
                {
                    data: 'pono'
                },
                {
                    data: 'supplier'
                },
                {
                    data: 'qty'
                },
                {
                    data: 'buyer'
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
            ],
            initComplete: function() {
                this.api().columns.adjust();
            }

        });

        function dataTableDetReload() {
            datatableDetail.ajax.reload();
        }

        function dataTableReload() {
            datatableList.ajax.reload();
            // detail dikosongkan dulu supaya ga error ukuran
            datatableDetail.clear().draw();
        }

        function openModal(no_form) {
            // ✅ Clear input & preview
            // $('#photoInput').val('');
            // $('#photoPreview').empty();
            // $('#cbo_defect').val('').trigger('change');

            const modalElement = document.getElementById('detailModal');
            const modal = new bootstrap.Modal(modalElement);

            console.log(no_form);

            //Fetch item data via AJAX
            $.ajax({
                url: '{{ route('get_info_modal_det_loading_out') }}',
                method: 'GET',
                dataType: 'json',
                data: {
                    no_form: no_form
                },
                success: function(response) {
                    console.log('AJAX Success:', response);

                    // Populate fields
                    $('#txtno_form').val(no_form);
                    $('#txtsupplier').val(response.supplier);
                    $('#txtberat_karung').val(response.berat_karung);
                    $('#txtberat_panel').val(response.berat_panel);

                    //Show the modal
                    modal.show();

                    // Wait for modal to be fully shown
                    $('#detailModal').on('shown.bs.modal', function() {
                        const datatable = $('#datatable_modal_trans').DataTable();

                        // Reload the table
                        datatable.ajax.reload(function() {
                            // Adjust columns after reload
                            setTimeout(() => {
                                datatable.columns.adjust();
                                if (datatable.responsive) {
                                    datatable.responsive.recalc();
                                }
                            }, 100); // slight delay helps layout
                        });

                        const datatable_stocker = $('#datatable_modal_stocker').DataTable();
                        // Reload the table
                        datatable_stocker.ajax.reload(function() {
                            // Adjust columns after reload
                            setTimeout(() => {
                                datatable_stocker.columns.adjust();
                                if (datatable_stocker.responsive) {
                                    datatable_stocker.responsive.recalc();
                                }
                            }, 100); // slight delay helps layout
                        });

                    });
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    alert('Failed to load defect data. Please try again.');
                }
            });
        }

        // Datatable modal
        let datatableModalTrans = $('#datatable_modal_trans').DataTable({
            ordering: true,
            processing: true,
            serverSide: false,
            paging: true,
            searching: true,
            scrollY: '600px',
            scrollX: true,
            scrollCollapse: true,
            autoWidth: true,
            ajax: {
                url: '{{ route('get_table_modal_det_loading_out') }}',
                type: "GET",
                data: function(d) {
                    d.no_form = $('#txtno_form').val();
                },
            },

            columns: [{
                    data: 'kpno'
                },
                {
                    data: 'itemdesc'
                },
                {
                    data: 'tot_qty'
                },

            ],
            initComplete: function() {
                this.api().columns.adjust();
            }

        });


        // Datatable modal
        let datatableModalStocker = $('#datatable_modal_stocker').DataTable({
            ordering: true,
            processing: true,
            serverSide: false,
            paging: true,
            searching: true,
            scrollY: '600px',
            scrollX: true,
            scrollCollapse: true,
            autoWidth: true,
            ajax: {
                url: '{{ route('get_table_modal_stocker_loading_out') }}',
                type: "GET",
                data: function(d) {
                    d.no_form = $('#txtno_form').val();
                },
            },

            columns: [{
                    data: 'no_karung'
                },
                {
                    data: 'id_qr_stocker'
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

            ],
            initComplete: function() {
                this.api().columns.adjust();
            }

        });

        function konfirmasi() {
            let no_form = $('#txtno_form').val();

            // Disable the Save button to prevent multiple clicks
            let $btn = $('#KonfButton'); // Add id="saveButton" to your Save button
            $btn.prop('disabled', true);

            Swal.fire({
                title: 'Konfirmasi Transaksi ini?',
                text: 'Pastikan data sudah benar, akan ke record di SB.',
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
