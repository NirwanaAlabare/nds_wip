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
            <h5 class="card-title fw-bold">
                <i class="fa-solid fa-diagram-project"></i> Inject Mutasi Sewing
            </h5>
        </div>

        <div class="col-auto d-flex gap-2 justify-content-start align-items-end mt-3 px-3">
            <a class="btn btn-outline-info btn-sm"
                data-toggle="modal"
                data-target="#importExcel"
                onclick="OpenModal()">
                <i class="fas fa-file-upload fa-sm"></i>
                Upload
            </a>

            <a class="btn btn-outline-warning btn-sm"
                href="{{ route('contoh-upload-import-inject-mutasi-sewing') }}">
                <i class="fas fa-file-download fa-sm"></i>
                Contoh Upload
            </a>
        </div>
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-12 table-responsive">
                    <table class="table table-bordered w-100 table" id="datatable">
                        <thead>
                            <tr>
                                <th>Tgl Saldo</th>
                                <th>Type Saldo</th>
                                <th>Buyer</th>
                                <th>WS</th>
                                <th>Style</th>
                                <th>Color</th>
                                <th>Size</th>
                                <th>Qty Loading</th>
                                <th>Input Rework Sewing</th>
                                <th>Input Rework Spotcleaning</th>
                                <th>Input Rework Mending</th>
                                <th>Defect Sewing</th>
                                <th>Defect Spotcleaning</th>
                                <th>Defect Mending</th>
                                <th>Qty Sew Reject</th>
                                <th>Qty Sewing</th>
                                <th>Input Rework Sewing F</th>
                                <th>Input Rework Spotcleaning F</th>
                                <th>Input Rework Mending F</th>
                                <th>Defect Sewing F</th>
                                <th>Defect Spotcleaning F</th>
                                <th>Defect Mending F</th>
                                <th>Qty Fin Reject</th>
                                <th>Qty Finishing</th>
                                <th>Total In SP</th>
                                <th>Rework SP</th>
                                <th>Defect SP</th>
                                <th>Reject SP</th>
                                <th>RFT SP</th>
                                <th>Total Defect Sewing</th>
                                <th>Total Input Rework Sewing</th>
                                <th>Total Defect Spotcleaning</th>
                                <th>Total Input Rework Spotcleaning</th>
                                <th>Total Defect Mending</th>
                                <th>Total Input Rework Mending</th>
                                <th>Qty Reject In</th>
                                <th>Qty Rejected</th>
                                <th>Qty Reworked</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <input type="hidden" name="items" id="items">
            </div>
            <div class="col-12 col-md-6 offset-md-3 mt-3 text-center">
                <button type="submit" class="btn btn-success w-100 mb-1 fw-bold" id="btnSimpan">
                    <i class="fa fa-save"></i> SIMPAN
                </button>
            </div>
        </div>
    </div>
        
    <div class="card">
        <div class="card-header bg-sb-secondary">
            <h5 class="card-title">
                <i class="fas fa-list fa-sm"></i> List Data
            </h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-end gap-3 mb-3">
            <div>
                <label class="form-label"><small>Tanggal Awal</small></label>
                <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal" value="{{ date('Y-m-d') }}">
            </div>
            <div>
                <label class="form-label"><small>Tanggal Akhir</small></label>
                <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir" value="{{ date('Y-m-d') }}">
            </div>
            <div>
                <button class="btn btn-primary btn-sm" onclick="listTableReload()"> <i class="fa fa-search"></i> </button>
            </div>
            <div class="ms-auto">
                <button type="button" class="btn btn-danger btn-sm" id="btnDeleteSelected" style="display:none;"> <i class="fa fa-trash"></i> Delete </button>
            </div>
        </div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm" id="list-table">
                    <thead>
                        <tr>
                            <th class="text-center">
                                <input type="checkbox" id="check_all">
                            </th>
                            <th>Tgl Saldo</th>
                            <th>Type Saldo</th>
                            <th>Buyer</th>
                            <th>WS</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Qty Loading</th>
                            <th>Input Rework Sewing</th>
                            <th>Input Rework Spotcleaning</th>
                            <th>Input Rework Mending</th>
                            <th>Defect Sewing</th>
                            <th>Defect Spotcleaning</th>
                            <th>Defect Mending</th>
                            <th>Qty Sew Reject</th>
                            <th>Qty Sewing</th>
                            <th>Input Rework Sewing F</th>
                            <th>Input Rework Spotcleaning F</th>
                            <th>Input Rework Mending F</th>
                            <th>Defect Sewing F</th>
                            <th>Defect Spotcleaning F</th>
                            <th>Defect Mending F</th>
                            <th>Qty Fin Reject</th>
                            <th>Qty Finishing</th>
                            <th>Total In SP</th>
                            <th>Rework SP</th>
                            <th>Defect SP</th>
                            <th>Reject SP</th>
                            <th>RFT SP</th>
                            <th>Total Defect Sewing</th>
                            <th>Total Input Rework Sewing</th>
                            <th>Total Defect Spotcleaning</th>
                            <th>Total Input Rework Spotcleaning</th>
                            <th>Total Defect Mending</th>
                            <th>Total Input Rework Mending</th>
                            <th>Qty Reject In</th>
                            <th>Qty Rejected</th>
                            <th>Qty Reworked</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="importExcel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="post" action="{{ route('import-data-inject-mutasi-sewing') }}" enctype="multipart/form-data"
                onsubmit="submitUploadForm(this, event)">
                <div class="modal-content">
                    <div class="modal-header bg-sb text-light">
                        <h5 class="modal-title" id="exampleModalLabel">Import Excel</h5>
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
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-window-close"
                                aria-hidden="true"></i> Close</button>
                        <button type="submit" class="btn btn-primary toastsDefaultDanger"><i class="fa fa-thumbs-up"
                                aria-hidden="true"></i> Import</button>
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

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $('.select2').select2()
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })

        let listTable = $("#list-table").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('get-data-inject-mutasi-sewing') }}',
                dataType: 'json',
                dataSrc: 'data',
                scrollY: '400px',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [
                { 
                    data: null,
                    className: 'text-center',
                    orderable: false,
                    render: function (data, type, row) {
                        return `
                            <input 
                                type="checkbox" 
                                class="row-check" 
                                value="${row.no}"
                            >
                        `;
                    }
                },
                { data: "tgl_saldo" },
                { data: "type_saldo" },
                { data: "buyer" },
                { data: "ws" },
                { data: "styleno" },
                { data: "color" },
                { data: "size" },
                { data: 'qty_loading' },
                { data: 'input_rework_sewing' },
                { data: 'input_rework_spotcleaning' },
                { data: 'input_rework_mending' },
                { data: 'defect_sewing' },
                { data: 'defect_spotcleaning' },
                { data: 'defect_mending' },
                { data: 'qty_sew_reject' },
                { data: 'qty_sewing' },
                { data: 'input_rework_sewing_f' },
                { data: 'input_rework_spotcleaning_f' },
                { data: 'input_rework_mending_f' },
                { data: 'defect_sewing_f' },
                { data: 'defect_spotcleaning_f' },
                { data: 'defect_mending_f' },
                { data: 'qty_fin_reject' },
                { data: 'qty_finishing' },
                { data: 'total_in_sp' },
                { data: 'rework_sp' },
                { data: 'defect_sp' },
                { data: 'reject_sp' },
                { data: 'rft_sp' },
                { data: 'total_defect_sewing' },
                { data: 'total_input_rework_sewing' },
                { data: 'total_defect_spotcleaning' },
                { data: 'total_input_rework_spotcleaning' },
                { data: 'total_defect_mending' },
                { data: 'total_input_rework_mending' },
                { data: 'qty_reject_in' },
                { data: 'qty_rejected' },
                { data: 'qty_reworked' },
            ],
            columnDefs: [
                {
                    targets: "_all",
                    className: "text-nowrap",
                    render: (data, type, row, meta) => data
                },
            ]
        });

        function listTableReload() {
            showLoading();

            listTable.ajax.reload(function () {
                hideLoading();
            });
        }

        function OpenModal() {
            $('#importExcel').modal('show');
        }

        let table_detail_item;

        table_detail_item = $('#datatable').DataTable({
            processing: true,
            serverSide: false,
            data: [],
            columns: [
                { data: 'tgl_saldo' },
                { data: 'type_saldo' },
                { data: 'buyer' },
                { data: 'ws' },
                { data: 'styleno' },
                { data: 'color' },
                { data: 'size' },
                { data: 'qty_loading' },
                { data: 'input_rework_sewing' },
                { data: 'input_rework_spotcleaning' },
                { data: 'input_rework_mending' },
                { data: 'defect_sewing' },
                { data: 'defect_spotcleaning' },
                { data: 'defect_mending' },
                { data: 'qty_sew_reject' },
                { data: 'qty_sewing' },
                { data: 'input_rework_sewing_f' },
                { data: 'input_rework_spotcleaning_f' },
                { data: 'input_rework_mending_f' },
                { data: 'defect_sewing_f' },
                { data: 'defect_spotcleaning_f' },
                { data: 'defect_mending_f' },
                { data: 'qty_fin_reject' },
                { data: 'qty_finishing' },
                { data: 'total_in_sp' },
                { data: 'rework_sp' },
                { data: 'defect_sp' },
                { data: 'reject_sp' },
                { data: 'rft_sp' },
                { data: 'total_defect_sewing' },
                { data: 'total_input_rework_sewing' },
                { data: 'total_defect_spotcleaning' },
                { data: 'total_input_rework_spotcleaning' },
                { data: 'total_defect_mending' },
                { data: 'total_input_rework_mending' },
                { data: 'qty_reject_in' },
                { data: 'qty_rejected' },
                { data: 'qty_reworked' },
            ]
        });

        function submitUploadForm(form, event) {
            event.preventDefault();

            let formData = new FormData(form);

            $.ajax({
                url: form.action,
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function () {
                    showLoading();
                },
                success: function(res) {
                    if (res.status === 200) {

                        res.data.forEach(item => {
                            table_detail_item.row.add({
                                tgl_saldo: item.tgl_saldo,
                                type_saldo: item.type_saldo,
                                buyer: item.buyer,
                                ws: item.ws,
                                styleno: item.styleno,
                                color: item.color,
                                size: item.size,
                                qty_loading: item.qty_loading,
                                input_rework_sewing: item.input_rework_sewing,
                                input_rework_spotcleaning: item.input_rework_spotcleaning,
                                input_rework_mending: item.input_rework_mending,
                                defect_sewing: item.defect_sewing,
                                defect_spotcleaning: item.defect_spotcleaning,
                                defect_mending: item.defect_mending,
                                qty_sew_reject: item.qty_sew_reject,
                                qty_sewing: item.qty_sewing,
                                input_rework_sewing_f: item.input_rework_sewing_f,
                                input_rework_spotcleaning_f: item.input_rework_spotcleaning_f,
                                input_rework_mending_f: item.input_rework_mending_f,
                                defect_sewing_f: item.defect_sewing_f,
                                defect_spotcleaning_f: item.defect_spotcleaning_f,
                                defect_mending_f: item.defect_mending_f,
                                qty_fin_reject: item.qty_fin_reject,
                                qty_finishing: item.qty_finishing,
                                total_in_sp: item.total_in_sp,
                                rework_sp: item.rework_sp,
                                defect_sp: item.defect_sp,
                                reject_sp: item.reject_sp,
                                rft_sp: item.rft_sp,
                                total_defect_sewing: item.total_defect_sewing,
                                total_input_rework_sewing: item.total_input_rework_sewing,
                                total_defect_spotcleaning: item.total_defect_spotcleaning,
                                total_input_rework_spotcleaning: item.total_input_rework_spotcleaning,
                                total_defect_mending: item.total_defect_mending,
                                total_input_rework_mending: item.total_input_rework_mending,
                                qty_reject_in: item.qty_reject_in,
                                qty_rejected: item.qty_rejected,
                                qty_reworked: item.qty_reworked,
                            }).draw(false);
                        });

                        $('#importExcel').modal('hide');

                        Swal.fire('Success', 'Data berhasil diimport', 'success');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Import gagal', 'error');
                },
                complete: function () {
                    hideLoading();
                }
            });
        }

        $(document).on('click', '#btnSimpan', function () {
            let data = table_detail_item.rows().data().toArray();

            if (data.length === 0) {
                Swal.fire('Warning', 'Data masih kosong!', 'warning');
                return;
            }

            $.ajax({
                url: "{{ route('store-inject-mutasi-sewing') }}",
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    items: data
                },
                beforeSend: function () {
                    showLoading();
                    $('#btnSimpan').prop('disabled', true);
                },
                success: function (res) {

                    Swal.fire('Success', 'Data berhasil disimpan', 'success');

                    table_detail_item.clear().draw();
                    listTableReload();
                },
                error: function (xhr) {
                    Swal.fire('Error', 'Gagal simpan data', 'error');
                },
                complete: function () {
                    hideLoading();
                    $('#btnSimpan').prop('disabled', false);
                }
            });

        });

        $('#check_all').on('change', function () {
            $('.row-check').prop('checked', $(this).prop('checked'));
            toggleDeleteButton();
        });

        $('#btnDeleteSelected').on('click', function () {

            let ids = [];

            $('.row-check:checked').each(function () {
                ids.push($(this).val());
            });

            if (ids.length === 0) {
                Swal.fire('Warning', 'Tidak ada data yang dipilih!', 'warning');
                return;
            }

            Swal.fire({
                title: 'Yakin?',
                text: 'Data yang dicentang akan dihapus!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!'
            }).then((result) => {

                if (result.isConfirmed) {

                    $.ajax({
                        url: "{{ route('delete-inject-mutasi-sewing') }}",
                        type: "POST",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        data: {
                            ids: ids
                        },
                        success: function (res) {
                            $('#list-table').DataTable().ajax.reload(function () {
                                toggleDeleteButton();
                            });

                            Swal.fire('Success', 'Data berhasil dihapus!', 'success');
                        },
                        error: function () {
                            Swal.fire('Error', 'Gagal menghapus data!', 'error');
                        }
                    });
                }
            });
        });

        $('#list-table').on('change', '.row-check', function () {
            toggleDeleteButton();
        });

        function toggleDeleteButton() {
            let checked = $('.row-check:checked').length;

            if (checked > 0) {
                $('#btnDeleteSelected').show();
            } else {
                $('#btnDeleteSelected').hide();
            }
        }
    </script>
@endsection
