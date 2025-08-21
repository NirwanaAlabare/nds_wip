@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        /* table.dataTable tbody tr.selected {
            color: white !important;
            background-color: #da00c8 !important;
        } */
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-bold">
                    <i class="fa fa-cog fa-sm"></i> Atur Cutting Plan
                </h5>
                <a href="{{ route('cut-plan') }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-reply fa-sm"></i> Kembali ke Data Cutting Plan
                </a>
            </div>
        </div>
        <div class="card-body">
            <form action="#" method="post">
                <div class="mb-3">
                    <label>Tanggal Plan</label>
                    <input type="date" class="form-control" name="tgl_plan" id="tgl_plan" value="{{ date('Y-m-d') }}">
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
                                <i class="fa fa-plus"></i> Tambah Form Cut :
                            </h5>
                        </div>
                        {{-- <div class="col-6">
                            <input type="date" class="form-control form-control-sm w-auto float-end" id="tgl_form" name="tgl_form" value="{{ date('Y-m-d') }}">
                        </div> --}}
                    </div>
                </div>
                <div class="card-body">
                    <div class="row justify-content-between mb-3">
                        <div class="col-6">
                            <p>Selected Form : <span class="fw-bold" id="selected-row-count-1">0</span></p>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-success btn-sm float-end" onclick="addToCutPlan(this)">
                                <i class="fa fa-plus fa-sm"></i> Tambahkan ke Cut Plan
                            </button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="datatable-select" class="table table-bordered table w-100">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tanggal</th>
                                    <th>No. Form</th>
                                    <th>No. Meja</th>
                                    <th>Style</th>
                                    <th>Color</th>
                                    <th>Panel</th>
                                    <th>Size Ratio</th>
                                    <th>Qty Ply</th>
                                    <th>No. Marker</th>
                                    <th>No. WS</th>
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
                    <h5 class="card-title fw-bold" style="padding-bottom: 2px">
                        <i class="fa fa-list"></i> Form Cut Terdaftar :
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-6">
                            <p>Selected Form : <span class="fw-bold" id="selected-row-count-2">0</span></p>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-danger btn-sm float-end" onclick="removeCutPlan(this)"><i class="fa fa-minus fa-sm"></i> Singkirkan dari Cut Plan</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="datatable-selected" class="table table-bordered table w-100">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tanggal</th>
                                    <th>No. Form</th>
                                    <th>No. Meja</th>
                                    <th>Style</th>
                                    <th>Color</th>
                                    <th>Panel</th>
                                    <th>Size Ratio</th>
                                    <th>Qty Ply</th>
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
    {{-- <button class="btn btn-sb btn-block" type="submit">SIMPAN</button> --}}
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

        //Focus Select2
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        //Reset Form
        if (document.getElementById('store-cut-plan')) {
            document.getElementById('store-cut-plan').reset();
        }

        // Tgl Plan
        document.getElementById("tgl_plan").addEventListener("change", function() {
            let todayDate = new Date();
            let selectedDate = new Date(this.value);

            // if (selectedDate < todayDate) {
            //    $("#tgl_plan").val(formatDate(todayDate));
            // }

            datatableSelect.ajax.reload(() => {
                $('#datatable-select').DataTable().ajax.reload(() => {
                    if (formArr) {
                        document.getElementById('selected-row-count-1').innerText = formArr.length;
                    }
                });
            });

            datatableSelected.ajax.reload(() => {
                $('#datatable-selected').DataTable().ajax.reload(() => {
                    if (formSelectedArr) {
                        document.getElementById('selected-row-count-2').innerText = formSelectedArr.length;
                    }
                });
            });
        });

        // SELECT FORM

        // Table Select Form
        var formFilter = ["id", "tanggal_filter", "no_form_filter", "no_meja_filter", "style_filter", "color_filter", "panel_filter", "size_ratio_filter", "qty_ply_filter", "no_marker_filter", "no_ws_filter"];
        $('#datatable-select thead tr').clone(true).appendTo('#datatable-select thead');
        $('#datatable-select thead tr:eq(1) th').each(function(i) {
            if (i != 0 && i != 7) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm" id="'+formFilter[i]+'" />');

                $('input', this).on('keyup change', function() {
                    if (datatableSelect.column(i).search() !== this.value) {
                        datatableSelect
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            } else {
                if (i == 0) {
                    $(this).html(`
                        <div class="form-check" style="scale: 1.5;translate: 50%;">
                            <input class="form-check-input" type="checkbox" value="" id="checkAllForm"">
                        </div>
                    `);
                } else {
                    $(this).empty();
                }
            }
        });

        function dataTableFormReload() {
            $('#datatable-select').DataTable().ajax.reload(() => {
                if (formArr) {
                    document.getElementById('selected-row-count-1').innerText = formArr.length;
                }

                document.getElementById("loading").classList.add("d-none");
            });
        }

        let datatableSelect = $("#datatable-select").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('create-cut-plan') }}',
                data: function(d) {
                    d.tgl_plan = $('#tgl_plan').val();
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
                    data: 'qty_ply'
                },
                {
                    data: 'id_marker'
                },
                {
                    data: 'ws'
                },
            ],
            columnDefs: [
                {
                    targets: [0],
                    render: (data, type, row, meta) => {
                        return `
                            <div class="form-check" style="scale: 1.5;translate: 50%;">
                                <input class="form-check-input check-form" type="checkbox" onchange="checkForm(this)" id="form_`+meta.row+`">
                            </div>
                        `;
                    }
                },
                {
                    targets: [9],
                    render: (data, type, row, meta) => {
                        return data ? `<a class='fw-bold' href='{{ route('edit-marker') }}/ `+row.marker_id+`' target='_blank'><u>`+data+`</u></a>` : "-";
                    }
                },
                {
                    targets: "_all",
                    className: "text-nowrap",
                    render: (data, type, row, meta) => {
                        return data ? data : '-'
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

                if (formArr.filter(item => item.id == data['id']).length > 0) {
                    currentPageCheck++;

                    $(row).find('input[type="checkbox"]').prop('checked', true);
                }
            },
            drawCallback: function (settings) {
                if (currentPageCheck == 0) {
                    $('#checkAllForm').prop("checked", false);
                } else {
                    $('#checkAllForm').prop("checked", true);
                }

                currentPageCheck = 0;
            },
        });

        // Check Select Form
        var currentPageCheck = 0;
        var formArr = [];

        function checkForm(element) {
            let data = $('#datatable-select').DataTable().row(element.closest('tr')).data();

            if (data) {
                if (element.checked) {
                    formArr.push(data);
                } else {
                    formArr = formArr.filter(item => item.id != data.id);
                }

                document.getElementById('selected-row-count-1').innerText = formArr.length;
            }
        }

        $("#checkAllForm").on("change", function () {
            document.getElementById("loading").classList.remove("d-none");

            if (this.checked) {
                $.ajax({
                    url: '{{ route('check-all-form-cut-plan') }}',
                    method: 'post',
                    data: {
                        tgl_plan : $('#tgl_plan').val(),
                        tanggal_filter : $('#tanggal_filter').val(),
                        no_form_filter : $('#no_form_filter').val(),
                        no_meja_filter : $('#no_meja_filter').val(),
                        style_filter : $('#style_filter').val(),
                        color_filter : $('#color_filter').val(),
                        panel_filter : $('#panel_filter').val(),
                        qty_ply_filter : $('#qty_ply_filter').val(),
                        no_marker_filter : $('#no_marker_filter').val(),
                        no_ws_filter : $('#no_ws_filter').val()
                    },
                    success: function (res) {
                        if (res) {
                            formArr = res;

                            dataTableFormReload();
                        } else {
                            document.getElementById("loading").classList.add("d-none");
                        }
                    },
                    error: function (jqXHR) {
                        console.log(jqXHR);

                        document.getElementById("loading").classList.add("d-none");
                    }
                })
            } else {
                formArr = [];

                dataTableFormReload();

                document.getElementById("loading").classList.add("d-none");
            }
        })

        // Submit Add to Plan
        function addToCutPlan(element) {
            let tglPlan = $("#tgl_plan").val();
            let selectedForm = formArr;
            let formCutPlan = [];

            for (let key in selectedForm) {
                if (!isNaN(key)) {
                    formCutPlan.push({
                        form_cut_id: selectedForm[key]['id'],
                        no_form: selectedForm[key]['no_form']
                    });
                }
            }

            if (tglPlan && formCutPlan.length > 0) {
                element.setAttribute('disabled', true);

                $.ajax({
                    type: "POST",
                    url: '{!! route('store-cut-plan') !!}',
                    data: {
                        tgl_plan: tglPlan,
                        formCutPlan: formCutPlan
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

                            if ((res.additional['success'].length + res.additional['fail'].length + res
                                    .additional['exist'].length) > 1) {
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

                        formArr = [];
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



        // SELECTED FORM

        // Table Selected Form
        var formSelectedFilter = ["id_selected", "tanggal_selected_filter", "no_form_selected_filter", "no_meja_selected_filter", "style_selected_filter", "color_selected_filter", "panel_selected_filter", "size_ratio_selected_filter", "qty_ply_selected_filter", "no_marker_selected_filter", "no_ws_selected_filter"];
        $('#datatable-selected thead tr').clone(true).appendTo('#datatable-selected thead');
        $('#datatable-selected thead tr:eq(1) th').each(function(i) {
            if (i != 0 && i != 7 && i != 11) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm" id="'+formSelectedFilter[i]+'"/>');

                $('input', this).on('keyup change', function() {
                    if (datatableSelected.column(i).search() !== this.value) {
                        datatableSelected
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            } else {
                if (i == 0) {
                    $(this).html(`
                        <div class="form-check" style="scale: 1.5;translate: 50%;">
                            <input class="form-check-input" type="checkbox" value="" id="checkAllFormSelected">
                        </div>
                    `);
                } else {
                    $(this).empty();
                }
            }
        });

        function dataTableFormSelectedReload() {
            $('#datatable-selected').DataTable().ajax.reload(() => {
                if (formSelectedArr) {
                    document.getElementById('selected-row-count-2').innerText = formSelectedArr.length;
                }

                document.getElementById("loading").classList.add("d-none");
            });
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
                    data: 'id',
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
                    data: 'qty_ply'
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
                    targets: [0],
                    render: (data, type, row, meta) => {
                        return `
                            <div class="form-check" style="scale: 1.5;translate: 50%;">
                                <input class="form-check-input check-form" type="checkbox" onchange="checkFormSelected(this)" id="form_selected_`+meta.row+`">
                            </div>
                        `;
                    }
                },
                {
                    targets: [11],
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

                if (formSelectedArr.filter(item => item.id == data['id']).length > 0) {
                    currentPageCheckSelected++;

                    $(row).find('input[type="checkbox"]').prop('checked', true);
                }
            },
            drawCallback: function (settings) {
                if (currentPageCheckSelected == 0) {
                    $('#checkAllFormSelected').prop("checked", false);
                } else {
                    $('#checkAllFormSelected').prop("checked", true);
                }

                currentPageCheckSelected = 0;
            }
        });

        // Check Form Selected
        var currentPageCheckSelected = 0;
        var formSelectedArr = [];

        function checkFormSelected(element) {
            let data = $('#datatable-selected').DataTable().row(element.closest('tr')).data();

            if (data) {
                if (element.checked) {
                    formSelectedArr.push(data);
                } else {
                    formSelectedArr = formSelectedArr.filter(item => item.id != data.id);
                }

                document.getElementById('selected-row-count-1').innerText = formSelectedArr.length;
            }
        }

        $("#checkAllFormSelected").on("change", function () {
            document.getElementById("loading").classList.remove("d-none");

            if (this.checked) {
                $.ajax({
                    url: '{{ route('check-all-form-selected-cut-plan') }}',
                    method: 'post',
                    data: {
                        tgl_plan : $('#tgl_plan').val(),
                        tanggal_filter : $('#tanggal_selected_filter').val(),
                        no_form_filter : $('#no_form_selected_filter').val(),
                        no_meja_filter : $('#no_meja_selected_filter').val(),
                        style_filter : $('#style_selected_filter').val(),
                        color_filter : $('#color_selected_filter').val(),
                        panel_filter : $('#panel_selected_filter').val(),
                        qty_ply_filter : $('#qty_ply_selected_filter').val(),
                        no_marker_filter : $('#no_marker_selected_filter').val(),
                        no_ws_filter : $('#no_ws_selected_filter').val()
                    },
                    success: function (res) {
                        if (res) {
                            formSelectedArr = res;

                            dataTableFormSelectedReload();
                        } else {
                            document.getElementById("loading").classList.add("d-none");
                        }
                    },
                    error: function (jqXHR) {
                        console.log(jqXHR);

                        document.getElementById("loading").classList.add("d-none");
                    }
                })
            } else {
                formSelectedArr = [];

                dataTableFormSelectedReload();

                document.getElementById("loading").classList.add("d-none");
            }
        })

        // Submit Remove from Cut Plan
        function removeCutPlan(element) {
            element.setAttribute('disabled', true);

            let tglPlan = $("#tgl_plan").val();
            let selectedForm = formSelectedArr;
            let formCutPlan = [];
            for (let key in selectedForm) {
                if (!isNaN(key)) {
                    formCutPlan.push({
                        id: selectedForm[key]['id'],
                        no_form: selectedForm[key]['no_form'],
                        status: selectedForm[key]['status']
                    });
                }
            }

            if (tglPlan && formCutPlan.length > 0 && !(formCutPlan.some(item => item.status.includes('PENGERJAAN FORM CUTTING') || item.status.includes('PENGERJAAN FORM CUTTING DETAIL') || item.status.includes('PENGERJAAN FORM CUTTING SPREAD') || item.status.includes('SELESAI PENGERJAAN')))) {
                Swal.fire({
                    icon: 'info',
                    title: 'Singkirkan Form yang dipilih?',
                    text: 'Yakin akan menyingkirkan form?',
                    showCancelButton: true,
                    showConfirmButton: true,
                    confirmButtonText: 'Singkirkan',
                    confirmButtonColor: "#d33141",
                }).then(async (result) => {
                    element.removeAttribute('disabled');

                    if (result.isConfirmed) {
                        element.setAttribute('disabled', true);

                        $.ajax({
                            type: "DELETE",
                            url: '{!! route('destroy-cut-plan') !!}',
                            data: {
                                tgl_plan: tglPlan,
                                formCutPlan: formCutPlan
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
                                        document.getElementById('selected-row-count-2')
                                            .innerText = $('#' + res.table).DataTable()
                                            .rows('.selected').data().length;
                                    });

                                    $('#datatable-select').DataTable().ajax.reload(() => {
                                        document.getElementById('selected-row-count-1')
                                            .innerText = $('#datatable-select').DataTable()
                                            .rows('.selected').data().length;
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
                                            title: 'Plan berhasil disingkirkan',
                                            html: message,
                                            showCancelButton: false,
                                            showConfirmButton: true,
                                            confirmButtonText: 'Oke',
                                        });
                                    }
                                }

                                formSelectedArr = [];
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
                    }
                });
            } else {
                element.removeAttribute('disabled');

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
    </script>
@endsection
