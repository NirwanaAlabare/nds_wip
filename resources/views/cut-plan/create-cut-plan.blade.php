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
    <form action="{{ route('store-marker') }}" method="post" id="store-marker" onsubmit="submitMarkerForm(this, event)">
        @csrf
        <div class="card card-sb card-outline">
            <div class="card-header">
                <h5 class="card-title fw-bold">
                    Manage Cutting Plan
                </h5>
            </div>
            <div class="card-body">
                <form action="#" method="post">
                    <div class="mb-3">
                        <label>Tgl Plan</label>
                        <input type="date" class="form-control" name="tgl_plan" id="tgl_plan" min='{{ date('Y-m-d') }}' value="{{ date('Y-m-d') }}">
                    </div>
                </form>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-12 mb-3">
                <div class="card card-danger h-100">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-6">
                                <h5 class="card-title fw-bold">
                                    Select Form Cut :
                                </h5>
                            </div>
                            <div class="col-6">
                                <input type="date" class="form-control form-control-sm w-auto float-end" id="tgl_form" name="tgl_form" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row justify-content-between mb-3">
                            <div class="col-6">
                                <p>Selected Form : <span class="fw-bold" id="selected-row-count-1">0</span></p>
                            </div>
                            <div class="col-6">
                                <button class="btn btn-success btn-sm float-end" onclick="addToCutPlan()"><i class="fa fa-plus fa-sm"></i> Add To Cut Plan</button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="datatable-select" class="table table-bordered table-sm w-100">
                                <thead>
                                    <tr>
                                        <th>No Form</th>
                                        <th>Tgl Form</th>
                                        <th>No. Meja</th>
                                        <th>Marker</th>
                                        <th>WS</th>
                                        <th>Color</th>
                                        <th>Panel</th>
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
                <div class="card card-success h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-2 fw-bold" style="padding-bottom: 2px">
                            Current Cut Plan :
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-6">
                                <p>Selected Cut Plan : <span class="fw-bold" id="selected-row-count-2">0</span></p>
                            </div>
                            <div class="col-6">
                                <button class="btn btn-danger btn-sm float-end" onclick="removeCutPlan()"><i class="fa fa-minus fa-sm"></i> Remove Cut Plan</button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="datatable-selected" class="table table-bordered table-sm w-100">
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
        {{-- <button class="btn btn-sb btn-block" type="submit">SIMPAN</button> --}}
    </form>
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

        document.getElementById("tgl_plan").addEventListener("change", function () {
            let todayDate = new Date();
            let selectedDate = new Date(this.value);

            if (selectedDate < todayDate) {
                $("#tgl_plan").val(formatDate(todayDate));
            }

            datatableSelect.ajax.reload(() => {
                $('#datatable-select').DataTable().ajax.reload(() => {
                    document.getElementById('selected-row-count-1').innerText = $('#datatable-select').DataTable().rows('.selected').data().length;
                });
            });

            datatableSelected.ajax.reload(() => {
                $('#datatable-selected').DataTable().ajax.reload(() => {
                    document.getElementById('selected-row-count-2').innerText = $('#datatable-selected').DataTable().rows('.selected').data().length;
                });
            });
        });

        document.getElementById("tgl_form").addEventListener("change", function () {
            datatableSelect.ajax.reload(() => {
                $('#datatable-select').DataTable().ajax.reload(() => {
                    document.getElementById('selected-row-count-1').innerText = $('#datatable-select').DataTable().rows('.selected').data().length;
                });
            });
        });

        let datatableSelect = $("#datatable-select").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('create-cut-plan') }}',
                data: function(d) {
                    d.tgl_plan = $('#tgl_plan').val();
                    d.tgl_form = $('#tgl_form').val();
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
                    data: 'color'
                },
                {
                    data: 'panel'
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
                },
            ]
        });

        // Datatable row selection
        datatableSelect.on('click', 'tbody tr', function (e) {
            e.currentTarget.classList.toggle('selected');
            document.getElementById('selected-row-count-1').innerText = $('#datatable-select').DataTable().rows('.selected').data().length;
        });

        function addToCutPlan() {
            let tglPlan = $("#tgl_plan").val();
            let selectedForm = $('#datatable-select').DataTable().rows('.selected').data();
            let formCutPlan = [];
            for (let key in selectedForm) {
                if (!isNaN(key)) {
                    formCutPlan.push({
                        no_form: selectedForm[key]['no_form']
                    });
                }
            }

            if (tglPlan && formCutPlan.length > 0) {
                $.ajax({
                    type: "POST",
                    url: '{!! route('store-cut-plan') !!}',
                    data: {
                        tgl_plan: tglPlan,
                        formCutPlan: formCutPlan
                    }, success: function (res) {
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
                            $('#'+res.table).DataTable().ajax.reload(() => {
                                document.getElementById('selected-row-count-2').innerText = $('#'+res.table).DataTable().rows('.selected').data().length;
                            });

                            $('#datatable-select').DataTable().ajax.reload(() => {
                                document.getElementById('selected-row-count-1').innerText = $('#datatable-select').DataTable().rows('.selected').data().length;
                            });
                        }

                        if (res.additional) {
                            let message = "";

                            if (res.additional['success'].length > 0) {
                                res.additional['success'].forEach(element => {
                                    message += element['no_form']+" - Berhasil <br>";
                                });
                            }

                            if (res.additional['fail'].length > 0) {
                                res.additional['fail'].forEach(element => {
                                    message += element['no_form']+" - Gagal <br>";
                                });
                            }

                            if (res.additional['exist'].length > 0) {
                                res.additional['exist'].forEach(element => {
                                    message += element['no_form']+" - Sudah Ada <br>";
                                });
                            }

                            if ((res.additional['success'].length+res.additional['fail'].length+res.additional['exist'].length) > 1) {
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
                    }, error: function (jqXHR) {
                        let res = jqXHR.responseJSON;
                        let message = '';

                        for (let key in res.errors) {
                            message = res.errors[key];
                        }

                        iziToast.error({
                            title: 'Error',
                            message: 'Terjadi kesalahan. '+message,
                            position: 'topCenter'
                        });
                    }
                })
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    html: "Harap isi tanggal plan dan tentukan form cut nya",
                    showCancelButton: false,
                    showConfirmButton: true,
                    confirmButtonText: 'Oke',
                });
            }
        }

        let datatableSelected = $("#datatable-selected").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('get-selected-form') }}',
                data: function(d) {
                    d.tgl_plan = $('#tgl_plan').val();
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

                        return data ? "<span style='color: "+ color + "' >" + data.toUpperCase() + "</span>" : "<span style=' color: " + color + "'>-</span>"
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

                        return data ? "<span style='color: "+ color + "' >" + data + "</span>" : "<span style=' color: " + color + "'>-</span>"
                    }
                }
            ]
        });

        function removeCutPlan() {
            let tglPlan = $("#tgl_plan").val();
            let selectedForm = $('#datatable-selected').DataTable().rows('.selected').data();
            let formCutPlan = [];
            for (let key in selectedForm) {
                if (!isNaN(key)) {
                    formCutPlan.push({
                        no_form: selectedForm[key]['no_form'],
                        status: selectedForm[key]['status']
                    });
                }
            }

            if (tglPlan && formCutPlan.length > 0 && !(formCutPlan.some(item => item.status.includes('PENGERJAAN FORM CUTTING') || item.status.includes('PENGERJAAN FORM CUTTING DETAIL') || item.status.includes('PENGERJAAN FORM CUTTING SPREAD') || item.status.includes('SELESAI PENGERJAAN')))) {
                Swal.fire({
                    icon: 'info',
                    title: 'Singkirkan Plan yang dipilih?',
                    text: 'Yakin akan menyingkirkan cut plan?',
                    showCancelButton: true,
                    showConfirmButton: true,
                    confirmButtonText: 'Singkirkan',
                    confirmButtonColor: "#d33141",
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "DELETE",
                            url: '{!! route('destroy-cut-plan') !!}',
                            data: {
                                tgl_plan: tglPlan,
                                formCutPlan: formCutPlan
                            }, success: function (res) {
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
                                    $('#'+res.table).DataTable().ajax.reload(() => {
                                        document.getElementById('selected-row-count-2').innerText = $('#'+res.table).DataTable().rows('.selected').data().length;
                                    });

                                    $('#datatable-select').DataTable().ajax.reload(() => {
                                        document.getElementById('selected-row-count-1').innerText = $('#datatable-select').DataTable().rows('.selected').data().length;
                                    });
                                }

                                if (res.additional) {
                                    let message = "";

                                    if (res.additional['success'].length > 0) {
                                        res.additional['success'].forEach(element => {
                                            message += element['no_form']+" - Berhasil <br>";
                                        });
                                    }

                                    if (res.additional['fail'].length > 0) {
                                        res.additional['fail'].forEach(element => {
                                            message += element['no_form']+" - Gagal <br>";
                                        });
                                    }

                                    if (res.additional['success'].length+res.additional['fail'].length > 1) {
                                        Swal.fire({
                                            icon: 'info',
                                            title: 'Plan berhasil disingkirkan',
                                            html: message,
                                            showCancelButton: false,
                                            showConfirmButton: true,
                                            confirmButtonText: 'Oke',
                                        });
                                    }
                                }
                            }, error: function (jqXHR) {
                                let res = jqXHR.responseJSON;
                                let message = '';

                                for (let key in res.errors) {
                                    message = res.errors[key];
                                }

                                iziToast.error({
                                    title: 'Error',
                                    message: 'Terjadi kesalahan. '+message,
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
                    html: "Harap isi tanggal plan dan tentukan form cut yang belum diproses",
                    showCancelButton: false,
                    showConfirmButton: true,
                    confirmButtonText: 'Oke',
                });
            }
        }

        // Datatable selected row selection
        datatableSelected.on('click', 'tbody tr', function (e) {
            e.currentTarget.classList.toggle('selected');
            document.getElementById('selected-row-count-2').innerText = $('#datatable-selected').DataTable().rows('.selected').data().length;
        });

        // function submitCutPlanForm(e, evt) {
        //     evt.preventDefault();

        //     clearModified();

        //     $.ajax({
        //         url: e.getAttribute('action'),
        //         type: e.getAttribute('method'),
        //         data: new FormData(e),
        //         processData: false,
        //         contentType: false,
        //         success: async function(res) {
        //             if (res.status == 200) {
        //                 e.reset();

        //                 Swal.fire({
        //                     icon: 'success',
        //                     title: 'Data Cut Plan berhasil disimpan',
        //                     text: res.message,
        //                     showCancelButton: false,
        //                     showConfirmButton: true,
        //                     confirmButtonText: 'Oke',
        //                     timer: 5000,
        //                     timerProgressBar: true
        //                 })
        //             } else {
        //                 iziToast.error({
        //                     title: 'Error',
        //                     message: res.message,
        //                     position: 'topCenter'
        //                 });
        //             }

        //             $('#datatable-selected').DataTable().ajax.reload();

        //             if (Object.keys(res.additional).length > 0 ) {
        //                 for (let key in res.additional) {
        //                     if (document.getElementById(key)) {
        //                         document.getElementById(key).classList.add('is-invalid');

        //                         if (res.additional[key].hasOwnProperty('message')) {
        //                             document.getElementById(key+'_error').classList.remove('d-none');
        //                             document.getElementById(key+'_error').innerHTML = res.additional[key]['message'];
        //                         }

        //                         if (res.additional[key].hasOwnProperty('value')) {
        //                             document.getElementById(key).value = res.additional[key]['value'];
        //                         }

        //                         modified.push(
        //                             [key, '.classList', '.remove(', "'is-invalid')"],
        //                             [key+'_error', '.classList', '.add(', "'d-none')"],
        //                             [key+'_error', '.innerHTML = ', "''"],
        //                         )
        //                     }
        //                 }
        //             }
        //         }, error: function (jqXHR) {
        //             let res = jqXHR.responseJSON;
        //             let message = '';
        //             let i = 0;

        //             for (let key in res.errors) {
        //                 message = res.errors[key];
        //                 document.getElementById(key).classList.add('is-invalid');
        //                 modified.push(
        //                     [key, '.classList', '.remove(', "'is-invalid')"],
        //                 )

        //                 if (i == 0) {
        //                     document.getElementById(key).focus();
        //                     i++;
        //                 }
        //             };
        //         }
        //     });
        // }
    </script>
@endsection
