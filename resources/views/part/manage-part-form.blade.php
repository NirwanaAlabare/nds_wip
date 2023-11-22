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
    <div class="card card-sb card-outline">
        <div class="card-header">
            <h5 class="card-title fw-bold">
                Atur Form Part
            </h5>
        </div>
        <div class="card-body">
            <form action="#" method="post">
                <div class="row">
                    <input type="hidden" class="form-control form-control-sm" name="id" id="id" value="{{ $part->id }}" readonly>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label><small><b>Kode Part</b></small></label>
                            <input type="text" class="form-control form-control-sm" name="kode" id="kode" value="{{ $part->kode }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label><small><b>No. WS</b></small></label>
                            <input type="text" class="form-control form-control-sm" name="ws" id="ws" value="{{ $part->act_costing_ws }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label><small><b>Buyer</b></small></label>
                            <input type="text" class="form-control form-control-sm" name="buyer" id="buyer" value="{{ $part->buyer }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label><small><b>Style</b></small></label>
                            <input type="text" class="form-control form-control-sm" name="style" id="style" value="{{ $part->style }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label><small><b>Color</b></small></label>
                            <input type="text" class="form-control form-control-sm" name="color" id="color" value="{{ $part->color }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label><small><b>Panel</b></small></label>
                            <input type="text" class="form-control form-control-sm" name="panel" id="panel" value="{{ $part->panel }}" readonly>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-12 mb-3">
            <div class="card card-primary h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0 fw-bold" style="padding-bottom: 2px">
                        Form Part :
                    </h5>
                </div>
                <div class="card-body">
                    {{-- <div class="row mb-3">
                        <div class="col-6">
                            <p>Form yang dipilih : <span class="fw-bold" id="selected-row-count-1">0</span></p>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-danger btn-sm float-end" onclick="removePartForm()"><i class="fa fa-minus fa-sm"></i> Singkirkan Form</button>
                        </div>
                    </div> --}}
                    <div class="table-responsive">
                        <table id="datatable-selected" class="table table-bordered table-sm w-100">
                            <thead>
                                <tr>
                                    <th>No. Form</th>
                                    <th>Tgl Spreading</th>
                                    <th>No. Meja</th>
                                    <th>Marker</th>
                                    <th>WS</th>
                                    <th>Style</th>
                                    <th>Color</th>
                                    <th>Panel</th>
                                    <th>No. Cut</th>
                                    <th>Size Ratio</th>
                                    <th>Qty Ply</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 mb-3">
            <div class="card card-danger h-100">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-6">
                            <h5 class="card-title fw-bold">
                                Pilih Form Cut :
                            </h5>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    {{-- <div class="d-flex gap-3">
                        <div class="mb-3">
                            <label><small><b>Tgl Awal</b></small></label>
                            <input type="date" class="form-control form-control-sm w-auto" id="tgl_awal" name="tgl_awal" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="mb-3">
                            <label><small><b>Tgl Akhir</b></small></label>
                            <input type="date" class="form-control form-control-sm w-auto" id="tgl_akhir" name="tgl_akhir" value="{{ date('Y-m-d') }}">
                        </div>
                    </div> --}}
                    <div class="row justify-content-between mb-3">
                        <div class="col-6">
                            <p>Form yang dipilih : <span class="fw-bold" id="selected-row-count-2">0</span></p>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-success btn-sm float-end" onclick="addToPartForm()">
                                <i class="fa fa-plus fa-sm"></i> Tambahkan ke Part
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="datatable-select" class="table table-bordered table-sm w-100">
                            <thead>
                                <tr>
                                    <th>No. Form</th>
                                    <th>Tgl Spreading</th>
                                    <th>No. Meja</th>
                                    <th>Marker</th>
                                    <th>WS</th>
                                    <th>Style</th>
                                    <th>Color</th>
                                    <th>Panel</th>
                                    <th>No. Cut</th>
                                    <th>Size Ratio</th>
                                    <th>Qty Ply</th>
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
        $('.select2').select2()

        //Initialize Select2 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })

        //Reset Form
        if (document.getElementById('store-cut-plan')) {
            document.getElementById('store-cut-plan').reset();
        }

        var id = document.getElementById("id").value;
        var ws = document.getElementById("ws").value;
        var panel = document.getElementById("panel").value;

        //Form Part Datatable
        let datatableSelected = $("#datatable-selected").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('manage-part-form') }}/'+id,
                data: function(d) {
                    d.act_costing_ws = $('#ws').val();
                    d.panel = $('#panel').val();
                },
            },
            columns: [
                {
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
                    data: 'no_cut'
                },
                {
                    data: 'marker_details'
                },
                {
                    data: 'qty_ply'
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
                        }

                        return data ? "<span style='color: " + color + "' >" + data.toUpperCase() +
                            "</span>" : "<span style=' color: " + color + "'>-</span>"
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

                        return data ? "<span style='color: " + color + "' >" + data + "</span>" : "<span style=' color: " + color + "'>-</span>"
                    }
                }
            ]
        });

        // Datatable row selection
        datatableSelected.on('click', 'tbody tr', function(e) {
            e.currentTarget.classList.toggle('selected');
            document.getElementById('selected-row-count-1').innerText = $('#datatable-selected').DataTable().rows('.selected').data().length;
        });

        function addToPartForm() {
            let selectedForm = $('#datatable-select').DataTable().rows('.selected').data();
            let partForms = [];
            for (let key in selectedForm) {
                if (!isNaN(key)) {
                    partForms.push({
                        form_id: selectedForm[key]['id'],
                        no_form: selectedForm[key]['no_form']
                    });
                }
            }

            if (partForms.length > 0) {
                $.ajax({
                    type: "POST",
                    url: '{!! route('store-part-form') !!}',
                    data: {
                        part_id: id,
                        partForms: partForms
                    },
                    success: function(res) {
                        if (res.status == 200) {
                            iziToast.success({
                                title: 'Success',
                                message: res.message,
                                position: 'topCenter'
                            });
                        } else {
                            iziToast.error({
                                title: 'Error',
                                message: res.message,
                                position: 'topCenter'
                            });
                        }

                        if (res.table != '') {
                            $('#' + res.table).DataTable().ajax.reload(() => {
                                document.getElementById('selected-row-count-2').innerText = $('#' + res.table).DataTable().rows('.selected').data().length;
                            });

                            $('#datatable-select').DataTable().ajax.reload(() => {
                                document.getElementById('selected-row-count-1').innerText = $('#datatable-select').DataTable().rows('.selected').data().length;
                            });
                        }

                        if (res.additional) {
                            let message = "";

                            if (res.additional['success'].length > 0) {
                                res.additional['success'].forEach(element => {
                                    message += element['no_form'] + " - Berhasil <br>";
                                });
                            }

                            if (res.additional['fail'].length > 0) {
                                res.additional['fail'].forEach(element => {
                                    message += element['no_form'] + " - Gagal <br>";
                                });
                            }

                            if (res.additional['exist'].length > 0) {
                                res.additional['exist'].forEach(element => {
                                    message += element['no_form'] + " - Sudah Ada <br>";
                                });
                            }

                            if ((res.additional['success'].length + res.additional['fail'].length + res.additional['exist'].length) > 0) {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Hasil Transfer',
                                    html: message,
                                    showCancelButton: false,
                                    showConfirmButton: true,
                                    confirmButtonText: 'Oke',
                                });
                            }
                        }
                    },
                    error: function(jqXHR) {
                        let res = jqXHR.responseJSON;
                        let message = '';

                        for (let key in res.errors) {
                            message = res.errors[key];
                        }

                        iziToast.error({
                            title: 'Error',
                            message: 'Terjadi kesalahan. ' + message,
                            position: 'topCenter'
                        });
                    }
                })
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    html: "Harap pilih form cut yang ingin ditambahkan",
                    showCancelButton: false,
                    showConfirmButton: true,
                    confirmButtonText: 'Oke',
                });
            }
        }

        //Form Cut Datatable
        let datatableSelect = $("#datatable-select").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('get-part-form-cut') }}/',
                data: function(d) {
                    d.act_costing_ws = $('#ws').val();
                    d.panel = $('#panel').val();
                },
            },
            columns: [
                {
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
                    data: 'no_cut'
                },
                {
                    data: 'marker_details'
                },
                {
                    data: 'qty_ply'
                },
            ],
            columnDefs: [
                {
                    targets: [2],
                    render: (data, type, row, meta) => {

                        return data ? data.toUpperCase() : "-";
                    }
                }
            ]
        });

        // Datatable row selection
        datatableSelect.on('click', 'tbody tr', function(e) {
            e.currentTarget.classList.toggle('selected');
            document.getElementById('selected-row-count-2').innerText = $('#datatable-select').DataTable().rows('.selected').data().length;
        });

        function removePartForm() {
            let tglPlan = $("#tgl_plan").val();
            let selectedForm = $('#datatable-selected').DataTable().rows('.selected').data();
            let partForms = [];
            for (let key in selectedForm) {
                if (!isNaN(key)) {
                    partForms.push({
                        form_id: selectedForm[key]['id'],
                        no_form: selectedForm[key]['no_form']
                    });
                }
            }

            if (partForms.length > 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'Singkirkan Form yang dipilih?',
                    text: 'Yakin akan menyingkirkan form?',
                    showCancelButton: true,
                    showConfirmButton: true,
                    confirmButtonText: 'Singkirkan',
                    confirmButtonColor: "#d33141",
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "DELETE",
                            url: '{!! route('destroy-part-form') !!}',
                            data: {
                                part_id: id,
                                partForms: partForms
                            },
                            success: function(res) {
                                if (res.status == 200) {
                                    iziToast.success({
                                        title: 'Success',
                                        message: res.message,
                                        position: 'topCenter'
                                    });
                                } else {
                                    iziToast.error({
                                        title: 'Error',
                                        message: res.message,
                                        position: 'topCenter'
                                    });
                                }

                                if (res.table != '') {
                                    $('#' + res.table).DataTable().ajax.reload(() => {document.getElementById('selected-row-count-2').innerText = $('#' + res.table).DataTable().rows('.selected').data().length;
                                    });

                                    $('#datatable-select').DataTable().ajax.reload(() => {
                                        document.getElementById('selected-row-count-1').innerText = $('#datatable-select').DataTable().rows('.selected').data().length;
                                    });
                                }

                                if (res.additional) {
                                    let message = "";

                                    if (res.additional['success'].length > 0) {
                                        res.additional['success'].forEach(element => {
                                            message += element['no_form'] +
                                                " - Berhasil <br>";
                                        });
                                    }

                                    if (res.additional['fail'].length > 0) {
                                        res.additional['fail'].forEach(element => {
                                            message += element['no_form'] + " - Gagal <br>";
                                        });
                                    }

                                    if (res.additional['success'].length + res.additional['fail'].length > 1) {
                                        Swal.fire({
                                            icon: 'info',
                                            title: 'Form berhasil disingkirkan',
                                            html: message,
                                            showCancelButton: false,
                                            showConfirmButton: true,
                                            confirmButtonText: 'Oke',
                                        });
                                    }
                                }
                            },
                            error: function(jqXHR) {
                                let res = jqXHR.responseJSON;
                                let message = '';

                                for (let key in res.errors) {
                                    message = res.errors[key];
                                }

                                iziToast.error({
                                    title: 'Error',
                                    message: 'Terjadi kesalahan. ' + message,
                                    position: 'topCenter'
                                });
                            }
                        })
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    html: "Harap pilih form cut yang akan disingkirkan",
                    showCancelButton: false,
                    showConfirmButton: true,
                    confirmButtonText: 'Oke',
                });
            }
        }
    </script>
@endsection

