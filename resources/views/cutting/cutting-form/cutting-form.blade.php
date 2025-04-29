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
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-cut fa-sm"></i> Form Cutting</h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-end gap-3 mb-3">
                <div class="d-flex align-items-end gap-3 mb-3">
                    <div>
                        <label class="form-label"><small>Tanggal Awal</small></label>
                        <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal" onchange="dataTableReload()">
                    </div>
                    <div>
                        <label class="form-label"><small>Tanggal Akhir</small></label>
                        <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir" value="{{ date('Y-m-d') }}" onchange="dataTableReload()">
                    </div>
                    <div>
                        <button class="btn btn-primary btn-sm" onclick="dataTableReload()"><i class="fa fa-search"></i></button>
                    </div>
                </div>
                <div class="d-flex align-items-end gap-3 mb-3">
                    @if (Auth::user()->type != 'meja')
                        <a href="{{ url('manual-form-cut/create') }}" target="_blank" class="btn btn-sm btn-sb"><i class="fas fa-clipboard-list"></i> Manual</a>
                    @endif
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-hover table w-100">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Tanggal</th>
                            <th>No. Form</th>
                            <th>No. Meja</th>
                            <th>No. Marker</th>
                            <th>No. WS</th>
                            <th>Color</th>
                            <th>Panel</th>
                            <th>Size Ratio</th>
                            <th>Qty Ply</th>
                            <th>Ket.</th>
                            <th class="align-bottom">Status</th>
                            <th class="align-bottom">Approve</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="detailSpreadingModal" tabindex="-1" aria-labelledby="detailSpreadingModalLabel" aria-hidden="true">
            <form action="{{ route('update-spreading') }}" method="post" onsubmit="submitForm(this, event)">
                @method('PUT')
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header bg-sb text-light">
                            <h1 class="modal-title fs-5" id="detailSpreadingModalLabel"><i class="fa fa-search fa-sm"></i> Detail Form</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" style="max-height: 65vh !important;">
                            <div class="row">
                                <input type="hidden" id="edit_id" name="edit_id">
                                <input type="hidden" id="edit_marker_id" name="edit_marker_id">
                                <div class="col-6 col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label"><small>No. Form</small></label>
                                        <input type="text" class="form-control" id="edit_no_form" name="edit_no_form" value="" readonly />
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label"><small>Tanggal</small></label>
                                        <input type="text" class="form-control" id="edit_tgl_form_cut" name="edit_tgl_form_cut" value="" readonly />
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label"><small>Marker</small></label>
                                        <input type="text" class="form-control" id="edit_id_marker" name="edit_id_marker" value="" readonly />
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label"><small>WS</small></label>
                                        <input type="text" class="form-control" id="edit_ws" name="edit_ws" value="" readonly />
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label"><small>Color</small></label>
                                        <input type="text" class="form-control" id="edit_color" name="edit_color" value="" readonly />
                                    </div>
                                </div>
                                <div class="col-6 col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label"><small>Panel</small></label>
                                        <input type="text" class="form-control" id="edit_panel" name="edit_panel" value="" readonly />
                                    </div>
                                </div>
                                <div class="col-4 col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label"><small>P. Marker</small></label>
                                        <input type="text" class="form-control" id="edit_panjang_marker" name="edit_panjang_marker" value="" readonly />
                                    </div>
                                </div>
                                <div class="col-4 col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label"><small>Unit P. Marker</small></label>
                                        <input type="text" class="form-control" id="edit_unit_panjang_marker" name="edit_unit_panjang_marker" value="" readonly />
                                    </div>
                                </div>
                                <div class="col-4 col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label"><small>Comma Marker</small></label>
                                        <input type="text" class="form-control" id="edit_comma_marker" name="edit_comma_marker" value="" readonly />
                                    </div>
                                </div>
                                <div class="col-4 col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label"><small>Unit Comma Marker</small></label>
                                        <input type="text" class="form-control" id="edit_unit_comma_marker" name="edit_unit_comma_marker" value="" readonly />
                                    </div>
                                </div>
                                <div class="col-4 col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label"><small>Lebar Marker</small></label>
                                        <input type="text" class="form-control" id="edit_lebar_marker" name="edit_lebar_marker" value="" readonly />
                                    </div>
                                </div>
                                <div class="col-4 col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label"><small>Unit Lebar Marker</small></label>
                                        <input type="text" class="form-control" id="edit_unit_lebar_marker" name="edit_unit_lebar_marker" value="" readonly />
                                    </div>
                                </div>
                                <div class="col-4 col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label"><small>PO Marker</small></label>
                                        <input type="text" class="form-control" id="edit_po_marker" name="edit_po_marker" value="" readonly />
                                    </div>
                                </div>
                                <div class="col-4 col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label"><small>Gelar QTY</small></label>
                                        <input type="text" class="form-control" id="edit_gelar_qty" name="edit_gelar_qty" value="" readonly />
                                    </div>
                                </div>
                                <div class="col-4 col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label"><small>Ply QTY</small></label>
                                        <input type="text" class="form-control" id="edit_qty_ply" name="edit_qty_ply" value="" readonly />
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label"><small>Urutan Marker</small></label>
                                        <input type="text" class="form-control" id="edit_urutan_marker" name="edit_urutan_marker" value="" readonly />
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label"><small>Cons. Marker</small></label>
                                        <input type="text" class="form-control" id="edit_cons_marker" name="edit_cons_marker" value="" readonly />
                                    </div>
                                </div>
                                <div class="col-4 col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label"><small>Tipe Marker</small></label>
                                        <input type="text" class="form-control" id="edit_tipe_marker" name="edit_tipe_marker" value="" readonly />
                                    </div>
                                </div>
                                <div class="col-4 col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label"><small>Meja</small></label>
                                        <input type="text" class="form-control" id="edit_nama_meja" name="edit_nama_meja" value="" readonly />
                                    </div>
                                </div>
                                <div class="col-4 col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label"><small>Keterangan</small></label>
                                        <textarea class="form-control" id="edit_notes" name="edit_notes" readonly></textarea>
                                    </div>
                                </div>
                                <div class="col-md-12 table-responsive">
                                    <table id="datatable-ratio" class="table table-bordered table-striped table w-100">
                                        <thead>
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
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-times fa-sm"></i> Tutup</button>
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
        $('.select2').select2()
        $('.select2bs4').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#editMejaModal")
        })
    </script>

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

        $('#datatable thead tr').clone(true).appendTo('#datatable thead');
        $('#datatable thead tr:eq(1) th').each(function(i) {
            if (i != 0 && i != 8 && i != 9 && i != 12) {
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

        let datatable = $("#datatable").DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            scrollX: "500px",
            scrollY: "500px",
            pageLength: 50,
            ajax: {
                url: '{{ route('form-cut-input') }}',
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
                    data: 'color'
                },
                {
                    data: 'panel'
                },
                {
                    data: 'marker_details'
                },
                {
                    data: 'total_lembar'
                },
                {
                    data: 'notes'
                },
                {
                    data: 'status'
                },
                {
                    data: 'app'
                },
            ],
            columnDefs: [
                {
                    targets: [0],
                    render: (data, type, row, meta) => {
                        let btnEdit = "<a href='javascript:void(0);' class='btn btn-primary btn-sm' onclick='editData(" + JSON.stringify(row) + ", \"detailSpreadingModal\", [{\"function\" : \"dataTableRatioReload()\"}]);'><i class='fa fa-search'></i></a>";

                        let btnProcess = "";

                        if (row.tipe_form_cut == 'MANUAL') {
                            btnProcess = (row.qty_ply > 0 && row.no_meja != '' && row.no_meja != null && row.app == 'Y') || row.status != 'SPREADING' ? `<a class='btn btn-success btn-sm' href='{{ route('process-manual-form-cut') }}/` +row.id + `' data-bs-toggle='tooltip' target='_blank'><i class='fa ` + (row.status == "SELESAI PENGERJAAN" ? `fa-search-plus` : `fa-plus`) +`'></i></a>` : `<button class='btn btn-success btn-sm' data-bs-toggle='tooltip' target='_blank' disabled><i class='fa ` + (row.status == "SELESAI PENGERJAAN" ? `fa-search-plus` : `fa-plus`) +`'></i></button>`;
                        } else if (row.tipe_form_cut == 'PILOT') {
                            btnProcess = (row.qty_ply > 0 && row.no_meja != '' && row.no_meja != null && row.app == 'Y') || row.status != 'SPREADING' ? `<a class='btn btn-success btn-sm' href='{{ route('process-pilot-form-cut') }}/` + row.id + `' data-bs-toggle='tooltip' target='_blank'><i class='fa `+(row.status == "SELESAI PENGERJAAN" ? `fa-search-plus` : `fa-plus`)+`'></i></a>` : `<button class='btn btn-success btn-sm' data-bs-toggle='tooltip' target='_blank' disabled><i class='fa `+(row.status == "SELESAI PENGERJAAN" ? `fa-search-plus` : `fa-plus`)+`'></i></button>`;
                        } else {
                            btnProcess = (row.qty_ply > 0 && row.no_meja != '' && row.no_meja != null && row.app == 'Y') || row.status != 'SPREADING' ? `<a class='btn btn-success btn-sm' href='{{ route('process-form-cut-input') }}/` + row.id + `' data-bs-toggle='tooltip' target='_blank'><i class='fa ` + (row.status == "SELESAI PENGERJAAN" ? `fa-search-plus` : `fa-plus`) + `'></i></a>` : `<button class='btn btn-success btn-sm' data-bs-toggle='tooltip' target='_blank' disabled><i class='fa ` + (row.status == "SELESAI PENGERJAAN" ? `fa-search-plus` : `fa-plus`) + `'></i></button>`;
                        }

                        return `<div class='d-flex gap-1 justify-content-center'>` + btnEdit + btnProcess + `</div>`;
                    }
                },
                {
                    targets: [4],
                    render: (data, type, row, meta) => {
                        return data ? `<a class='fw-bold' href='{{ route('edit-marker') }}/ `+row.marker_id+`' target='_blank'><u>`+data+`</u></a>` : "-";
                    }
                },
                {
                    targets: [11],
                    className: "text-center align-middle",
                    render: (data, type, row, meta) => {
                        console.log(row.marker_details);

                        icon = "";

                        switch (data) {
                            case "SPREADING":
                                if (row.app != 'Y') {
                                    icon = `<i class="fas fa-file fa-lg" style="color: #616161;"></i>`;
                                } else {
                                    icon = `<i class="fas fa-file fa-lg"></i>`;
                                }
                                break;
                            case "PENGERJAAN MARKER":
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
                    targets: [12],
                    className: "text-center align-middle",
                    render: (data, type, row, meta) => {
                        icon = "";
                        color = "";

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
                        } else {
                            if (row.app != 'Y') {
                                color = '#616161';
                            }
                        }

                        switch (data) {
                            case "Y":
                                icon = `<i class="fas fa-check fa-lg" style="color: ` + color + `;"></i>`;
                                break;
                            case "N":
                                icon = `<i class="fas fa-times fa-lg" style="color: ` + color + `;"></i>`;
                                break;
                            default:
                                icon = `<i class="fas fa-minus fa-lg" style="color: ` + color + `;"></i>`;
                                break;
                        }

                        return icon;
                    }
                },
                {
                    targets: '_all',
                    className: "text-nowrap",
                    render: (data, type, row, meta) => {
                        var color = 'black';

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
                        } else {
                            if (row.app != 'Y') {
                                color = '#616161';
                            }
                        }

                        return '<span style="font-weight: 600; color:' + color + ';">' + (data ? data : "-") + '</span>';
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

        let datatableRatio = $("#datatable-ratio").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('get-form-cut-ratio') }}',
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
    </script>
@endsection
