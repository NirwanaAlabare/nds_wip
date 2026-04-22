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
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-scroll fa-sm"></i> Spreading</h5>
        </div>
        <div class="card-body">
            <div class="row justify-content-between align-items-end g-3 mb-3">
                <div class="col-md-6">
                    <div class="d-flex align-items-end gap-3 mb-3">
                        <div>
                            <label class="form-label"><small>Tanggal Awal</small></label>
                            <input type="date" class="form-control form-control-sm" onchange="dataTableReload()" id="tgl-awal" name="tgl_awal">
                        </div>
                        <div>
                            <label class="form-label"><small>Tanggal Akhir</small></label>
                            <input type="date" class="form-control form-control-sm" onchange="dataTableReload()" id="tgl-akhir" name="tgl_akhir" value="{{ date('Y-m-d') }}">
                        </div>
                        <div>
                            <button class="btn btn-primary btn-sm" onclick="dataTableReload()"><i class="fa fa-search"></i> </button>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="d-flex justify-content-end align-items-end gap-1 mb-3">
                        <a href="{{ route('create-spreading') }}" class="btn btn-success btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="Buat Spreading Baru">
                            <i class="fas fa-plus"></i>
                        </a>
                        <button class="btn btn-sb-secondary btn-sm" onclick="dataTableReload()" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="Refresh Data">
                            <i class="fas fa-rotate"></i>
                        </button>
                        <a href="{{ url('manual-form-cut/create') }}" target="_blank" class="btn btn-sm btn-sb" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip" data-bs-title="Buat Form Manual"><i class="fas fa-clipboard-list"></i> Manual</a>
                        {{-- <a href="{{ url('pilot-form-cut/create') }}" target="_blank" class="btn btn-sm btn-sb-secondary"><i class="fas fa-clipboard-list"></i> Pilot</a> --}}
                        {{-- <button type="button" onclick="updateNoCut()" class="btn btn-sm btn-sb"><i class="fas fa-sync-alt"></i> Generate No. Cut</button> --}}
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table w-100">
                    <thead>
                        <tr>
                            <th>Action</th>
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
                            <th>Created By</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Edit Meja Modal -->
        <div class="modal fade" id="editMejaModal" tabindex="-1" aria-labelledby="editMejaModalLabel" aria-hidden="true">
            <form action="{{ route('update-spreading') }}" method="post" onsubmit="submitForm(this, event)">
                @method('PUT')
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header bg-sb text-light">
                            <h1 class="modal-title fs-5" id="editMejaModalLabel"><i class="fa fa-edit fa-sm"></i> Ubah Alokasi Form</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" style="max-height: 65vh !important;">
                            <div class="container-fluid">
                                <div class="row g-3">

                                    <!-- Hidden Fields -->
                                    <input type="hidden" id="edit_id" name="edit_id">
                                    <input type="hidden" id="edit_marker_id" name="edit_marker_id">

                                    <!-- Header Section -->
                                    <div class="col-12">
                                    <h5 class="fw-semibold mb-2">Form Information</h5>
                                    <hr class="mt-0 border-1 border-sb">
                                    </div>

                                    <!-- Readonly Info -->
                                    <div class="col-md-3">
                                    <label class="form-label small text-muted">Tanggal Form</label>
                                    <input type="text" class="form-control form-control-sm" id="edit_tgl_form_cut" readonly>
                                    </div>

                                    <div class="col-md-3">
                                    <label class="form-label small text-muted">No. Form</label>
                                    <input type="text" class="form-control form-control-sm" id="edit_no_form" readonly>
                                    </div>

                                    <div class="col-md-3">
                                    <label class="form-label small text-muted">Marker</label>
                                    <input type="text" class="form-control form-control-sm" id="edit_id_marker" readonly>
                                    </div>

                                    <div class="col-md-3">
                                    <label class="form-label small text-muted">WS</label>
                                    <input type="text" class="form-control form-control-sm" id="edit_ws" readonly>
                                    </div>

                                    <!-- Secondary Info -->
                                    <div class="col-md-3">
                                    <label class="form-label small text-muted">Color</label>
                                    <input type="text" class="form-control form-control-sm" id="edit_color" readonly>
                                    </div>

                                    <div class="col-md-3">
                                    <label class="form-label small text-muted">Panel</label>
                                    <input type="text" class="form-control form-control-sm" id="edit_panel" readonly>
                                    </div>

                                    <div class="col-md-3">
                                    <label class="form-label small text-muted">PO Marker</label>
                                    <input type="text" class="form-control form-control-sm" id="edit_po_marker" readonly>
                                    </div>

                                    <div class="col-md-3">
                                    <label class="form-label small text-muted">Tipe Marker</label>
                                    <input type="text" class="form-control form-control-sm" id="edit_tipe_marker" readonly>
                                    </div>

                                    <!-- Measurements Section -->
                                    <div class="col-12 mt-4">
                                    <h6 class="fw-semibold">Measurement Details</h6>
                                    </div>

                                    <div class="col-md-2">
                                    <label class="form-label small">Panjang</label>
                                    <input type="text" class="form-control form-control-sm" id="edit_panjang_marker" readonly>
                                    </div>

                                    <div class="col-md-2">
                                    <label class="form-label small">Unit</label>
                                    <input type="text" class="form-control form-control-sm" id="edit_unit_panjang_marker" readonly>
                                    </div>

                                    <div class="col-md-2">
                                    <label class="form-label small">Comma</label>
                                    <input type="text" class="form-control form-control-sm" id="edit_comma_marker" readonly>
                                    </div>

                                    <div class="col-md-2">
                                    <label class="form-label small">Unit</label>
                                    <input type="text" class="form-control form-control-sm" id="edit_unit_comma_marker" readonly>
                                    </div>

                                    <div class="col-md-2">
                                    <label class="form-label small">Lebar</label>
                                    <input type="text" class="form-control form-control-sm" id="edit_lebar_marker" readonly>
                                    </div>

                                    <div class="col-md-2">
                                    <label class="form-label small">Unit</label>
                                    <input type="text" class="form-control form-control-sm" id="edit_unit_lebar_marker" readonly>
                                    </div>

                                    <!-- Quantity Section -->
                                    <div class="col-12 mt-4">
                                    <h6 class="fw-semibold">Quantity Details</h6>
                                    </div>

                                    <div class="col-md-3">
                                    <label class="form-label small">Gelar QTY</label>
                                    <input type="text" class="form-control form-control-sm" id="edit_gelar_qty" readonly>
                                    </div>

                                    <div class="col-md-3">
                                    <label class="form-label small">Ply QTY</label>
                                    <input type="text" class="form-control form-control-sm" id="edit_qty_ply" readonly>
                                    </div>

                                    <div class="col-md-3">
                                    <label class="form-label small">Urutan Marker</label>
                                    <input type="text" class="form-control form-control-sm" id="edit_urutan_marker" readonly>
                                    </div>

                                    <div class="col-md-3">
                                    <label class="form-label small">Cons. Marker</label>
                                    <input type="text" class="form-control form-control-sm" id="edit_cons_marker" readonly>
                                    </div>

                                    <!-- Action Section -->
                                    <div class="col-12 mt-4">
                                    <h6 class="fw-semibold">Actions</h6>
                                    </div>

                                    <div class="col-md-4">
                                    <label class="form-label small">No. Meja</label>
                                    <select class="form-select form-select-sm select2bs4" id="edit_no_meja" name="edit_no_meja">
                                        <option value="">-</option>
                                        @foreach ($meja as $m)
                                        <option value="{{ $m->id }}">{{ strtoupper($m->name) }}</option>
                                        @endforeach
                                    </select>
                                    </div>

                                    <div class="col-md-8">
                                    <label class="form-label small">Keterangan</label>
                                    <textarea class="form-control form-control-sm" id="edit_notes" name="edit_notes" rows="2"></textarea>
                                    </div>

                                    <!-- Action Section -->
                                    <div class="col-12 mt-4">
                                    <h6 class="fw-semibold">Plan</h6>
                                    </div>

                                    <div class="col-md-4">
                                    <label class="form-label small">Tanggal Plan</label>
                                    <input type="date" class="form-control form-control-sm" id="edit_tgl_plan" name="edit_tgl_plan">
                                    </div>

                                    <div class="col-md-2 align-items-end">
                                    <label class="form-label small ">&nbsp;</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="edit_approve" name="edit_approve">
                                        <label class="form-check-label">Approve</label>
                                    </div>
                                    </div>

                                    <!-- Table Section -->
                                    <div class="col-12 mt-4">
                                    <h6 class="fw-semibold">Ratio Details</h6>
                                    <div class="table-responsive">
                                        <table id="datatable-ratio" class="table table-hover table-bordered align-middle w-100">
                                        <thead class="table-light">
                                            <tr>
                                            <th>Size</th>
                                            <th>Ratio</th>
                                            <th>Cut Qty</th>
                                            </tr>
                                        </thead>
                                        </table>
                                    </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-success">Simpan</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Edit Status Modal -->
        <div class="modal fade" id="editStatusModal" tabindex="-1" aria-labelledby="editStatusModalLabel" aria-hidden="true">
            <form action="{{ route('update-status') }}" method="post" onsubmit="submitForm(this, event)">
                @method('PUT')
                <div class="modal-dialog modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header bg-sb text-light">
                            <h1 class="modal-title fs-5" id="editStatusModalLabel"><i class="fa fa-cog fa-sm"></i> Ubah Status Form</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" style="max-height: 65vh !important;">
                            <div class="row">
                                <input type="hidden" id="edit_id_status" name="edit_id_status">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <select class="form-select select2bs4stat" name="edit_status" id="edit_status">
                                            <option value="SPREADING">SPREADING</option>
                                            <option value="PENGERJAAN FORM CUTTING">PENGERJAAN FORM CUTTING</option>
                                            <option value="PENGERJAAN FORM CUTTING DETAIL">PENGERJAAN FORM CUTTING DETAIL</option>
                                            <option value="PENGERJAAN FORM CUTTING SPREAD">PENGERJAAN FORM CUTTING SPREAD</option>
                                            <option value="SELESAI PENGERJAAN">SELESAI PENGERJAAN</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-success">Simpan</button>
                        </div>
                    </div>
                </div>
            </form>
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
    <script>
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        $('.select2').select2();

        $('.select2bs4').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#editMejaModal")
        });

        $('.select2bs4stat').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#editStatusModal")
        });
    </script>

    <script>
        $(document).ready(() => {
            let oneWeeksBefore = new Date(new Date().setDate(new Date().getDate() - 7));
            let oneWeeksBeforeDate = ("0" + oneWeeksBefore.getDate()).slice(-2);
            let oneWeeksBeforeMonth = ("0" + (oneWeeksBefore.getMonth() + 1)).slice(-2);
            let oneWeeksBeforeYear = oneWeeksBefore.getFullYear();
            let oneWeeksBeforeFull = oneWeeksBeforeYear + '-' + oneWeeksBeforeMonth + '-' + oneWeeksBeforeDate;

            $("#tgl-awal").val(oneWeeksBeforeFull).trigger("change");

            // window.addEventListener("focus", () => {
            //     dataTableReload();
            // });
        });

        let datatable = $("#datatable").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('spreading') }}',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
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
                {
                    data: 'created_by_username'
                },
                {
                    data: 'created_at'
                },
                {
                    data: 'updated_at'
                },
            ],
            columnDefs: [
                {
                    targets: [0],
                    render: (data, type, row, meta) => {
                        // Superadmin Access
                        let isAdmin = "{{ Auth::user()->roles->whereIn("nama_role", ["superadmin"])->count() }}";

                        // Buttons
                        let btnEditMeja = row.status == 'SPREADING' ? "<a href='javascript:void(0);' data-bs-toggle='tooltip' data-bs-title='Ubah Alokasi Form' class='btn btn-info btn-sm' onclick='editData(" + JSON.stringify(row) + ", \"editMejaModal\", [{\"function\" : \"dataTableRatioReload()\"}]);'><i class='fa fa-edit'></i></a>" : "<button class='btn btn-info btn-sm' data-bs-toggle='tooltip' data-bs-title='Ubah Alokasi Form' onclick='editData(" + JSON.stringify(row) + ", \"editMejaModal\", [{\"function\" : \"dataTableRatioReload()\"}]);' disabled><i class='fa fa-edit'></i></button>";
                        let btnEditStatus = row.status != 'SPREADING' ? "<a href='javascript:void(0);' class='btn btn-primary btn-sm' data-bs-toggle='tooltip' data-bs-title='Ubah Status Form' onclick='editData(" + JSON.stringify({'id_status' : row.id, 'status' : row.status}) + ", \"editStatusModal\", [{\"function\" : \"dataTableRatio1Reload()\"}]);'><i class='fa fa-cog'></i></a>" : "<button class='btn btn-primary btn-sm' data-bs-toggle='tooltip' data-bs-title='Ubah Status Form' onclick='editData(" + JSON.stringify({'id_status' : row.id, 'status' : row.status}) + ", \"editStatusModal\", [{\"function\" : \"dataTableRatio1Reload()\"}]);' disabled><i class='fa fa-cog'></i></button>";
                        let btnDelete = row.status == 'SPREADING' ? "<a href='javascript:void(0);' data='"+JSON.stringify(row)+"' data-url='{{ route('destroy-spreading') }}/"+row['id']+"' class='btn btn-danger btn-sm' data-bs-toggle='tooltip' data-bs-title='Hapus Form' onclick='deleteData(this);'><i class='fa fa-trash'></i></a>" : "<button class='btn btn-danger btn-sm' data-bs-toggle='tooltip' data-bs-title='Hapus Form' onclick='deleteData(this);' disabled><i class='fa fa-trash'></i></button>";
                        let btnPDF = "<a href='javascript:void(0);' class='btn btn-dark btn-sm' data='"+JSON.stringify(row)+"' data-bs-toggle='tooltip' data-bs-title='Cetak Form' onclick='printForm(this);'><i class='fa fa-file-pdf'></i></a>";

                        // Set Form Process based on form type
                        let btnProcess = "";
                        if (row.tipe_form_cut == 'MANUAL') {
                            btnProcess = (row.qty_ply > 0 && row.no_meja != '' && row.no_meja != null && row.app == 'Y') || row.status != 'SPREADING' ?
                                `<a class='btn btn-success btn-sm' href='{{ route('process-manual-form-cut') }}/` + row.id + `' data-bs-toggle="tooltip" data-bs-title="Proses Form" target='_blank'><i class='fa `+ (row.status == "SELESAI PENGERJAAN" ? `fa-search-plus` : `fa-plus`) +`'></i></a>` :
                                `<button class='btn btn-success btn-sm' data-bs-toggle="tooltip" data-bs-title="Proses Form" disabled><i class='fa `+ (row.status == "SELESAI PENGERJAAN" ? `fa-search-plus` : `fa-plus`) +`'></i></button>`;
                        } else if (row.tipe_form_cut == 'PILOT') {
                            btnProcess = (row.qty_ply > 0 && row.no_meja != '' && row.no_meja != null && row.app == 'Y') || row.status != 'SPREADING' ?
                                `<a class='btn btn-success btn-sm' href='{{ route('process-pilot-form-cut') }}/` + row.id + `' data-bs-toggle="tooltip" data-bs-title="Proses Form" target='_blank'><i class='fa `+(row.status == "SELESAI PENGERJAAN" ? `fa-search-plus` : `fa-plus`)+`'></i></a>` :
                                `<button class='btn btn-success btn-sm' data-bs-toggle="tooltip" data-bs-title="Proses Form" disabled><i class='fa `+(row.status == "SELESAI PENGERJAAN" ? `fa-search-plus` : `fa-plus`)+`'></i></button>`;
                        } else {
                            btnProcess = (row.qty_ply > 0 && row.no_meja != '' && row.no_meja != null && row.app == 'Y') || row.status != 'SPREADING' ?
                                `<a class='btn btn-success btn-sm' href='{{ route('process-form-cut-input') }}/` + row.id + `' data-bs-toggle="tooltip" data-bs-title="Proses Form" target='_blank'><i class='fa `+(row.status == "SELESAI PENGERJAAN" ? `fa-search-plus` : `fa-plus`)+`'></i></a>` :
                                `<button class='btn btn-success btn-sm' data-bs-toggle="tooltip" data-bs-title="Proses Form" disabled><i class='fa `+(row.status == "SELESAI PENGERJAAN" ? `fa-search-plus` : `fa-plus`)+`'></i></button>`;
                        }

                        return `<div class='d-flex gap-1 justify-content-center'>` + btnEditMeja + (isAdmin > 0 ? btnEditStatus : '') + btnProcess + (isAdmin > 0 ? btnDelete : '') + btnPDF + `</div>`;
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
                    targets: [15, 16],
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

                        return "<span style='font-weight: 600; color: "+color+"'>"+formatDateTime(data)+"</span>";
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
            },
            "drawCallback": function(settings) {
                $('[data-bs-toggle="tooltip"]').tooltip();
            }
        });

        let datatableRatio = $("#datatable-ratio").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('getdata_ratio') }}',
                data: function(d) {
                    d.cbomarker = $('#edit_marker_id').val();
                },
            },
            columns: [
                {
                    data: 'size'
                },
                {
                    data: 'ratio'
                },
                {
                    data: 'cut_qty'
                },
            ]
        });

        let datatableRatio1 = $("#datatable-ratio-1").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('getdata_ratio') }}',
                data: function(d) {
                    d.cbomarker = $('#edit_marker_id').val();
                },
            },
            columns: [
                {
                    data: 'size'
                },
                {
                    data: 'ratio'
                },
                {
                    data: 'cut_qty'
                },
            ]
        });

        function dataTableReload() {
            datatable.ajax.reload();
        }

        function dataTableRatioReload() {
            datatableRatio.ajax.reload();
        }

        function dataTableRatio1Reload() {
            datatableRatio1.ajax.reload();
        }

        $('#datatable thead tr').clone(true).appendTo('#datatable thead');
        $('#datatable thead tr:eq(1) th').each(function(i) {
            if (i != 0 && i != 9 && i != 10 && i != 12) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm"/>');

                $('input', this).on('keyup change', function() {
                    if (datatable.column(i).search() !== this.value) {
                        datatable
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            } else {
                $(this).empty();
            }
        });

        function updateNoCut() {
            $.ajax({
                url: '{{ route('form-cut-update-no-cut') }}',
                type: "put",
                success: function(res) {
                    console.log("success", res);
                },
                error: function(jqXHR) {
                    console.log("error", jqXHR);
                }
            });
        }

        function printForm(element) {
            if (element.getAttribute('data')) {
                let data = JSON.parse(element.getAttribute('data'));

                if (data) {
                    showLoading();

                    $.ajax({
                        url: '{{ route('export-cutting-form-pdf') }}',
                        type: 'post',
                        data: {
                            id: data.id
                        },
                        xhrFields:
                        {
                            responseType: 'blob'
                        },
                        success: function(res) {
                            hideLoading();

                            if (res) {
                                if (res.status == 400) {
                                    return Swal.fire({
                                        icon: 'error',
                                        title: 'Gagal',
                                        text: res.message,
                                        confirmButtonColor: "#6531a0",
                                    });
                                }

                                var blob = new Blob([res], {type: 'application/pdf'});
                                var link = document.createElement('a');
                                link.href = window.URL.createObjectURL(blob);
                                link.download = data.no_form+" "+data.ws+" "+data.style+" "+data.color+" "+data.panel+".pdf";
                                link.click();
                            }
                        },
                        error: function (jqXHR) {
                            hideLoading();

                            console.error(jqXHR);
                        }
                    });
                }
            }
        }
    </script>
@endsection
