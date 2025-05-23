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
            <h5 class="card-title fw-bold">
                <i class="fa fa-search fa-sm"></i> Detail Cutting Plan Output
            </h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <input type="hidden" class="form-control" id="id" value="{{ $cutPlanData->id }}">
                <div class="col-md-3 mb-3">
                    <label class="form-label"><small>Tanggal</small></label>
                    <input type="text" class="form-control" id="tanggal" value="{{ strtoupper($cutPlanData->tgl_plan) }}" readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label"><small>No. Meja</small></label>
                    <input type="hidden" class="form-control" id="no_meja" value="{{ strtoupper($cutPlanData->meja->id) }}" readonly>
                    <input type="text" class="form-control" id="nama_meja" value="{{ strtoupper($cutPlanData->meja->name) }}" readonly>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label"><small>No. WS</small></label>
                    <input type="text" class="form-control" id="ws" name="ws" value="{{ $cutPlanData->ws }}" readonly>
                    <input type="hidden" class="form-control" id="id_ws" name="id_ws" value="{{ $cutPlanData->id_ws }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label"><small>Style</small></label>
                    <input type="text" class="form-control" id="style" name="style" value="{{ $cutPlanData->style }}" readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label"><small>Color</small></label>
                    <input type="text" class="form-control" id="color" name="color" value="{{ $cutPlanData->color }}" readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label"><small>Panel</small></label>
                    <input type="text" class="form-control" id="panel" name="panel" value="{{ $cutPlanData->panel }}" readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label"><small>Target Shift 1</small></label>
                    <input type="number" class="form-control" onchange="calculateTarget()" onkeyup="calculateTarget()" id="target_1" name="target_1" value="{{ $cutPlanData->target_1 }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label"><small>Pending Shift 1</small></label>
                    <input type="number" class="form-control" onchange="calculateTarget()" onkeyup="calculateTarget()" id="pending_1" name="pending_1" value="{{ $cutPlanData->pending_1 }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label"><small>Target Shift 2</small></label>
                    <input type="number" class="form-control" onchange="calculateTarget()" onkeyup="calculateTarget()" id="target_2" name="target_2" value="{{ $cutPlanData->target_2 }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label"><small>Pending Shift 2</small></label>
                    <input type="number" class="form-control" onchange="calculateTarget()" onkeyup="calculateTarget()" id="pending_2" name="pending_2" value="{{ $cutPlanData->pending_2 }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label"><small>Total Target</small></label>
                    <input type="number" class="form-control" id="total_target" name="total_target" value="{{ $cutPlanData->target_1 + $cutPlanData->pending_1 + $cutPlanData->target_2 +$cutPlanData->pending_2 }}" readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label"><small>Material Cons.</small></label>
                    <input type="number" class="form-control" onchange="calculateTarget()" onkeyup="calculateTarget()" id="cons" name="cons" step="0.001" value="{{ $cutPlanData->cons }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label"><small>Material Need</small></label>
                    <input type="number" class="form-control" id="need" name="need" step="0.0001" value="{{ $cutPlanData->need }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label"><small>Material Unit</small></label>
                    <select class="form-select select2bs4" id="unit" name="unit" value="{{ $cutPlanData->unit }}">
                        <option value="meter" {{ $cutPlanData->unit == "meter" ? "selected" : "" }}>METER</option>
                        <option value="yard" {{ $cutPlanData->unit == "yard" ? "selected" : "" }}>YARD</option>
                        <option value="kgm" {{ $cutPlanData->unit == "kgm" ? "selected" : "" }}>KGM</option>
                    </select>
                </div>
            </div>
            <div class="d-flex justify-content-between my-3">
                <h5 class="text-sb fw-bold">
                    Cutting Plan Form
                </h5>
                <button class="btn btn-sm btn-sb fw-bold" data-bs-toggle="modal" data-bs-target="#addFormModal">
                    <i class="fa fa-plus fa-sm"></i> Tambah Form
                </button>
            </div>
            <div class="table-responsive">
                <table id="datatable-form" class="table table-bordered table w-100">
                    <thead>
                        <tr>
                            <th>Tanggal Form</th>
                            <th>No. Form</th>
                            <th>No. Meja</th>
                            <th>No. Marker</th>
                            <th>No. WS</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Panel</th>
                            <th>Size Ratio</th>
                            <th>Qty Ply</th>
                            <th>Ket.</th>
                            <th class="align-bottom" style="text-align: left !important;">Status</th>
                            <th>Plan</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between gap-3 mt-3">
                <a href="{{ route('cut-plan-output') }}" class="btn btn-primary fw-bold">
                    <i class="fa fa-reply"></i>
                    KEMBALI
                </a>
                <button class="btn btn-success fw-bold">
                    <i class="fa fa-save"></i>
                    SIMPAN
                </button>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="addFormModal" tabindex="-1" aria-labelledby="addFormModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-sb text-light">
                <h1 class="modal-title fs-5 fw-bold" id="addFormModalLabel"><i class="fa fa-plus"></i> Tambah Form</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="datatable-add-form" class="table table-bordered table w-100">
                        <thead>
                            <tr>
                                <th><input class="me-1" type="checkbox" id="checkAllForm" onchange="checkAllForm(this)"></th>
                                <th>Tanggal Form</th>
                                <th>No. Form</th>
                                <th>No. Meja</th>
                                <th>No. Marker</th>
                                <th>No. WS</th>
                                <th>Style</th>
                                <th>Color</th>
                                <th>Panel</th>
                                <th>Size Ratio</th>
                                <th>Qty Ply</th>
                                <th>Ket.</th>
                                <th class="align-bottom" style="text-align: left !important;">Status</th>
                                <th>Plan</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-times"></i> Tutup</button>
                <button type="button" class="btn btn-primary" onclick="addForm()"><i class="fa fa-save"></i> Simpan</button>
            </div>
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

        // Calculate Total
        function calculateTarget() {
            let target1 = $("#target_1").val() ? Number($("#target_1").val()) : 0;
            let pending1 = $("#pending_1").val() ? Number($("#pending_1").val()) : 0;
            let target2 = $("#target_2").val() ? Number($("#target_2").val()) : 0;
            let pending2 = $("#pending_2").val() ? Number($("#pending_2").val()) : 0;

            let totalTarget = target1 + pending1 + target2 + pending2;

            let cons = $("#cons").val() ? $("#cons").val() : 0;

            document.getElementById('total_target').value = Number(totalTarget);

            document.getElementById('need').value = Number(totalTarget * cons).round(3);
        }

        let datatable = $("#datatable-form").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('cut-plan-output-form') }}',
                data: function(d) {
                    d.id = $('#id').val();
                    d.act_costing_id = $('#id_ws').val();
                    d.act_costing_ws = $('#ws').val();
                    d.color = $('#color').val();
                    d.no_meja = $('#no_meja').val();
                },
            },
            columns: [
                // {
                //     data: 'id'
                // },
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
                    data: 'marker_details'
                },
                {
                    data: 'ply_progress'
                },
                {
                    data: 'notes'
                },
                {
                    data: 'status'
                },
                {
                    data: 'tgl_plan'
                },
            ],
            columnDefs: [
                // {
                //     targets: [0],
                //     render: (data, type, row, meta) => {
                //         let btnEditMeja = row.status == 'SPREADING' ? "<a href='javascript:void(0);' class='btn btn-info btn-sm' onclick='editData(" + JSON.stringify(row) + ", \"editMejaModal\", [{\"function\" : \"dataTableRatioReload()\"}]);'><i class='fa fa-edit'></i></a>" : "<button class='btn btn-info btn-sm' onclick='editData(" + JSON.stringify(row) + ", \"editMejaModal\", [{\"function\" : \"dataTableRatioReload()\"}]);' disabled><i class='fa fa-edit'></i></button>";
                //         let btnEditStatus = row.status != 'SPREADING' ? "<a href='javascript:void(0);' class='btn btn-primary btn-sm' onclick='editData(" + JSON.stringify({'id_status' : row.id, 'status' : row.status}) + ", \"editStatusModal\", [{\"function\" : \"dataTableRatio1Reload()\"}]);'><i class='fa fa-cog'></i></a>" : "<button class='btn btn-primary btn-sm' onclick='editData(" + JSON.stringify({'id_status' : row.id, 'status' : row.status}) + ", \"editStatusModal\", [{\"function\" : \"dataTableRatio1Reload()\"}]);' disabled><i class='fa fa-cog'></i></button>";
                //         let btnDelete = row.status == 'SPREADING' ? "<a href='javascript:void(0);' class='btn btn-danger btn-sm' data='"+JSON.stringify(row)+"' data-url='"+'{{ route('destroy-spreading') }}'+"/"+row.id+"' onclick='deleteData(this);'><i class='fa fa-trash'></i></a>" : "<button class='btn btn-danger btn-sm' data='"+JSON.stringify(row)+"' data-url='"+'{{ route('destroy-spreading') }}'+"/"+row.id+"' onclick='deleteData(this);' disabled><i class='fa fa-trash'></i></button>";
                //         let btnProcess = "";

                //         if (row.tipe_form_cut == 'MANUAL') {
                //             btnProcess = (row.qty_ply > 0 && row.no_meja != '' && row.no_meja != null && row.app == 'Y') || row.status != 'SPREADING' ?
                //                 `<a class='btn btn-success btn-sm' href='{{ route('process-manual-form-cut') }}/` + row.id + `' data-bs-toggle='tooltip' target='_blank'><i class='fa `+ (row.status == "SELESAI PENGERJAAN" ? `fa-search-plus` : `fa-plus`) +`'></i></a>` :
                //                 `<button class='btn btn-success btn-sm' data-bs-toggle='tooltip' disabled><i class='fa `+ (row.status == "SELESAI PENGERJAAN" ? `fa-search-plus` : `fa-plus`) +`'></i></button>`;
                //         } else if (row.tipe_form_cut == 'PILOT') {
                //             btnProcess = (row.qty_ply > 0 && row.no_meja != '' && row.no_meja != null && row.app == 'Y') || row.status != 'SPREADING' ?
                //                 `<a class='btn btn-success btn-sm' href='{{ route('process-pilot-form-cut') }}/` + row.id + `' data-bs-toggle='tooltip' target='_blank'><i class='fa `+(row.status == "SELESAI PENGERJAAN" ? `fa-search-plus` : `fa-plus`)+`'></i></a>` :
                //                 `<button class='btn btn-success btn-sm' data-bs-toggle='tooltip' disabled><i class='fa `+(row.status == "SELESAI PENGERJAAN" ? `fa-search-plus` : `fa-plus`)+`'></i></button>`;
                //         } else {
                //             btnProcess = (row.qty_ply > 0 && row.no_meja != '' && row.no_meja != null && row.app == 'Y') || row.status != 'SPREADING' ?
                //                 `<a class='btn btn-success btn-sm' href='{{ route('process-form-cut-input') }}/` + row.id + `' data-bs-toggle='tooltip' target='_blank'><i class='fa `+(row.status == "SELESAI PENGERJAAN" ? `fa-search-plus` : `fa-plus`)+`'></i></a>` :
                //                 `<button class='btn btn-success btn-sm' data-bs-toggle='tooltip' disabled><i class='fa `+(row.status == "SELESAI PENGERJAAN" ? `fa-search-plus` : `fa-plus`)+`'></i></button>`;
                //         }

                //         return `<div class='d-flex gap-1 justify-content-center'>` + btnEditMeja + btnEditStatus + btnProcess + btnDelete + `</div>`;
                //     }
                // },
                {
                    targets: [4],
                    render: (data, type, row, meta) => {
                        return data ? `<a class='fw-bold' href='{{ route('edit-marker') }}/ `+row.marker_id+`' target='_blank'><u>`+data+`</u></a>` : "-";
                    }
                },
                {
                    targets: [10],
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
                    targets: [12],
                    className: "text-center align-middle",
                    render: (data, type, row, meta) => {
                        icon = "";

                        switch (data) {
                            case "SPREADING":
                            case "PENGERJAAN PILOT MARKER":
                            case "PENGERJAAN PILOT DETAIL":
                                icon = `<i class="fas fa-file fa-lg"></i>`;
                                break;
                            case "PENGERJAAN MARKER":
                            case "PENGERJAAN FORM CUTTING":
                            case "PENGERJAAN FORM CUTTING DETAIL":
                            case "PENGERJAAN FORM CUTTING SPREAD":
                                icon = `<i class="fas fa-sync-alt fa-spin fa-lg" style="color: #2243d6;"></i>`;
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
                        } else if (row.status == 'PENGERJAAN MARKER') {
                            color = '#2243d6';
                        } else if (row.status == 'PENGERJAAN FORM CUTTING') {
                            color = '#2243d6';
                        } else if (row.status == 'PENGERJAAN FORM CUTTING DETAIL') {
                            color = '#2243d6';
                        } else if (row.status == 'PENGERJAAN FORM CUTTING SPREAD') {
                            color = '#2243d6';
                        }

                        return  "<span style='font-weight: 600; color: "+ color + "' >" + (data ? data : '-') + "</span>"
                    }
                },
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

        function datatableReload() {
            $("#datatable-form").DataTable().ajax.reload()
        }

        let datatableAddForm = $("#datatable-add-form").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('available-cut-plan-output-form') }}',
                data: function(d) {
                    d.id = $('#id').val();
                    d.act_costing_id = $('#id_ws').val();
                    d.act_costing_ws = $('#ws').val();
                    d.color = $('#color').val();
                    d.no_meja = $('#no_meja').val();
                },
            },
            columns: [
                {
                    data: 'id'
                },
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
                    data: 'marker_details'
                },
                {
                    data: 'ply_progress'
                },
                {
                    data: 'notes'
                },
                {
                    data: 'status'
                },
                {
                    data: 'tgl_plan'
                },
            ],
            columnDefs: [
                // {
                //     targets: [0],
                //     render: (data, type, row, meta) => {
                //         let btnEditMeja = row.status == 'SPREADING' ? "<a href='javascript:void(0);' class='btn btn-info btn-sm' onclick='editData(" + JSON.stringify(row) + ", \"editMejaModal\", [{\"function\" : \"dataTableRatioReload()\"}]);'><i class='fa fa-edit'></i></a>" : "<button class='btn btn-info btn-sm' onclick='editData(" + JSON.stringify(row) + ", \"editMejaModal\", [{\"function\" : \"dataTableRatioReload()\"}]);' disabled><i class='fa fa-edit'></i></button>";
                //         let btnEditStatus = row.status != 'SPREADING' ? "<a href='javascript:void(0);' class='btn btn-primary btn-sm' onclick='editData(" + JSON.stringify({'id_status' : row.id, 'status' : row.status}) + ", \"editStatusModal\", [{\"function\" : \"dataTableRatio1Reload()\"}]);'><i class='fa fa-cog'></i></a>" : "<button class='btn btn-primary btn-sm' onclick='editData(" + JSON.stringify({'id_status' : row.id, 'status' : row.status}) + ", \"editStatusModal\", [{\"function\" : \"dataTableRatio1Reload()\"}]);' disabled><i class='fa fa-cog'></i></button>";
                //         let btnDelete = row.status == 'SPREADING' ? "<a href='javascript:void(0);' class='btn btn-danger btn-sm' data='"+JSON.stringify(row)+"' data-url='"+'{{ route('destroy-spreading') }}'+"/"+row.id+"' onclick='deleteData(this);'><i class='fa fa-trash'></i></a>" : "<button class='btn btn-danger btn-sm' data='"+JSON.stringify(row)+"' data-url='"+'{{ route('destroy-spreading') }}'+"/"+row.id+"' onclick='deleteData(this);' disabled><i class='fa fa-trash'></i></button>";
                //         let btnProcess = "";

                //         if (row.tipe_form_cut == 'MANUAL') {
                //             btnProcess = (row.qty_ply > 0 && row.no_meja != '' && row.no_meja != null && row.app == 'Y') || row.status != 'SPREADING' ?
                //                 `<a class='btn btn-success btn-sm' href='{{ route('process-manual-form-cut') }}/` + row.id + `' data-bs-toggle='tooltip' target='_blank'><i class='fa `+ (row.status == "SELESAI PENGERJAAN" ? `fa-search-plus` : `fa-plus`) +`'></i></a>` :
                //                 `<button class='btn btn-success btn-sm' data-bs-toggle='tooltip' disabled><i class='fa `+ (row.status == "SELESAI PENGERJAAN" ? `fa-search-plus` : `fa-plus`) +`'></i></button>`;
                //         } else if (row.tipe_form_cut == 'PILOT') {
                //             btnProcess = (row.qty_ply > 0 && row.no_meja != '' && row.no_meja != null && row.app == 'Y') || row.status != 'SPREADING' ?
                //                 `<a class='btn btn-success btn-sm' href='{{ route('process-pilot-form-cut') }}/` + row.id + `' data-bs-toggle='tooltip' target='_blank'><i class='fa `+(row.status == "SELESAI PENGERJAAN" ? `fa-search-plus` : `fa-plus`)+`'></i></a>` :
                //                 `<button class='btn btn-success btn-sm' data-bs-toggle='tooltip' disabled><i class='fa `+(row.status == "SELESAI PENGERJAAN" ? `fa-search-plus` : `fa-plus`)+`'></i></button>`;
                //         } else {
                //             btnProcess = (row.qty_ply > 0 && row.no_meja != '' && row.no_meja != null && row.app == 'Y') || row.status != 'SPREADING' ?
                //                 `<a class='btn btn-success btn-sm' href='{{ route('process-form-cut-input') }}/` + row.id + `' data-bs-toggle='tooltip' target='_blank'><i class='fa `+(row.status == "SELESAI PENGERJAAN" ? `fa-search-plus` : `fa-plus`)+`'></i></a>` :
                //                 `<button class='btn btn-success btn-sm' data-bs-toggle='tooltip' disabled><i class='fa `+(row.status == "SELESAI PENGERJAAN" ? `fa-search-plus` : `fa-plus`)+`'></i></button>`;
                //         }

                //         return `<div class='d-flex gap-1 justify-content-center'>` + btnEditMeja + btnEditStatus + btnProcess + btnDelete + `</div>`;
                //     }
                // },
                {
                    targets: [0],
                    render: (data, type, row, meta) => {
                        return `
                            <input class="check_add_form me-1" type="checkbox" value="`+data+`" id="check_add_`+meta.row+`" name="check_add[`+meta.row+`]" `+(checkedForms.includes(Number(data)) ? 'checked' : '')+` onchange='checkForm(this)'>
                        `;
                    }
                },
                {
                    targets: [4],
                    render: (data, type, row, meta) => {
                        return data ? `<a class='fw-bold' href='{{ route('edit-marker') }}/ `+row.marker_id+`' target='_blank'><u>`+data+`</u></a>` : "-";
                    }
                },
                {
                    targets: [10],
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
                    targets: [12],
                    className: "text-center align-middle",
                    render: (data, type, row, meta) => {
                        icon = "";

                        switch (data) {
                            case "SPREADING":
                            case "PENGERJAAN PILOT MARKER":
                            case "PENGERJAAN PILOT DETAIL":
                                icon = `<i class="fas fa-file fa-lg"></i>`;
                                break;
                            case "PENGERJAAN MARKER":
                            case "PENGERJAAN FORM CUTTING":
                            case "PENGERJAAN FORM CUTTING DETAIL":
                            case "PENGERJAAN FORM CUTTING SPREAD":
                                icon = `<i class="fas fa-sync-alt fa-spin fa-lg" style="color: #2243d6;"></i>`;
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
                        } else if (row.status == 'PENGERJAAN MARKER') {
                            color = '#2243d6';
                        } else if (row.status == 'PENGERJAAN FORM CUTTING') {
                            color = '#2243d6';
                        } else if (row.status == 'PENGERJAAN FORM CUTTING DETAIL') {
                            color = '#2243d6';
                        } else if (row.status == 'PENGERJAAN FORM CUTTING SPREAD') {
                            color = '#2243d6';
                        }

                        return  "<span style='font-weight: 600; color: "+ color + "' >" + (data ? data : '-') + "</span>"
                    }
                },
            ],
            rowCallback: function( row, data, index ) {
                if (data['tipe_form_cut'] == 'MANUAL') {
                    $('td', row).css('background-color', '#e7dcf7');
                    $('td', row).css('border', '0.15px solid #d0d0d0');
                } else if (data['tipe_form_cut'] == 'PILOT') {
                    $('td', row).css('background-color', '#c5e0fa');
                    $('td', row).css('border', '0.15px solid #d0d0d0');
                }

                checkAllFormElement();
            }
        });

        function datatableAddFormReload() {
            $("#datatable-add-form").DataTable().ajax.reload()
        }

        var checkedForms = [];

        async function checkAllForm(element) {
            document.getElementById('loading').classList.remove('d-none');

            if (element.checked) {
                $.ajax({
                    url: '{{ route('cut-plan-output-check-all-form') }}',
                    method: 'get',
                    data: {
                        act_costing_id: $('#id_ws').val(),
                        act_costing_ws: $('#ws').val(),
                        color: $('#color').val(),
                        no_meja: $('#no_meja').val(),
                    },
                    success: async function (res) {
                        if (res.length > 0) {
                            checkedForms = res;

                            checkAllFormElement();
                        }

                        document.getElementById('loading').classList.add('d-none');
                    },
                    error: function (jqXHR) {
                        console.log(jqXHR);

                        document.getElementById('loading').classList.add('d-none');
                    }
                });
            } else {
                checkedForms = [];

                checkAllFormElement();

                document.getElementById('loading').classList.add('d-none');
            }
        }

        function checkAllFormElement() {
            let checkAllForm = document.getElementsByClassName("check_add_form");

            for (let i = 0; i < checkAllForm.length; i++) {
                if (checkedForms.includes(Number(checkAllForm[i].value))) {
                    checkAllForm[i].checked = true;
                } else {
                    checkAllForm[i].checked = false;
                }
            }
        }

        function checkForm(element) {
            if (element.checked) {
                checkedForms.push(Number(element.value));
            } else {
                checkedForms.splice(Number(element.value));
            }
        }

        function addForm() {
            $.ajax({
                url: '{{ route('add-cut-plan-output-form') }}',
                method: 'post',
                data: {
                    id: $('#id').val(),
                    tanggal: $('#tanggal').val(),
                    no_meja: $('#no_meja').val(),
                    forms: checkedForms,
                },
                success: async function (res) {
                    console.log(res);

                    if (res.status == 200) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            html: res.message,
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                        });

                        datatableReload();

                        datatableAddFormReload();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Terjadi Kesalahan',
                            html: res.message,
                            showCancelButton: false,
                            showConfirmButton: true,
                            confirmButtonText: 'Oke',
                        });
                    }

                    checkedForms = [];
                    $("#checkAllForm").prop("checked", false);

                    document.getElementById('loading').classList.add('d-none');
                },
                error: function (jqXHR) {
                    console.log(jqXHR);

                    document.getElementById('loading').classList.add('d-none');
                }
            });
        }
    </script>
@endsection
