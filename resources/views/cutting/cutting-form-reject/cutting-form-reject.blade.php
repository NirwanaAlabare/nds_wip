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
            <h5 class="card-title"><i class="fa-solid fa-file-circle-exclamation"></i> Form Ganti Reject</h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-end mb-3">
                <div class="d-flex justify-content-start align-items-end gap-3">
                    <div>
                        <label class="form-label">Dari</label>
                        <input type="date" class="form-control" value="" id="date-from" name="dateFrom">
                    </div>
                    <span class="mb-2"> - </span>
                    <div>
                        <label class="form-label">Sampai</label>
                        <input type="date" class="form-control" value="" id="date-to" name="dateTo">
                    </div>
                </div>
            </div>
            <table class="table table-bordered" id="cutting-reject-table">
                <thead>
                    <th>Action</th>
                    <th>Tanggal</th>
                    <th>No. Form</th>
                    <th>Panel</th>
                    <th>No. WS</th>
                    <th>Style</th>
                    <th>Color</th>
                    <th>Size</th>
                    <th>Qty</th>
                </thead>
                <tbody>
                </tbody>
            </table>
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

        let cuttingRejectTable = $("#cutting-reject-table").DataTable({
            processing: true,
            ordering: false,
            serverSide: true,
            ajax: {
                url: '{{ route('cutting-reject') }}',
                data: function(d) {
                    d.dateFrom = $('#date-from').val();
                    d.dateTo = $('#date-to').val();
                },
            },
            columns: [
                {
                    data: 'id'
                },
                {
                    data: 'tanggal'
                },
                {
                    data: 'no_form'
                },
                {
                    data: 'panel'
                },
                {
                    data: 'act_costing_ws'
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
                    data: 'qty'
                },
            ],
            columnDefs: [
                {
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
                        } else {
                            if (row.app != 'Y') {
                                color = '#616161';
                            }
                        }

                        return data ? "<span style='color: " + color + "'>" + data.toUpperCase() +
                            "</span>" : "<span style='color: " + color + "'>-</span>"
                    }
                },
                {
                    targets: [7],
                    className: "text-center align-middle",
                    render: (data, type, row, meta) => {
                        icon = "";

                        switch (data) {
                            case "SPREADING":
                                if (row.app != 'Y') {
                                    icon = `<i class="fas fa-file fa-lg" style="color: #616161;"></i>`;
                                } else {
                                    icon = `<i class="fas fa-file fa-lg" style="color: #616161;"></i>`;
                                }
                                break;
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
                    targets: [10],
                    className: "text-center align-middle",
                    render: (data, type, row, meta) => {
                        icon = "";
                        color = "";

                        if (row.status == 'SELESAI PENGERJAAN') {
                            color = '#087521';
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
                                icon = `<i class="fas fa-check fa-lg" style="color: `+color+`;"></i>`;
                                break;
                            case "N":
                                icon = `<i class="fas fa-times fa-lg" style="color: `+color+`;"></i>`;
                                break;
                            default:
                                icon = `<i class="fas fa-minus fa-lg" style="color: `+color+`;"></i>`;
                                break;
                        }

                        return icon;
                    }
                },
                {
                    targets: [11],
                    render: (data, type, row, meta) => {
                        let btnEdit = "<a href='javascript:void(0);' class='btn btn-primary btn-sm' onclick='editData(" +
                            JSON.stringify(row) +
                            ", \"detailSpreadingModal\", [{\"function\" : \"dataTableRatioReload()\"}]);'><i class='fa fa-search'></i></a>";

                        let btnProcess = (row.qty_ply > 0 && row.no_meja != '' && row.no_meja != null && row.app == 'Y') || row.status != 'SPREADING' ?
                            `<a class='btn btn-success btn-sm' href='{{ route('process-form-cut-input') }}/` +
                            row.id +
                            `' data-bs-toggle='tooltip' target='_blank'><i class='fa `+(row.status == "SELESAI PENGERJAAN" ? `fa-search-plus` : `fa-plus`)+`'></i></a>` :
                            "";

                        return `<div class='d-flex gap-1 justify-content-center'>` + btnEdit + btnProcess +
                            `</div>`;
                    }
                },
                {
                    targets: '_all',
                    render: (data, type, row, meta) => {
                        var color = 'black';

                        if (row.status == 'SELESAI PENGERJAAN') {
                            color = '#087521';
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

                        return '<span style="color:' + color + '">' + data + '</span>';
                    }
                }
            ]
        });
    </script>
@endsection
