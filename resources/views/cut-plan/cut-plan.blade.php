@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="modal fade" id="cutPlanDetailModal" tabindex="-1" role="dialog" aria-labelledby="cutPlanDetailModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" style="max-width: 75%;">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h1 class="modal-title fs-5" id="cutPlanDetailModalLabel">Cut Plan</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Tanggal Plan</label>
                        <input type="date" class="form-control form-control" name="edit_tgl_plan" id="edit_tgl_plan"
                            readonly>
                    </div>
                    <div class="mb-3">
                        <div class="table-responsive">
                            <table id="datatable-form" class="table table-bordered table-sm w-100">
                                <thead>
                                    <tr>
                                        <th>No Form</th>
                                        <th>Tgl Form</th>
                                        <th>No. Meja</th>
                                        <th>Marker</th>
                                        <th>WS</th>
                                        <th>Style</th>
                                        <th>Color</th>
                                        <th>Panel</th>
                                        <th class="align-bottom">Status</th>
                                        <th>Size Ratio</th>
                                        <th>Qty Ply</th>
                                        <th>Qty Output</th>
                                        <th>Qty Actual</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                {{-- <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-sb">Simpan</button>
                </div> --}}
            </div>
        </div>
    </div>

    <div class="modal fade" id="manageCutPlanModal" tabindex="-1" role="dialog" aria-labelledby="manageCutPlanModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h1 class="modal-title fs-5" id="manageCutPlanModalLabel">Atur Form Cut</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('update-cut-plan') }}" method="post" id="manage-cut-plan-form">
                        @method('PUT')
                        <div class='row'>
                            <div class='col-sm-6'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Tgl. Plan</small></label>
                                    <input type='text' class='form-control' id='manage_tgl_plan' name='manage_tgl_plan'
                                        readonly>
                                </div>
                            </div>
                            <div class='col-sm-6'>
                                <div class='form-group'>
                                    <label class='form-label'><small>No. Cut Plan</small></label>
                                    <input type='text' class='form-control' id='manage_no_cut_plan'
                                        name='manage_no_cut_plan' onchange="datatableManageFormReload();" readonly>
                                </div>
                            </div>
                        </div>
                        <div class='row'>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Total Form</small></label>
                                    <input type='text' class='form-control' id='manage_total_form'
                                        name='manage_total_form' value = '' readonly>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Form Tersedia</small></label>
                                    <input type='text' class='form-control' id='manage_total_belum'
                                        name='manage_total_belum' value = '' readonly>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Form On Progress</small></label>
                                    <input type='text' class='form-control' id='manage_total_on_progress'
                                        name='manage_total_on_progress' value = '' readonly>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Form Selesai</small></label>
                                    <input type='text' class='form-control' id='manage_total_beres'
                                        name='manage_total_beres' value = '' readonly>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 table-responsive">
                            <table class="table table-bordered w-100" id="manage-form-datatable">
                                <thead>
                                    <tr>
                                        <th>Form Cut Data</th>
                                        <th>Marker Data</th>
                                        <th>Detail Data</th>
                                        <th>Ratio Data</th>
                                        <th>No. Form</th>
                                        <th>Meja</th>
                                        <th>Approve</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                        <div class="my-3">
                            <button type="button" class="btn btn-sb btn-block fw-bold mb-3"
                                onclick="submitManageForm();">SIMPAN</button>
                            <button type="button" class="btn btn-no btn-block fw-bold mb-3"
                                data-bs-dismiss="modal">BATAL</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <div class="card card-sb card-outline">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0">Data Cutting Plan</h5>
        </div>
        <div class="card-body">
            <a href="{{ route('create-cut-plan') }}" class="btn btn-success btn-sm mb-3">
                <i class="fa fa-cog"></i>
                Atur Cutting Plan
            </a>
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <label class="form-label"><small>Tgl Awal</small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal">
                </div>
                <div class="mb-3">
                    <label class="form-label"><small>Tgl Akhir</small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir"
                        value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <button class="btn btn-primary btn-sm" onclick="filterTable()">Tampilkan</button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-sm w-100">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Total Form</th>
                            <th>Belum Dikerjakan</th>
                            <th>On Progress</th>
                            <th>Selesai</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables  & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            let oneWeeksBefore = new Date(new Date().setDate(new Date().getDate() - 7));
            let oneWeeksBeforeDate = ("0" + oneWeeksBefore.getDate()).slice(-2);
            let oneWeeksBeforeMonth = ("0" + (oneWeeksBefore.getMonth() + 1)).slice(-2);
            let oneWeeksBeforeYear = oneWeeksBefore.getFullYear();
            let oneWeeksBeforeFull = oneWeeksBeforeYear + '-' + oneWeeksBeforeMonth + '-' + oneWeeksBeforeDate;

            $("#tgl-awal").val(oneWeeksBeforeFull).trigger("change");

            window.addEventListener("focus", () => {
                $('#datatable').DataTable().ajax.reload(null, false);
            });
        });


        let datatable = $("#datatable").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('cut-plan') }}',
                data: function(d) {
                    d.tgl_awal = $('#tgl-awal').val();
                    d.tgl_akhir = $('#tgl-akhir').val();
                },
            },
            columns: [{
                    data: 'tgl_plan_fix',
                },
                {
                    data: 'total_form'
                },
                {
                    data: 'total_belum'
                },
                {
                    data: 'total_on_progress'
                },
                {
                    data: 'total_beres'
                },
                {
                    data: 'no_cut_plan'
                },
            ],
            columnDefs: [{
                targets: [5],
                render: (data, type, row, meta) => {
                    return `
                        <div class='d-flex gap-1 justify-content-center'>
                            <a class='btn btn-primary btn-sm' onclick='editData(` + JSON.stringify(row) + `, \"cutPlanDetailModal\", [{\"function\" : \"datatableFormReload()\"}]);'>
                                <i class='fa fa-search'></i>
                            </a>
                            <a class='btn btn-warning btn-sm' onclick='manageCutPlan(` + JSON.stringify(row) + `);'>
                                <i class='fa fa-cog'></i>
                            </a>
                        </div>
                    `;
                }
            }, ]
        });

        function filterTable() {
            datatable.ajax.reload();
        }

        let datatableForm = $("#datatable-form").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('get-selected-form') }}',
                data: function(d) {
                    d.tgl_plan = $('#edit_tgl_plan').val();
                },
            },
            columns: [{
                    data: 'no_form'
                },
                {
                    data: 'tgl_form_cut'
                },
                {
                    data: 'nama_meja'
                },
                {
                    data: 'id_marker'
                },
                {
                    data: 'ws'
                },
                {
                    data: 'style'
                },
                {
                    data: 'color'
                },
                {
                    data: 'panel'
                },
                {
                    data: 'status'
                },
                {
                    data: 'marker_details'
                },
                {
                    data: 'qty_ply'
                },
                {
                    data: 'qty_output'
                },
                {
                    data: 'qty_act'
                },
            ],
            columnDefs: [{
                    targets: [2],
                    render: (data, type, row, meta) => {
                        let color = "";

                        if (row.status == 'SELESAI PENGERJAAN') {
                            color = '#087521';
                        } else if (row.status == 'PENGERJAAN FORM CUTTING') {
                            color = '#2243d6';
                        } else if (row.status == 'PENGERJAAN FORM CUTTING DETAIL') {
                            color = '#2243d6';
                        } else if (row.status == 'PENGERJAAN FORM CUTTING SPREAD') {
                            color = '#2243d6';
                        }

                        return data ? "<span style='color: " + color + "' >" + data.toUpperCase() +
                            "</span>" : "<span style=' color: " + color + "'>-</span>"
                    }
                },
                {
                    targets: [8],
                    className: "text-center align-middle",
                    render: (data, type, row, meta) => {
                        icon = "";

                        switch (data) {
                            case "SPREADING":
                                icon = `<i class="fas fa-file fa-lg"></i>`;
                                break;
                            case "PENGERJAAN FORM CUTTING":
                            case "PENGERJAAN FORM CUTTING DETAIL":
                            case "PENGERJAAN FORM CUTTING SPREAD":
                                icon =
                                    `<i class="fas fa-sync-alt fa-spin fa-lg" style="color: #2243d6;"></i>`;
                                break;
                            case "SELESAI PENGERJAAN":
                                icon = `<i class="fas fa-check fa-lg" style="color: #087521;"></i>`;
                                break;
                        }

                        return icon;
                    }
                },
                {
                    targets: '_all',
                    render: (data, type, row, meta) => {
                        let color = "";

                        if (row.status == 'SELESAI PENGERJAAN') {
                            color = '#087521';
                        } else if (row.status == 'PENGERJAAN FORM CUTTING') {
                            color = '#2243d6';
                        } else if (row.status == 'PENGERJAAN FORM CUTTING DETAIL') {
                            color = '#2243d6';
                        } else if (row.status == 'PENGERJAAN FORM CUTTING SPREAD') {
                            color = '#2243d6';
                        }

                        return data ? "<span style='color: " + color + "' >" + data + "</span>" :
                            "<span style=' color: " + color + "'>-</span>"
                    }
                }
            ]
        });

        function datatableFormReload() {
            datatableForm.ajax.reload();
        }

        function manageCutPlan(data) {
            for (let key in data) {
                if (document.getElementById('manage_' + key)) {
                    $('#manage_' + key).val(data[key]).trigger("change");
                    document.getElementById('manage_' + key).setAttribute('value', data[key]);

                    if (document.getElementById('manage_' + key).classList.contains('select2bs4') || document
                        .getElementById('manage_' + key).classList.contains('select2')) {
                        $('#manage_' + key).val(data[key]).trigger('change.select2');
                    }
                }
            }

            $("#manageCutPlanModal").modal('show');
        };

        let manageFormDatatable = $("#manage-form-datatable").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('get-cut-plan-form') }}',
                data: function(d) {
                    d.no_cut_plan = $('#manage_no_cut_plan').val();
                },
            },
            columns: [{
                    data: 'form_info',
                    sortable: false
                },
                {
                    data: 'marker_info',
                    sortable: false
                },
                {
                    data: 'marker_detail_info',
                    searchable: false,
                    sortable: false
                },
                {
                    data: 'ratio_info',
                    searchable: false,
                    sortable: false,
                },
                {
                    data: 'input_no_form',
                    searchable: false,
                    sortable: false,
                },
                {
                    data: 'meja',
                    searchable: false,
                    sortable: false
                },
                {
                    data: 'approve',
                    searchable: false,
                    sortable: false
                },
            ],
            columnDefs: [{
                    targets: [0, 1, 2, 3, 5, 6],
                    className: 'w-auto',
                },
                {
                    targets: [4],
                    className: 'd-none',
                }
            ],
        });

        function datatableManageFormReload() {
            manageFormDatatable.ajax.reload();
        }

        function approve(id) {
            document.getElementById('approve_' + id).value = 'Y';
        }

        function submitManageForm() {
            let manageForm = document.getElementById('manage-cut-plan-form');

            $.ajax({
                url: manageForm.getAttribute('action'),
                type: manageForm.getAttribute('method'),
                data: new FormData(manageForm),
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.status == 200) {
                        $('.modal').modal('hide');

                        iziToast.success({
                            title: 'Success',
                            message: 'Form berhasil diubah',
                            position: 'topCenter'
                        });

                        if (res.additional) {
                            let message = "";

                            if (res.additional['success'].length > 0) {
                                res.additional['success'].forEach(element => {
                                    message += element + " - Berhasil <br>";
                                });
                            }

                            if (res.additional['fail'].length > 0) {
                                res.additional['fail'].forEach(element => {
                                    message += element + " - Gagal <br>";
                                });
                            }

                            if (res.additional['exist'].length > 0) {
                                res.additional['exist'].forEach(element => {
                                    message += element + " - Sudah Ada <br>";
                                });
                            }

                            if (res.additional['success'].length + res.additional['fail'].length + res
                                .additional['exist'].length > 1) {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Hasil Ubah Data Form',
                                    html: message,
                                    showCancelButton: false,
                                    showConfirmButton: true,
                                    confirmButtonText: 'Oke',
                                });
                            }
                        }
                    } else {
                        iziToast.error({
                            title: 'Error',
                            message: res.message,
                            position: 'topCenter'
                        });
                    }
                },
                error: function(jqXHR) {
                    iziToast.error({
                        title: 'Error',
                        message: 'Terjadi kesalahan.',
                        position: 'topCenter'
                    });
                }
            })
        }
    </script>
@endsection
