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
    {{-- Detail Cutting Plan Modal --}}
    <div class="modal fade" id="cutPlanDetailModal" tabindex="-1" role="dialog" aria-labelledby="cutPlanDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" style="width: 100%; height: 100%;">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h1 class="modal-title fs-5" id="cutPlanDetailModalLabel"><i class="fa fa-search"></i> Detail Cutting Plan</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Tanggal Plan</label>
                        <input type="date" class="form-control form-control" name="edit_tgl_plan" id="edit_tgl_plan" readonly>
                    </div>
                    <div class="mb-3">
                        <div class="table-responsive">
                            <table id="datatable-form" class="table table-bordered table-sm w-100">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>No. Form</th>
                                        <th>No. Meja</th>
                                        <th>Style</th>
                                        <th>Color</th>
                                        <th>Panel</th>
                                        <th>Size Ratio</th>
                                        <th>Qty Ply</th>
                                        <th>Qty Output</th>
                                        <th>Qty Actual</th>
                                        <th>No. Marker</th>
                                        <th>No. WS</th>
                                        <th class="align-bottom">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Manage Cutting Plan Modal --}}
    <form action="{{ route('update-cut-plan') }}" method="post" id="manage-cut-plan-form">
        @method('PUT')
        <div class="modal fade" id="manageCutPlanModal" tabindex="-1" role="dialog" aria-labelledby="manageCutPlanModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-sb text-light">
                        <h5 class="modal-title fs-5" id="manageCutPlanModalLabel"><i class="fa fa-edit"></i> Edit Plan Form Cut</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class='row'>
                            <div class='col-sm-6'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Tgl. Plan</small></label>
                                    <input type='text' class='form-control' id='manage_tgl_plan' name='manage_tgl_plan' readonly>
                                </div>
                            </div>
                            <div class='col-sm-6'>
                                <div class='form-group'>
                                    <label class='form-label'><small>No. Cut Plan</small></label>
                                    <input type='text' class='form-control' id='manage_no_cut_plan' name='manage_no_cut_plan' onchange="datatableManageFormReload();" readonly>
                                </div>
                            </div>
                        </div>
                        <div class='row'>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Total Form</small></label>
                                    <input type='text' class='form-control' id='manage_total_form' name='manage_total_form' value = '' readonly>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Form Tersedia</small></label>
                                    <input type='text' class='form-control' id='manage_total_belum' name='manage_total_belum' value = '' readonly>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Form On Progress</small></label>
                                    <input type='text' class='form-control' id='manage_total_on_progress' name='manage_total_on_progress' value = '' readonly>
                                </div>
                            </div>
                            <div class='col-sm-3'>
                                <div class='form-group'>
                                    <label class='form-label'><small>Form Selesai</small></label>
                                    <input type='text' class='form-control' id='manage_total_beres' name='manage_total_beres' value = '' readonly>
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
                    </div>
                    <div class="modal-footer">
                        <div class="d-flex w-100 justify-content-between">
                            <button type="button" class="btn btn-no fw-bold" data-bs-dismiss="modal"><i class="fa fa-times"></i> BATAL</button>
                            <button type="button" class="btn btn-success fw-bold" onclick="submitManageForm();"><i class="fa fa-check"></i> SIMPAN</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>


    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-map fa-sm"></i> Cutting Plan</h5>
        </div>
        <div class="card-body">
            <a href="{{ route('create-cut-plan') }}" class="btn btn-success btn-sm mb-3">
                <i class="fa fa-cog"></i>
                Plan
            </a>
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <label class="form-label"><small>Tanggal Awal</small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal" onchange="filterTable()">
                </div>
                <div class="mb-3">
                    <label class="form-label"><small>Tanggal Akhir</small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir" value="{{ date('Y-m-d') }}" onchange="filterTable()">
                </div>
                <div class="mb-3">
                    <button class="btn btn-primary btn-sm" onclick="filterTable()"><i class="fa fa-search"></i></button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-sm w-100">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Tanggal</th>
                            <th>Total Form</th>
                            <th>Belum Dikerjakan</th>
                            <th>On Progress</th>
                            <th>Selesai</th>
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
    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <!-- Page specific script -->
    <script>
        //Initialize Select2 Elements
        $('.select2').select2();

        //Initialize Select2 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        });

        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

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
            columns: [
                {
                    data: 'no_cut_plan'
                },
                {
                    data: 'tgl_plan_fix',
                },
                {
                    data: 'total_form',
                    searchable: false
                },
                {
                    data: 'total_belum',
                    searchable: false
                },
                {
                    data: 'total_on_progress',
                    searchable: false
                },
                {
                    data: 'total_beres',
                    searchable: false
                },
            ],
            columnDefs: [
                {
                    targets: [0],
                    render: (data, type, row, meta) => {
                        return `
                            <div class='d-flex gap-1 justify-content-center'>
                                <a class='btn btn-info btn-sm' onclick='editData(` + JSON.stringify(row) + `, \"cutPlanDetailModal\", [{\"function\" : \"datatableFormReload()\"}]);'>
                                    <i class='fa fa-search'></i>
                                </a>
                                <a class='btn btn-primary btn-sm' onclick='manageCutPlan(` + JSON.stringify(row) + `);'>
                                    <i class='fa fa-edit'></i>
                                </a>
                            </div>
                        `;
                    }
                },
            ],
        });

        function filterTable() {
            datatable.ajax.reload();
        }

        // Cutting Plan Form Header Column Form
        $('#datatable-form thead tr').clone(true).appendTo('#datatable-form thead');
        $('#datatable-form thead tr:eq(1) th').each(function(i) {
            if (i != 12) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm" />');

                $('input', this).on('keyup change', function() {
                    if (datatableForm.column(i).search() !== this.value) {
                        datatableForm
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            } else {
                $(this).empty();
            }
        });

        // Cutting Plan Form Datatable
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
            columns: [
                {
                    data: 'tgl_form_cut'
                },
                {
                    data: 'no_form'
                },
                {
                    data: 'nama_meja'
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
                    data: 'marker_details'
                },
                {
                    data: undefined
                },
                {
                    data: 'qty_output'
                },
                {
                    data: 'qty_act'
                },
                {
                    data: 'id_marker'
                },
                {
                    data: 'ws'
                },
                {
                    data: 'status'
                },
            ],
            columnDefs: [
                {
                    targets: [7],
                    render: (data, type, row, meta) => {
                        return `
                            <div class="progress border border-sb position-relative" style="min-width: 50px;height: 21px">
                                <p class="position-absolute" style="top: 50%;left: 50%;transform: translate(-50%, -50%);">`+row.total_lembar+`/`+row.qty_ply+`</p>
                                <div class="progress-bar" style="background-color: #75baeb;width: `+((row.total_lembar/row.qty_ply)*100)+`%" role="progressbar"></div>
                            </div>
                        `;
                    }
                },
                {
                    targets: [10],
                    render: (data, type, row, meta) => {
                        return data ? `<a class='fw-bold' href='{{ route('edit-marker') }}/ `+row.marker_id+`' target='_blank'><u>`+data+`</u></a>` : "-";
                    }
                },
                {
                    targets: [12],
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
                    className: "text-nowrap",
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

                        return data ? "<span style='color: " + color + "' >" + data + "</span>" : "<span style=' color: " + color + "'>-</span>"
                    }
                }
            ],
            rowCallback: function( row, data, index ) {
                if (data['tipe_form_cut'] == 'MANUAL') {
                    $('td', row).css('background-color', '#e7dcf7');
                    $('td', row).css('border', '0.15px solid #d0d0d0');
                } else if (data['tipe_form_cut'] == 'PILOT') {
                    $('td', row).css('background-color', '#c5e0fa');
                    $('td', row).css('border', '0.15px solid #d0d0d0');
                }
            }
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
            paging: false,
            ajax: {
                url: '{{ route('get-cut-plan-form') }}',
                data: function(d) {
                    d.no_cut_plan = $('#manage_no_cut_plan').val();
                    d.form_info_filter = $('#form_info_filter').val();
                    d.marker_info_filter = $('#marker_info_filter').val();
                    d.meja_filter = $('#meja_filter').val();
                    d.approve_filter = $('#approve_filter').val();
                },
            },
            columns: [
                {
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
                    data: 'input_form_cut_id',
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
            columnDefs: [
                {
                    targets: [0, 1, 2, 3, 5, 6],
                    className: 'w-auto',
                },
                {
                    targets: [4],
                    className: 'd-none',
                },
            ],
            rowCallback: function( row, data, index ) {
                $("#no_meja_"+index).val(data.no_meja).trigger('change');

                if (data['tipe_form_cut'] == 'MANUAL') {
                    $('td', row).css('background-color', '#e7dcf7');
                    $('td', row).css('border', '0.15px solid #d0d0d0');
                } else if (data['tipe_form_cut'] == 'PILOT') {
                    $('td', row).css('background-color', '#c5e0fa');
                    $('td', row).css('border', '0.15px solid #d0d0d0');
                }
            },
            drawCallback: function( settings ) {
                $('.select2bs4').select2({
                    theme: 'bootstrap4',
                    dropdownParent: $('#manageCutPlanModal')
                });
            }
        });

        function datatableManageFormReload() {
            manageFormDatatable.ajax.reload();
        }

        $('#manage-form-datatable thead tr').clone(true).appendTo('#manage-form-datatable thead');
        $('#manage-form-datatable thead tr:eq(1) th').each(function(i) {
            if (i != 2 && i != 3) {
                let elementId = '';

                console.log(i);

                switch (i) {
                    case 0 :
                        elementId = 'form_info_filter';
                        break;
                    case 1 :
                        elementId = 'marker_info_filter';
                        break;
                    case 5 :
                        elementId = 'meja_filter';
                        break;
                    case 6 :
                        elementId = 'approve_filter';
                        break;
                }

                let title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm" style="'+(elementId == "" ? "visibility: hidden;" : "")+'" id="'+elementId+'"/>');

                console.log(elementId);

                $('input', this).on('keyup change', function() {
                    datatableManageFormReload();
                });
            } else {
                $(this).empty();
            }
        });

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
