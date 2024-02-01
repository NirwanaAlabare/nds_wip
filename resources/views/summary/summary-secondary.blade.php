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
    <div class="card text-center">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="true" href="#">Active</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Link</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link disabled" aria-disabled="true">Disabled</a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <h5 class="card-title">Special title treatment</h5>
            <p class="card-text">With supporting text below as a natural lead-in to additional content.</p>
            <a href="#" class="btn btn-primary">Go somewhere</a>
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
        window.addEventListener("focus", () => {
            $('#datatable').DataTable().ajax.reload(null, false);
        });
        $('#datatable thead tr').clone(true).appendTo('#datatable thead');
        $('#datatable thead tr:eq(1) th').each(function(i) {
            if (i == 1 || i == 2 || i == 3 || i == 4 || i == 5 || i == 6 || i == 7 || i == 10 || i == 11 || i == 12) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm" style="width:100%"/>');

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
            ordering: false,
            serverSide: true,
            ajax: {
                url: '{{ route('summary') }}',
                data: function(d) {
                    d.date = $('#tanggal').val();
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
                    data: 'status'
                },
                {
                    data: 'marker_details'
                },
                {
                    data: undefined
                },
                {
                    data: 'notes'
                },
                {
                    data: 'app'
                },
            ],
            columnDefs: [
                {
                    targets: [9],
                    className: "text-center align-middle",
                    render: (data, type, row, meta) => {
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
                    targets: [11],
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
                    targets: [13],
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
                    targets: [0],
                    render: (data, type, row, meta) => {
                        console.log()

                        let btnEdit = "<a href='javascript:void(0);' class='btn btn-primary btn-sm' onclick='editData(" + JSON.stringify(row) + ", \"detailSpreadingModal\", [{\"function\" : \"dataTableRatioReload()\"}]);'><i class='fa fa-search'></i></a>";

                        let btnProcess = "";

                        if (row.tipe_form_cut == 'MANUAL') {
                            btnProcess = (row.qty_ply > 0 && row.no_meja != '' && row.no_meja != null && row.app == 'Y') || row.status != 'SPREADING' ? `<a class='btn btn-success btn-sm' href='{{ route('process-manual-form-cut') }}/` + row.id + `' data-bs-toggle='tooltip' target='_blank'><i class='fa ` + (row.status == "SELESAI PENGERJAAN" ? `fa-search-plus` : `fa-plus`) + `'></i></a>` : "";
                        } else if (row.tipe_form_cut == 'PILOT') {
                            btnProcess = (row.qty_ply > 0 && row.no_meja != '' && row.no_meja != null && row.app == 'Y') || row.status != 'SPREADING' ? `<a class='btn btn-success btn-sm' href='{{ route('process-pilot-form-cut') }}/` + row.id + `' data-bs-toggle='tooltip' target='_blank'><i class='fa `+(row.status == "SELESAI PENGERJAAN" ? `fa-search-plus` : `fa-plus`)+`'></i></a>` : "";
                        } else {
                            btnProcess = (row.qty_ply > 0 && row.no_meja != '' && row.no_meja != null && row.app == 'Y') || row.status != 'SPREADING' ? `<a class='btn btn-success btn-sm' href='{{ route('process-form-cut-input') }}/` + row.id + `' data-bs-toggle='tooltip' target='_blank'><i class='fa ` + (row.status == "SELESAI PENGERJAAN" ? `fa-search-plus` : `fa-plus`) +`'></i></a>` :"";
                        }

                        return `<div class='d-flex gap-1 justify-content-center'>` + btnEdit + btnProcess + `</div>`;
                    }
                },
                {
                    targets: [4,5],
                    className: "text-nowrap"
                },
                {
                    targets: '_all',
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

                        return '<span style="color:' + color + ';">' + (data ? data : "-") + '</span>';
                    }
                },
            ],
            rowCallback: function(row, data, index) {
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
