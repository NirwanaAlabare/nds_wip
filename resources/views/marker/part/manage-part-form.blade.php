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
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-bold">
                    <i class="fa-solid fa-file-circle-plus"></i> Tambah Form ke Part
                </h5>
                <a href="{{ route('part') }}" class="btn btn-sm btn-primary">
                    <i class="fa fa-reply"></i> Kembali ke Part
                </a>
            </div>
        </div>
        <div class="card-body">
            <form action="#" method="post">
                <div class="row">
                    <input type="hidden" class="form-control form-control-sm" name="id" id="id" value="{{ $part->id }}" readonly>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label><small><b>Kode Part</b></small></label>
                            <input type="text" class="form-control form-control-sm" name="kode" id="kode" value="{{ $part->kode }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label><small><b>No. WS</b></small></label>
                            <input type="text" class="form-control form-control-sm" name="ws" id="ws" value="{{ $part->act_costing_ws }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label><small><b>Buyer</b></small></label>
                            <input type="text" class="form-control form-control-sm" name="buyer" id="buyer" value="{{ $part->buyer }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label><small><b>Style</b></small></label>
                            <input type="text" class="form-control form-control-sm" name="style" id="style" value="{{ $part->style }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label><small><b>Color</b></small></label>
                            <input type="text" class="form-control form-control-sm" name="color" id="color" value="{{ $part->color }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label><small><b>Panel</b></small></label>
                            <input type="text" class="form-control form-control-sm" name="panel" id="panel" value="{{ $part->panel }}" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label><small><b>Parts</b></small></label>
                            <input type="text" class="form-control form-control-sm" name="part_details" id="part_details" value="{{ $part->part_details }}" readonly>
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
                    <div class="row align-items-center">
                        <div class="col-6">
                            <h5 class="card-title fw-bold">
                                <i class="fa-regular fa-hourglass-half"></i> Form Cut Pending :
                            </h5>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    {{-- <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <h5 class="mb-1">Harap cek kembali data <strong>Form</strong> jika <strong>Form</strong> tidak muncul.</h5>
                        <hr>
                        <p>Mungkin <a href="{{ route('marker-panel') }}"><strong>Panel Marker</strong></a> dari <strong>Form</strong> yang tidak muncul tersebut tidak sesuai dengan <strong>Panel</strong> dari <strong>Part</strong> yang telah dibuat. Jika kesulitan bisa hubungi IT.</p>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" onclick="updatePartFormInfoReaded()"></button>
                    </div> --}}
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
                    <div class="row justify-content-between mb-3 align-items-center">
                        <div class="col-6">
                            <p class="mb-0">Form yang dipilih : <span class="fw-bold" id="selected-row-count-2">0</span></p>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-success btn-sm float-end fw-bold" onclick="addToPartForm(this)">
                                <i class="fa fa-arrow-right fa-sm"></i> FORM IN
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="datatable-select" class="table table-bordered table w-100">
                            <thead>
                                <tr>
                                    <th>Tgl Spreading</th>
                                    <th>No. Form</th>
                                    <th>No. Meja</th>
                                    <th>No. Cut</th>
                                    <th>Style</th>
                                    <th>Color</th>
                                    <th>Panel</th>
                                    <th>Qty Ply</th>
                                    <th>Size Ratio</th>
                                    <th>Marker</th>
                                    <th>WS</th>
                                    <th>Type</th>
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
            <div class="card card-success h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0 fw-bold" style="padding-bottom: 2px">
                        <i class="fa fa-check"></i> Form Cut In :
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row justify-content-between align-items-center mb-3">
                        <div class="col-6">
                            <p class="mb-0">Form yang dipilih : <span class="fw-bold" id="selected-row-count-1">0</span></p>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-primary btn-sm float-end fw-bold" onclick="removePartForm()">
                                <i class="fa fa-arrow-left fa-sm"></i> FORM OUT
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="datatable-selected" class="table table-bordered table w-100">
                            <thead>
                                <tr>
                                    <th>Tgl Spreading</th>
                                    <th>No. Form</th>
                                    <th>No. Meja</th>
                                    <th>No. Cut</th>
                                    <th>Style</th>
                                    <th>Color</th>
                                    <th>Panel</th>
                                    <th>Qty Ply</th>
                                    <th>Size Ratio</th>
                                    <th>Marker</th>
                                    <th>WS</th>
                                    <th>Type</th>
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
            paging: false,
            ajax: {
                url: '{{ route('manage-part-form') }}/'+id,
                data: function(d) {
                    d.act_costing_ws = $('#ws').val();
                    d.panel = $('#panel').val();
                },
            },
            columns: [
                {
                    data: 'tgl_mulai_form'
                },
                {
                    data: 'no_form'
                },
                {
                    data: 'nama_meja'
                },
                {
                    data: 'no_cut'
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
                    data: 'qty_ply'
                },
                {
                    data: 'marker_details',
                    searchable: false
                },
                {
                    data: 'id_marker'
                },
                {
                    data: 'act_costing_ws'
                },
                {
                    data: 'type'
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

                        return data ? "<span style='color: " + color + "' >" + data.toUpperCase() + "</span>" : "<span style=' color: " + color + "'>-</span>"
                    }
                },
                {
                    targets: [9],
                    render: (data, type, row, meta) => {
                        return `
                            <a href='{{ route('edit-marker') }}/ `+row.marker_id+`' target='_blank'>`+data+`</a>
                        `;
                    }
                },
                {
                    targets: '_all',
                    className: 'text-nowrap',
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

        $('#datatable-selected thead tr').clone(true).appendTo('#datatable-selected thead');
        $('#datatable-selected thead tr:eq(1) th').each(function(i) {
            if (i == 0 || i == 1 || i == 2 || i == 3 || i == 4 || i == 5 || i == 6 || i == 7 || i == 9 || i == 10) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm" />');

                $('input', this).on('keyup change', function() {
                    if (datatableSelected.column(i).search() !== this.value) {
                        datatableSelected
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            } else {
                $(this).empty();
            }
        });

        function addToPartForm(element) {
            element.setAttribute('disabled', true);

            let selectedForm = $('#datatable-select').DataTable().rows('.selected').data();
            let partForms = [];
            for (let key in selectedForm) {
                if (!isNaN(key)) {
                    partForms.push({
                        form_id: selectedForm[key]['id'],
                        no_form: selectedForm[key]['no_form'],
                        type: selectedForm[key]['type']
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
                        element.removeAttribute('disabled');

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
                        element.removeAttribute('disabled');

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
                element.removeAttribute('disabled');

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
            paging: false,
            ajax: {
                url: '{{ route('get-part-form-cut') }}/',
                data: function(d) {
                    d.act_costing_ws = $('#ws').val();
                    d.panel = $('#panel').val();
                },
            },
            columns: [
                {
                    data: 'tgl_mulai_form'
                },
                {
                    data: 'no_form'
                },
                {
                    data: 'nama_meja'
                },
                {
                    data: 'no_cut'
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
                    data: 'qty_ply',
                },
                {
                    data: 'marker_details',
                    searchable: false
                },
                {
                    data: 'id_marker'
                },
                {
                    data: 'ws'
                },
                {
                    data: 'type'
                },
            ],
            columnDefs: [
                {
                    targets: "_all",
                    className: "text-nowrap"
                },
                {
                    targets: [1],
                    className: "text-nowrap",
                    render: (data, type, row, meta) => {
                        if (data) {
                            if (row.type == 'PIECE') {
                                return `
                                    <a href='{{ route('process-cutting-piece') }}/ `+row.id+`' target='_blank'>`+data+`</a>
                                `;
                            }
                        }

                        return data ? data : '-';
                    }
                },
                {
                    targets: [2],
                    className: "text-nowrap",
                    render: (data, type, row, meta) => {
                        return data ? data.toUpperCase() : "-";
                    }
                },
                {
                    targets: [9],
                    className: "text-nowrap",
                    render: (data, type, row, meta) => {
                        if (data) {
                            return `
                                <a href='{{ route('edit-marker') }}/ `+row.marker_id+`' target='_blank'>`+data+`</a>
                            `;
                        }

                        return data ? data : '-';
                    }
                },
            ]
        });

        // Datatable row selection
        datatableSelect.on('click', 'tbody tr', function(e) {
            e.currentTarget.classList.toggle('selected');
            document.getElementById('selected-row-count-2').innerText = $('#datatable-select').DataTable().rows('.selected').data().length;
        });

        $('#datatable-select thead tr').clone(true).appendTo('#datatable-select thead');
        $('#datatable-select thead tr:eq(1) th').each(function(i) {
            if (i == 0 || i == 1 || i == 2 || i == 3 || i == 4 || i == 5 || i == 6 || i == 7 || i == 9 || i == 10) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm" />');

                $('input', this).on('keyup change', function() {
                    if (datatableSelect.column(i).search() !== this.value) {
                        datatableSelect
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            } else {
                $(this).empty();
            }
        });

        function removePartForm() {
            let tglPlan = $("#tgl_plan").val();
            let selectedForm = $('#datatable-selected').DataTable().rows('.selected').data();
            let partForms = [];
            for (let key in selectedForm) {
                if (!isNaN(key)) {
                    partForms.push({
                        form_id: selectedForm[key]['id'],
                        no_form: selectedForm[key]['no_form'],
                        type: selectedForm[key]['type']
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

