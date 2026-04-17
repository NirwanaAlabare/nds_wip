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
            <h5 class="card-title">
                <i class="fa-solid fa-box"></i> Modify Packing PO
            </h5>
        </div>
        <div class="card-body">
            {{-- <div class="form-group mt-3">
                <label class='form-label'>Department</label>
                <select class="form-select" name="department" id="department">
                    <option value="qc">QC</option>
                    <option value="packing">FINISHING</option>
                    <option value="packing_po">PACKING</option>
                </select>
            </div> --}}
            <label class="form-label">Kode Numbering :</label>
            <textarea class="form-control" name="kode_numbering" id="kode_numbering" cols="30" rows="10"></textarea>
            <div class="form-text">Contoh : <br>&nbsp;&nbsp;&nbsp;<b> {{ date("Y") }}_1_1</b><br>&nbsp;&nbsp;&nbsp;<b> {{ date("Y") }}_1_2</b><br>&nbsp;&nbsp;&nbsp;<b> {{ date("Y") }}_1_3</b></div>
        </div>

        <div class="card-footer border-1">
            <button class="btn btn-sb btn-block" onclick="listTableReload()"><i class="fa fa-solid fa-search"></i> Search</button>
        </div>
    </div>
    <div class="card">
        <div class="card-header bg-sb-secondary">
            <h5 class="card-title">
                <i class="fas fa-list fa-sm"></i> List Data
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-sm" id="list-table">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>PO</th>
                            <th>WS</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Line</th>
                            <th>Created At</th>
                            <th>Created By</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card card-primary h-100">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fa fa-edit"></i> Modify Data
                    </h5>
                </div>
                <div class="card-body">
                    <label class="form-label">Update PO</label>
                    <select class="form-select form-select-sm select2bs4" name="edit_packing_po" id="edit_packing_po">
                    </select>
                </div>
                <div class="card-footer border-1">
                    <button type="button" class="btn btn-primary btn-block" onclick="updatePackingPO()"><i class="fa fa-save"></i> SIMPAN</button>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-danger h-100">
                <div class="card-header">
                    <h5 class="card-title">
                        Undo Data
                    </h5>
                </div>
                <div class="card-body">
                    <button type="button" class="btn btn-danger btn-block h-100" onclick="undoPackingPO()"><i class="fa fa-rotate"></i> UNDO</button>
                    {{-- <button type="button" class="btn btn-success btn-block" onclick="restoreUndoPackingPO()"><i class="fa fa-circle"></i> RESTORE</button> --}}
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
                url: '{{ route('get-packing-po') }}',
                dataType: 'json',
                dataSrc: 'data',
                scrollY: '400px',
                data: function(d) {
                    d.kode_numbering = $('#kode_numbering').val();
                },
            },
            columns: [
                { data: "kode_numbering" },
                { data: "po" },
                { data: "ws" },
                { data: "style" },
                { data: "color" },
                { data: "size" },
                { data: "sewing_line" },
                { data: "created_at" },
                { data: "created_by_username" }
            ],
            columnDefs: [
                {
                    targets: "_all",
                    className: "text-nowrap",
                },
                {
                    targets: [7],
                    className: "text-nowrap",
                    render: (data, type, row, meta) => formatDateTime(data)
                },
            ]
        });

        function listTableReload() {
            listTable.ajax.reload();

            updatePo();
        }

        function updatePo() {
            return $.ajax({
                type: "get",
                url: "{{ route('get-po-qr') }}",
                data: {
                    kode_numbering: $("#kode_numbering").val()
                },
                dataType: "json",
                success: function (response) {
                    $("#edit_packing_po").empty();
                    $("#edit_packing_po").append('<option value="" selected disabled>-- Pilih PO --</option>');
                    response.forEach(po => {
                        $("#edit_packing_po").append(`<option value="${po.po}">${po.po}</option>`);
                    });
                },
                error: function (jqXHR) {
                    console.error(jqXHR);
                }
            });
        }

        function updatePackingPO() {
            Swal.fire({
                title: 'Update Packing PO?',
                html: '<span class="text-danger"><b>Critical</b></span> <br> Yakin akan Update Packing PO ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'UPDATE',
                cancelButtonText: 'BATAL',
                confirmButtonColor: "#082149"
            }).then((result) => {
                $.ajax({
                    type: "PUT",
                    url: "{{ route('modify-packing-po-update') }}",
                    data: {
                        kode_numbering: $("#kode_numbering").val(),
                        edit_packing_po: $("#edit_packing_po").val(),
                    },
                    dataType: "json",
                    success: function (res) {
                        document.getElementById("loading").classList.add("d-none");

                        console.log(res);

                        Swal.fire({
                            icon: res.status == 200 ? 'success' : 'error',
                            title: res.status == 200 ? 'Berhasil' : 'Gagal',
                            html: res.message,
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                            confirmButtonColor: "#082149",
                        });

                        listTableReload();
                    }
                });
            });
        }

        function undoPackingPo() {
            Swal.fire({
                title: 'UNDO Undo Packing PO ?',
                html: '<span class="text-danger"><b>Critical</b></span> <br> Yakin akan UNDO SECONDARY ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'UNDO',
                cancelButtonText: 'BATAL',
                confirmButtonColor: "#dc3545"
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById("loading").classList.remove("d-none");

                    $.ajax({
                        url: "{{ route('modify-packing-po-delete') }}",
                        type: "post",
                        data: {
                            kode_numbering: $("#kode_numbering").val()
                        },
                        dataType: "json",
                        success: function (res) {
                            document.getElementById("loading").classList.add("d-none");

                            console.log(res);

                            if (res) {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Info',
                                    html: res.message,
                                    showCancelButton: false,
                                    showConfirmButton: true,
                                    confirmButtonText: 'Oke',
                                    confirmButtonColor: "#082149",
                                });

                                listTableReload();
                            }
                        }
                    });
                }
            });
        }

        function restoreUndoSecondary() {
            Swal.fire({
                title: 'Restore UNDO Output',
                html: '<span class="text-danger"><b>Critical</b></span> <br> Yakin akan Restore UNDO OUTPUT ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'RESTORE',
                cancelButtonText: 'BATAL',
                confirmButtonColor: "#dc3545"
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById("loading").classList.remove("d-none");

                    $.ajax({
                        url: "{{ route('restore-undo-submit') }}",
                        type: "post",
                        data: {
                            kode_numbering: $("#kode_numbering").val(),
                            department: $("#department").val()
                        },
                        dataType: "json",
                        success: function (res) {
                            document.getElementById("loading").classList.add("d-none");

                            if (res) {
                                console.log(res);
                                let messageRft = (res.rft.length)+" RFT <br>";
                                let messageDefect = (res.defect.length)+" Defect <br>";
                                let messageRework = (res.rework.length)+" Rework <br>";
                                let messageReject = (res.reject.length)+" Reject <br>";
                                let message = messageRft + messageDefect + messageRework + messageReject;

                                Swal.fire({
                                    icon: 'info',
                                    title: 'Info',
                                    html: message,
                                    showCancelButton: false,
                                    showConfirmButton: true,
                                    confirmButtonText: 'Oke',
                                    confirmButtonColor: "#082149",
                                });
                            }

                            listTableReload();
                        }
                    });
                }
            });
        }
    </script>
@endsection
