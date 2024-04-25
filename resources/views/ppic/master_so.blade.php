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
    <!-- Import Excel -->
    <div class="modal fade" id="importExcel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="post" action="{{ route('import-excel-so') }}" enctype="multipart/form-data"
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


    <div class="card card-info  collapsed-card">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-upload"></i> Upload Master SO PPIC</h5>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <input type="hidden" class="form-control form-control-sm" id="user" name= "user"
                        value="{{ $user }}">
                    <a class="btn btn-outline-info position-relative btn-sm" data-toggle="modal" data-target="#importExcel"
                        onclick="OpenModal()">
                        <i class="fas fa-file-upload fa-sm"></i>
                        Upload
                    </a>
                </div>
                <div class="mb-3">
                    <a class="btn btn-outline-warning position-relative btn-sm" href="{{ route('contoh_upload_ppic_so') }}">
                        <i class="fas fa-file-download fa-sm"></i>
                        Contoh Upload
                    </a>
                </div>
                <div class="mb-3">
                    <a onclick="export_excel_master_so_sb()" class="btn btn-outline-success position-relative btn-sm">
                        <i class="fas fa-file-download fa-sm"></i>
                        Master Data SO SB
                    </a>
                </div>
            </div>
            <label>Preview</label>
            <div class="table-responsive">
                <table id="datatable-preview" class="table table-bordered table-sm w-100 text-nowrap">
                    <thead class="table-info">
                        <tr style='text-align:center; vertical-align:middle'>
                            <th>ID</th>
                            <th>Barcode</th>
                            <th>PO</th>
                            <th>Dest</th>
                            <th>Qty PO</th>
                            <th>Tgl. Shipment</th>
                            <th>Buyer</th>
                            <th>WS</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Style</th>
                            <th>Reff</th>
                            <th>Brand</th>
                            <th>User</th>
                            <th>Tgl. Upload</th>
                        </tr>
                    </thead>
                </table>
                <div class="d-flex justify-content-between">
                    <div class="p-2 bd-highlight">
                        <a class="btn btn-outline-warning" onclick="undo()">
                            <i class="fas fa-sync-alt
                            fa-spin"></i>
                            Undo
                        </a>
                    </div>
                    <div class="p-2 bd-highlight">
                        <a class="btn btn-outline-success" onclick="simpan()">
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
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> List Master</h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Awal</b></small></label>
                    <input type="date" class="form-control form-control-sm " id="tgl-awal" name="tgl_awal"
                        oninput="dataTableReload()" value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label"><small><b>Tgl Akhir</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir"
                        oninput="dataTableReload()" value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <a onclick="export_excel_master_so_ppic()" class="btn btn-outline-success position-relative btn-sm">
                        <i class="fas fa-file-excel fa-sm"></i>
                        Export Excel
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-sm w-100 table-hover display nowrap">
                    <thead class="table-primary">
                        <tr style='text-align:center; vertical-align:middle'>
                            <th>Buyer</th>
                            <th>Tgl. Shipment</th>
                            <th>Barcode</th>
                            <th>Reff</th>
                            <th>No. PO</th>
                            <th>Dest</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Qty PO</th>
                            <th>User</th>
                            <th>Tgl. Upload</th>
                        </tr>
                    </thead>
                </table>
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
        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }
    </script>
    <script type="text/javascript">
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

                        // $('#cbows').val("").trigger("change");
                        // $("#cbomarker").prop("disabled", true);

                        Swal.fire({
                            icon: 'success',
                            title: 'Data Upload berhasil diupload',
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                            timer: 5000,
                            timerProgressBar: true
                        })
                        dataTablePreviewReload();
                        $('#importExcel').modal('hide');
                        dataTableReload();
                    }
                },

            });
        }
    </script>
    <script>
        function OpenModal() {
            $('#importExcel').modal('show');
        }

        function dataTablePreviewReload() {
            datatable_preview.ajax.reload();
        }

        function dataTableReload() {
            datatable.ajax.reload();
        }

        let datatable_preview = $("#datatable-preview").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            paging: true,
            searching: true,
            ajax: {
                url: '{{ route('show_tmp_ppic_so') }}',
                data: function(d) {
                    d.user = $('#user').val();
                },
            },
            columns: [{
                    data: 'id_so_det'

                },
                {
                    data: 'barcode'
                },
                {
                    data: 'po'
                },
                {
                    data: 'dest'
                },
                {
                    data: 'qty_po'
                },
                {
                    data: 'tgl_shipment'
                },
                {
                    data: 'buyer'
                },
                {
                    data: 'ws'
                },
                {
                    data: 'color'
                },
                {
                    data: 'size'
                },
                {
                    data: 'styleno'
                },
                {
                    data: 'reff_no'
                },
                {
                    data: 'po'
                },
                {
                    data: 'created_by'
                },
                {
                    data: 'created_at'
                },
            ],
            // columnDefs:
            // [
            //     {
            //         "className": "align-middle",
            //         "targets": "_all"
            //     },
            //     {
            //         targets: [10],
            //         render: (data, type, row, meta) => {
            //             return `
        //                 <div class='d-flex gap-1 justify-content-center'>
        //                 <input type="text" size='2' class="form-control form-control-sm" id="barcode[` + row
            //                 .id + `]"
        //                 name="barcode[` + row.id + `]"
        //                 value="` +
            //                 row.barcode + `"
        //                     autocomplete="off">
        //                     </div>
        //         `;
            //         }
            //     },
            //     {
            //         targets: [11],
            //         render: (data, type, row, meta) => {
            //             return `
        //                 <div class='d-flex gap-1 justify-content-center'>
        //                 <input type="text" size='2' class="form-control form-control-sm" id="barcode[` + row
            //                 .id + `]"
        //                 name="barcode[` + row.id + `]"
        //                 value="` +
            //                 row.po + `"
        //                     autocomplete="off">
        //                     </div>
        //         `;
            //         }
            //     },
            //     {
            //         targets: [12],
            //         render: (data, type, row, meta) => {
            //             return `
        //                 <div class='d-flex gap-1 justify-content-center'>
        //                 <input type="text" size='2' class="form-control form-control-sm" id="barcode[` + row
            //                 .id + `]"
        //                 name="barcode[` + row.id + `]"
        //                 value="` +
            //                 row.dest + `"
        //                     autocomplete="off">
        //                     </div>
        //         `;
            //         }
            //     },
            //     {
            //         targets: [13],
            //         render: (data, type, row, meta) => {
            //             return `
        //                 <div class='d-flex gap-1 justify-content-center'>
        //                 <input type="text" size='2' class="form-control form-control-sm" id="barcode[` + row
            //                 .id + `]"
        //                 name="barcode[` + row.id + `]"
        //                 value="` +
            //                 row.tgl_shipment + `"
        //                     autocomplete="off">
        //                     </div>
        //         `;
            //         }
            //     },
            // ]
        });

        function undo() {
            let user = $('#user').val();
            $.ajax({
                type: "post",
                url: '{{ route('undo_tmp_ppic_so') }}',
                data: {
                    user: user
                },
                success: function(response) {
                    if (response.icon == 'salah') {
                        iziToast.warning({
                            message: response.msg,
                            position: 'topCenter'
                        });
                    } else {
                        iziToast.success({
                            message: response.msg,
                            position: 'topCenter'
                        });
                    }
                    dataTablePreviewReload();
                },
            });
        };

        function export_excel_master_so_sb() {
            Swal.fire({
                title: 'Please Wait...',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            $.ajax({
                type: "get",
                url: '{{ route('export_excel_master_sb_so') }}',
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    {
                        swal.close();
                        Swal.fire({
                            title: 'Data Sudah Di Export!',
                            icon: "success",
                            showConfirmButton: true,
                            allowOutsideClick: false
                        });
                        var blob = new Blob([response]);
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = "Master SO SB.xlsx";
                        link.click();

                    }
                },
            });
        }

        function simpan() {
            $.ajax({
                type: "post",
                url: '{{ route('store_tmp_ppic_so') }}',
                success: function(response) {
                    if (response.icon == 'salah') {
                        iziToast.warning({
                            message: response.msg,
                            position: 'topCenter'
                        });
                        dataTableReload();
                        dataTablePreviewReload();
                    } else {
                        Swal.fire({
                            text: response.msg,
                            icon: "success"
                        });
                        dataTableReload();
                        dataTablePreviewReload();
                    }

                },
                error: function(request, status, error) {
                    iziToast.warning({
                        message: 'Data Temporary Kosong cek lagi',
                        position: 'topCenter'
                    });
                    dataTableReload();
                    dataTablePreviewReload();
                },
            });

        };


        let datatable = $("#datatable").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            paging: true,
            searching: true,
            destroy: true,
            scrollX: true,
            ajax: {
                url: '{{ route('master-so') }}',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [{
                    data: 'buyer'

                }, {
                    data: 'tgl_shipment_fix'
                },
                {
                    data: 'barcode'
                },
                {
                    data: 'reff_no'
                },
                {
                    data: 'po'
                },
                {
                    data: 'dest'
                },
                {
                    data: 'color'
                },
                {
                    data: 'size'
                },
                {
                    data: 'qty_po'
                },
                {
                    data: 'created_by'
                },
                {
                    data: 'created_at'
                },
            ],
            columnDefs: [{
                "className": "dt-left",
                "targets": "_all"
            }, ]
        });

        function export_excel_master_so_ppic() {
            Swal.fire({
                title: 'Please Wait...',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            $.ajax({
                type: "get",
                url: '{{ route('export_excel_master_so_ppic') }}',
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    {
                        swal.close();
                        Swal.fire({
                            title: 'Data Sudah Di Export!',
                            icon: "success",
                            showConfirmButton: true,
                            allowOutsideClick: false
                        });
                        var blob = new Blob([response]);
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = "Master SO PPIC.xlsx";
                        link.click();

                    }
                },
            });
        }
    </script>
@endsection
