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
                <i class="fa-solid fa-screwdriver-wrench"></i> DC Tools
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <h5 class="text-sb-secondary fw-bold mt-3">Modify Data</h5>
                </div>
                <div class="col-md-4">
                    <a href="{{ route("modify-dc-qty") }}" class="home-item" target="_blank">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-sb mb-0"><i class="fa-solid fa-gears"></i> Modify DC Qty</h5>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="{{ route("modify-loading-line") }}" class="home-item" target="_blank">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-sb mb-0"><i class="fa-solid fa-gears"></i> Modify Loading Line</h5>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="{{ route("modify-year-sequence") }}" class="home-item" target="_blank">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-sb mb-0"><i class="fa-solid fa-gears"></i> Modify Year Sequence</h5>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a type="button" class="home-item" data-bs-toggle="modal" data-bs-target="#undoDcInModal">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-sb mb-0"><i class="fa-solid fa-gears"></i> Modify DC IN</h5>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-12">
                    <h5 class="text-sb-secondary fw-bold mt-3">Synchronize Data</h5>
                </div>
                <div class="col-md-4">
                    <a type="button" class="home-item" onclick="emptyOrderLoading()">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-sb mb-0"><i class="fa-solid fa-gears"></i> Fix Empty Order Loading</h5>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a type="button" class="home-item" onclick="redundantLoadingPlan()">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="text-sb mb-0"><i class="fa-solid fa-gears"></i> Fix Redundant Loading Plan</h5>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Reset Stocker By id -->
    <div class="modal fade" id="undoDcInModal" tabindex="-1" aria-labelledby="undoDcInModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-sb">
                    <h1 class="modal-title fs-5" id="undoDcInModalLabel">Modify DC IN</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">No. Stocker</label>
                        <textarea class="form-control" name="dc-in-stocker-ids" id="dc-in-stocker-ids" cols="30" rows="10"></textarea>
                    </div>
                    <div class="d-flex justify-content-between gap-3 my-3">
                        <div>
                            <div class="form-text">Contoh : <br>&nbsp;&nbsp;&nbsp;<b> STK-123113</b><br>&nbsp;&nbsp;&nbsp;<b> STK-123114</b><br>&nbsp;&nbsp;&nbsp;<b> STK-123115</b></div>
                        </div>
                        <div>
                            <button class="btn btn-sb-secondary" onclick="dcInListTableReload()">GET</button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm w-100" id="dc-in-list-table">
                                <thead>
                                    <tr>
                                        <th>Tanggal Transaksi</th>
                                        <th>No. Stocker</th>
                                        <th>Worksheet</th>
                                        <th>Style</th>
                                        <th>Color</th>
                                        <th>Size</th>
                                        <th>Qty Awal</th>
                                        <th>Qty Reject</th>
                                        <th>Qty Replace</th>
                                        <th>Qty IN</th>
                                        <th>Tujuan</th>
                                        <th>Proses</th>
                                        <th>Secondary Inhouse IN</th>
                                        <th>Secondary Inhouse OUT</th>
                                        <th>Secondary IN</th>
                                        <th>Trolley</th>
                                        <th>Line</th>
                                        <th>Created At</th>
                                        <th>Updated At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
                <div class="modal-footer justify-content-between align-items-end">
                    <div>
                        <button class="btn btn-danger" onclick="deleteDcIn()"><i class="fa fa-rotate-left"></i> UNDO</button>
                    </div>
                    <div class="d-flex align-items-end gap-3">
                        <div>
                            <label class="form-label">Tanggal</label>
                            <input type="date" class="form-control form-control-sm" id="dc-in-tanggal">
                        </div>
                        <button class="btn btn-sb" onclick="updateDcIn()"><i class="fa fa-edit"></i> UPDATE</button>
                    </div>
                    {{-- <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-times"></i> Batal</button>
                    <button type="button" class="btn btn-sb-secondary" onclick="resetStockerId()"><i class="fa fa-rotate-left"></i> UNDO</button> --}}
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

        function emptyOrderLoading() {
            Swal.fire({
                title: 'Fix Empty Order Loading?',
                html: '<span class="text-danger"><b>Critical</b></span> <br> Yakin akan mengubah data loading?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'UBAH',
                cancelButtonText: 'BATAL',
                confirmButtonColor: "#dc3545"
            }).then((result) => {
                if (result.isConfirmed) {
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
                        url: "{{ route('empty-order-loading') }}",
                        dataType: "json",
                        success: function (response) {
                            if (response.status == 200) {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'INFO',
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
                                    html: 'Terjadi kesalahan',
                                    showCancelButton: false,
                                    showConfirmButton: true,
                                    confirmButtonText: 'Oke',
                                    confirmButtonColor: "#082149",
                                });
                            }
                        }
                    });
                }
            });
        }

        function redundantLoadingPlan() {
            Swal.fire({
                title: 'Fix Redundant Loading Plan?',
                html: '<span class="text-danger"><b>Critical</b></span> <br> Yakin akan mengubah data loading?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'UBAH',
                cancelButtonText: 'BATAL',
                confirmButtonColor: "#dc3545"
            }).then((result) => {
                if (result.isConfirmed) {
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
                        url: "{{ route('redundant-loading-plan') }}",
                        dataType: "json",
                        success: function (response) {
                            if (response.status == 200) {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'INFO',
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
                                    html: 'Terjadi kesalahan',
                                    showCancelButton: false,
                                    showConfirmButton: true,
                                    confirmButtonText: 'Oke',
                                    confirmButtonColor: "#082149",
                                });
                            }
                        }
                    });
                }
            });
        }

        $("#dc-in-list-table").DataTable({
            processing: true,
            ordering: false,
            serverSide: true,
            ajax: {
                url: '{{ route('dc-in-list') }}',
                data: function(d) {
                    d.stocker_ids = $("#dc-in-stocker-ids").val();
                },
            },
            columns: [
                {
                    data: "tgl_trans",
                },
                {
                    data: "id_qr_stocker",
                },
                {
                    data: "ws",
                },
                {
                    data: "styleno",
                },
                {
                    data: "color",
                },
                {
                    data: "size",
                },
                {
                    data: "qty_awal",
                },
                {
                    data: "qty_reject",
                },
                {
                    data: "qty_replace",
                },
                {
                    data: "qty_in",
                },
                {
                    data: "tujuan",
                },
                {
                    data: "lokasi",
                },
                {
                    data: "sec_inhouse_in",
                },
                {
                    data: "sec_inhouse_out",
                },
                {
                    data: "sec_in",
                },
                {
                    data: "trolley",
                },
                {
                    data: "line",
                },
                {
                    data: "created_at",
                },
                {
                    data: "updated_at",
                },
            ],
            columnDefs: [
                {
                    targets: "_all",
                    className: "text-nowrap"
                },
                {
                    targets: [12, 13, 14, 17, 18],
                    className: "text-nowrap",
                    render: (data, type, row, meta) => {
                        return data ? formatDateTime(data) : '-';
                    }
                },
            ]
        });

        function dcInListTableReload() {
            $("#dc-in-list-table").DataTable().ajax.reload();
        }

        function updateDcIn() {
            Swal.fire({
                title: 'Update DC IN?',
                html: 'Yakin akan mengubah data DC IN?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'UPDATE',
                cancelButtonText: 'BATAL',
                confirmButtonColor: "#082149"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "put",
                        url: "{{ route('update-dc-in') }}",
                        data: {
                            "stocker_ids": $("#dc-in-stocker-ids").val(),
                            "tanggal": $("#dc-in-tanggal").val(),
                        },
                        dataType: "json",
                        success: function (response) {
                            console.log(response);

                            let alert = {};
                            if (response) {
                                alert = {
                                    icon: response.status == 200 ? "success" : "error",
                                    title: response.status == 200 ? "Berhasil" : "Gagal",
                                    html: response.message ? response.message : (response.status == 200 ? "Data berhasil diubah" : "Data gagal diubah"),
                                };
                            } else {
                                alert = {
                                    icon: "error",
                                    title: "Gagal",
                                    html: "Terjadi kesalahan",
                                };
                            }

                            return Swal.fire(alert).then(() => {
                                dcInListTableReload();
                            });
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            console.error(jqXHR);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                html: 'Terjadi kesalahan: ' + (jqXHR.responseJSON?.message || errorThrown || 'Unknown error'),
                            });
                        }
                    });
                }
            });
        }

        function deleteDcIn() {
            Swal.fire({
                title: 'Delete DC IN?',
                html: '<span class="text-danger"><b>Critical</b></span> <br> Yakin akan menghapus data DC IN?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'HAPUS',
                cancelButtonText: 'BATAL',
                confirmButtonColor: "#dc3545"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "delete",
                        url: "{{ route('delete-dc-in') }}",
                        data: {
                            "stocker_ids": $("#dc-in-stocker-ids").val(),
                            "tanggal": $("#dc-in-tanggal").val(),
                        },
                        dataType: "json",
                        success: function (response) {
                            console.log(response);

                            let alert = {};
                            if (response) {
                                alert = {
                                    icon: response.status == 200 ? "success" : "error",
                                    html: response.message ? response.message : (response.status == 200 ? "Data berhasil dihapus" : "Data gagal dihapus"),
                                };
                            } else {
                                alert = {
                                    icon: "error",
                                    html: "Terjadi kesalahan",
                                };
                            }

                            return Swal.fire(alert).then(() => {
                                dcInListTableReload();
                            });
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            console.error(jqXHR);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                html: 'Terjadi kesalahan: ' + (jqXHR.responseJSON?.message || errorThrown || 'Unknown error'),
                            });
                        }
                    });
                }
            });
        }
    </script>
@endsection
