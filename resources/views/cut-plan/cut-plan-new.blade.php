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
                                        <th>Color</th>
                                        <th>Panel</th>
                                        <th>Status</th>
                                        <th>Size Ratio</th>
                                        <th>Qty Ply</th>
                                        <th>Qty Output</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    {{-- <button type="submit" class="btn btn-sb">Simpan</button> --}}
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="exampleModalEdit" tabindex="-1" role="dialog" aria-labelledby="exampleModalEditLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable" style="max-width: 55%;">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h1 class="modal-title fs-5" id="exampleModalEditLabel"></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class='row'>
                        <div class='col-sm-3'>
                            <div class='form-group'>
                                <label class='form-label'><small>Gramasi</small></label>
                                <input type='text' class='form-control' id='txt_gramasi' name='txt_gramasi'
                                    value = ''>
                                <input type='hidden' class='form-control' id='id_c' name='id_c' value = ''>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-sb">Simpan</button>
                </div>
            </div>
        </div>
    </div>



    <div class="card card-sb card-outline">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0">Data Cutting Plan</h5>
        </div>
        <div class="card-body">
            <a href="{{ route('create-cut-plan') }}" class="btn btn-primary btn-sm mb-3">
                <i class="fa fa-cog"></i>
                Atur Cutting Plan
            </a>
            <div class="d-flex align-items-end gap-3 mb-3">
                <div class="mb-3">
                    <label class="form-label"><small>Tgl Plan Awal</small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal"
                        value="{{ date('Y-m-d') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label"><small>Tgl Plan Akhir</small></label>
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
                            <th>Tgl Plan</th>
                            <th>Buyer</th>
                            <th>WS</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Panel</th>
                            <th>Qty Output</th>
                            <th>Act</th>
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
        let datatable = $("#datatable").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('cut-plan-new') }}',
                data: function(d) {
                    d.tgl_awal = $('#tgl-awal').val();
                    d.tgl_akhir = $('#tgl-akhir').val();
                },
            },
            columns: [{
                    data: 'tgl_plan_fix',
                    searchable: false
                },
                {
                    data: 'buyer'
                },
                {
                    data: 'act_costing_ws'
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
                    data: 'qty_output'
                },
            ],
            columnDefs: [{
                targets: [7],
                render: (data, type, row, meta) => {
                    return `
                        <div class='d-flex gap-1 justify-content-center'>
                            <a class='btn btn-primary btn-sm' onclick='editData(` + JSON.stringify(row) +
                        `, \"cutPlanDetailModal\", [{\"function\" : \"datatableFormReload()\"}]);'>
                                <i class='fa fa-search'></i>
                            </a>
                            <a class='btn btn-info btn-sm' data-bs-toggle="modal" data-bs-target="#exampleModalEdit"
                            onclick='edit(` + row.tgl_plan + `,"` + row.buyer + `");'>
                                        <i class='fa fa-edit'></i>
                                    </a>
                        </div>
                    `;
                }
            }, ]
        });

        function filterTable() {
            datatable.ajax.reload();
        }

        function edit(tgl_plan, buyer) {
            $("#exampleModalEditLabel").html('Marker Edit');
            // jQuery.ajax({
            //     url: '{{ route('show_gramasi') }}',
            //     method: 'POST',
            //     data: {
            //         id_c: id_c
            //     },
            //     dataType: 'json',
            //     success: function(response) {
            //         document.getElementById('txt_gramasi').value = response.gramasi;
            //         document.getElementById('id_c').value = response.id;
            //     },
            //     error: function(request, status, error) {
            //         alert(request.responseText);
            //     },
            // });
        };



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
                    targets: [7],
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
    </script>
@endsection
