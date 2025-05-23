@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endsection

@section('content')
    {{-- Show Detail Marker Modal --}}
    <div class="modal fade" id="showMarkerModal" tabindex="-1" role="dialog" aria-labelledby="showMarkerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h1 class="modal-title fs-5" id="showMarkerModalLabel"></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="detail">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Edit Marker Modal --}}
    <div class="modal fade" id="editMarkerModal" tabindex="-1" role="dialog" aria-labelledby="editMarkerModalLabel" aria-hidden="true">
        <form action="{{ route('update_marker') }}" method="post" onsubmit="submitForm(this, event)">
            @method('PUT')
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-sb text-light">
                        <h1 class="modal-title fs-5" id="editMarkerModalLabel"></h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class='row'>
                            <div class='col-sm-12'>
                                <div class='form-group'>
                                    <label class='form-label'><small class="fw-bold">Kode</small></label>
                                    <input type='text' class='form-control' id='txtkode_marker_edit' name='txtkode_marker_edit' value = '' readonly>
                                </div>
                            </div>
                            <div class='col-sm-12'>
                                <div class='form-group'>
                                    <label class='form-label'><small class="fw-bold">Gramasi</small></label>
                                    <input type='number' class='form-control' id='txt_gramasi' name='txt_gramasi' value = ''>
                                    <input type='hidden' class='form-control' id='id_c' name='id_c' value = ''>
                                </div>
                            </div>
                            <div class='col-sm-12' id="marker_pilot">
                                <div class='form-group'>
                                    <label class='form-label'><small class="fw-bold">Status Pilot</small></label>
                                    <div class="d-flex gap-3 ms-1">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="pilot_status" id="idle" value="idle">
                                            <label class="form-check-label" for="idle">
                                                <small class="fw-bold"><i class="fa fa-minus fa-sm"></i> Pilot Idle</small>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="pilot_status" id="active" value="active">
                                            <label class="form-check-label text-success" for="active">
                                                <small class="fw-bold"><i class="fa fa-check fa-sm"></i> Pilot
                                                    Approve</small>
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="pilot_status" id="not_active" value="not active">
                                            <label class="form-check-label text-danger" for="not_active">
                                                <small class="fw-bold"><i class="fa fa-times fa-sm"></i> Pilot
                                                    Disapprove</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12 d-none" id="advanced-edit-section">
                                <a href="" class="btn btn-primary btn-sm btn-block" id="advanced-edit-link"><i class="fas fa-edit"></i> Advanced Edit</a>
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

    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-marker fa-sm"></i> Marker</h5>
        </div>
        <div class="card-body">
            <a href="{{ route('create-marker') }}" class="btn btn-success btn-sm mb-3">
                <i class="fas fa-plus"></i>
                Baru
            </a>
            <div class="d-flex justify-content-between">
                <div class="d-flex justify-content-start align-items-end gap-3 mb-3">
                    <div class="mb-3">
                        <label class="form-label"><small>Tanggal Awal</small></label>
                        <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal"
                            value="{{ date('Y-m-d') }}" onchange="filterTable()">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><small>Tanggal Akhir</small></label>
                        <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir"
                            value="{{ date('Y-m-d') }}" onchange="filterTable()">
                    </div>
                    <div class="mb-3">
                        <button class="btn btn-primary btn-sm" onclick="filterTable()"><i class="fa fa-search"></i></button>
                    </div>
                </div>
                <div class="d-flex justify-content-end align-items-end gap-3 mb-3">
                    <button class="btn btn-info btn-sm mb-3 fw-bold" onclick="fixMarkerBalanceQty()">
                        <i class="fa-solid fa-screwdriver-wrench fa-sm"></i>
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table w-100">
                    <thead>
                        <tr>
                            <th class="align-bottom">Action</th>
                            <th>Tanggal</th>
                            <th>No. Marker</th>
                            <th>No. WS</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Panel</th>
                            <th>Urutan</th>
                            <th>Panjang</th>
                            <th>Lebar</th>
                            <th>Gramasi</th>
                            <th>Gelar QTYs</th>
                            <th>Total Form</th>
                            <th>PO</th>
                            <th>Ket.</th>
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
            if (i != 0) {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control form-control-sm" />');

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
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('marker') }}',
                data: function(d) {
                    d.tgl_awal = $('#tgl-awal').val();
                    d.tgl_akhir = $('#tgl-akhir').val();
                },
            },
            columns: [
                {
                    data: 'id'
                },
                {
                    data: 'tgl_cut_fix',
                    searchable: false
                },
                {
                    data: 'kode'
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
                    data: 'urutan_marker'
                },
                {
                    data: 'panjang_marker_fix',
                    searchable: false
                },
                {
                    data: 'lebar_marker',
                },
                {
                    data: 'gramasi'
                },
                {
                    data: undefined
                },
                {
                    data: 'total_form',
                    searchable: false
                },
                {
                    data: 'po_marker'
                },
                {
                    data: 'notes',
                },
            ],
            columnDefs: [
                {
                    targets: [0],
                    render: (data, type, row, meta) => {
                        let exportBtn = `
                            <button type="button" class="btn btn-sm btn-success" onclick="printMarker('` + row.kode + `');">
                                <i class="fa fa-print"></i>
                            </button>
                        `;

                        if (row.cancel != 'Y' && row.total_form > 0 /* && row.tipe_marker != "pilot marker" */) {
                            return `
                                <div class='d-flex gap-1 justify-content-start mb-1'>
                                    <a class='btn btn-primary btn-sm' data-bs-toggle="modal" data-bs-target="#showMarkerModal" onclick='getdetail(` + row.id + `);'>
                                        <i class='fa fa-search'></i>
                                    </a>
                                    <button class='btn btn-info btn-sm' data-bs-toggle="modal" data-bs-target="#editMarkerModal" onclick='edit(` + row.id + `);'>
                                        <i class='fa fa-edit'></i>
                                    </button>
                                    ` + exportBtn + `
                                    <button class='btn btn-danger btn-sm' onclick='cancel(` + row.id + `);' disabled>
                                        <i class='fa fa-ban'></i>
                                    </button>
                                </div>
                            `;
                        } else if ((row.cancel != 'Y' && row.total_form < 1) /*|| (row.cancel != 'Y' && row.gelar_qty_balance > 0  && row.tipe_marker == "pilot marker" )*/) {
                            return `
                                <div class='d-flex gap-1 justify-content-start mb-1'>
                                    <a class='btn btn-primary btn-sm' data-bs-toggle="modal" data-bs-target="#showMarkerModal" onclick='getdetail(` + row.id + `);'>
                                        <i class='fa fa-search'></i>
                                    </a>
                                    <button class='btn btn-info btn-sm' data-bs-toggle="modal" data-bs-target="#editMarkerModal" onclick='edit(` + row.id + `);'>
                                        <i class='fa fa-edit'></i>
                                    </button>
                                    ` + exportBtn + `
                                    <button class='btn btn-danger btn-sm' onclick='cancel(` + row.id + `);'>
                                        <i class='fa fa-ban'></i>
                                    </button>
                                </div>
                            `;
                        } else if (row.cancel == 'Y') {
                            return `
                                <div class='d-flex gap-1 justify-content-start'>
                                    <a class='btn btn-primary btn-sm' data-bs-toggle="modal" data-bs-target="#showMarkerModal" onclick='getdetail(` + row.id + `);'>
                                        <i class='fa fa-search'></i>
                                    </a>
                                    <button class='btn btn-info btn-sm' data-bs-toggle="modal" data-bs-target="#editMarkerModal" onclick='edit(` + row.id + `);' disabled>
                                        <i class='fa fa-edit'></i>
                                    </button>
                                    ` + exportBtn + `
                                    <button class='btn btn-danger btn-sm' onclick='cancel(` + row.id + `);' disabled>
                                        <i class='fa fa-ban'></i>
                                    </button>
                                </div>
                            `;
                        } else {
                            return `
                                <div class='d-flex gap-1 justify-content-start'>
                                    <a class='btn btn-primary btn-sm' data-bs-toggle="modal" data-bs-target="#showMarkerModal" onclick='getdetail(` + row.id + `);'>
                                        <i class='fa fa-search'></i>
                                    </a>
                                    <button class='btn btn-info btn-sm' data-bs-toggle="modal" data-bs-target="#editMarkerModal" onclick='edit(` + row.id + `);'>
                                        <i class='fa fa-edit'></i>
                                    </button>
                                    ` + exportBtn + `
                                    <button class='btn btn-danger btn-sm' onclick='cancel(` + row.id + `);' disabled>
                                        <i class='fa fa-ban'></i>
                                    </button>
                                </div>
                            `;
                        }
                    }
                },
                {
                    targets: [11],
                    render: (data, type, row, meta) => {
                        return `
                            <div class="progress border border-sb position-relative" style="height: 21px; width: 100px;">
                                <p class="position-absolute" style="top: 50%;left: 50%;transform: translate(-50%, -50%);">` + row.total_lembar + `/` + row.gelar_qty + `</p>
                                <div class="progress-bar" style="background-color: #75baeb;width: ` + ((row.total_lembar / row.gelar_qty) * 100) + `%" role="progressbar"></div>
                            </div>
                        `;
                    }
                },
                {
                    targets: '_all',
                    className: 'text-nowrap',
                    render: (data, type, row, meta) => {
                        var color = '#2b2f3a';
                        if (row.total_form != '0' && row.cancel == 'N') {
                            color = '#087521';
                        } else if (row.total_form == '0' && row.cancel == 'N') {
                            color = '#2243d6';
                        } else if (row.cancel == 'Y') {
                            color = '#d33141';
                        }
                        return '<span style="font-weight: 600; color:' + color + '">' + data + '</span>';
                    }
                },
            ],
            rowCallback: function( row, data, index ) {
                if (data['tipe_marker'] == 'special marker') {
                    $('td', row).css('background-color', '#e7dcf7');
                    $('td', row).css('border', '0.15px solid #d0d0d0');
                } else if (data['tipe_marker'] == 'pilot marker' || data['tipe_marker'] == 'bulk marker') {
                    $('td', row).css('background-color', '#c5e0fa');
                    $('td', row).css('border', '0.15px solid #d0d0d0');
                }
            }
        });

        function filterTable() {
            datatable.ajax.reload();
        }

        function getdetail(id_c) {
            $("#showMarkerModalLabel").html('<i class="fa-solid fa-magnifying-glass fa-sm"></i> Marker Detail');
            let html = $.ajax({
                type: "POST",
                url: '{{ route('show-marker') }}',
                data: {
                    id_c: id_c
                },
                async: false
            }).responseText;
            $("#detail").html(html);

            $("#detail-marker-ratio").DataTable({
                ordering: false
            });

            $("#detail-marker-form").DataTable({
                ordering: false
            });
        };

        function edit(id_c) {
            document.getElementById("loading").classList.remove('d-none');

            $("#editMarkerModalLabel").html('<i class="fa fa-edit fa-sm"></i> Marker Edit');
            $.ajax({
                url: '{{ route('show_gramasi') }}',
                method: 'POST',
                data: {
                    id_c: id_c
                },
                dataType: 'json',
                success: function(response) {
                    console.log(response);

                    document.getElementById('txtkode_marker_edit').value = response.kode;
                    document.getElementById('txt_gramasi').value = response.gramasi;
                    document.getElementById('id_c').value = response.id;

                    if (response.tipe_marker == "pilot marker") {
                        document.getElementById('marker_pilot').classList.remove('d-none');

                        if (response.status_marker) {
                            document.getElementById(response.status_marker).checked = true;
                        } else {
                            document.getElementById("idle").checked = true;
                        }
                    } else {
                        document.getElementById('marker_pilot').classList.add('d-none');
                    }

                    if (response.jumlah_form > 0) {
                        document.getElementById('txt_gramasi').setAttribute('readonly', true);
                    } else {
                        document.getElementById('txt_gramasi').removeAttribute('readonly');
                    }

                    document.getElementById('advanced-edit-link').setAttribute('href','{{ route('edit-marker') }}/' + response.id);
                    document.getElementById('advanced-edit-section').classList.remove('d-none');

                    document.getElementById("loading").classList.add('d-none');
                },
                error: function(request, status, error) {
                    alert(request.responseText);

                    document.getElementById("loading").classList.add('d-none');
                },
            });
        };

        function cancel(id_c) {
            Swal.fire({
                icon: 'error',
                title: 'Hapus Data',
                showConfirmButton: true,
                confirmButtonText: "Hapus",
                confirmButtonColor: "red",
                showCancelButton: true
            }).then((result) => {
                if (result.isConfirmed) {
                    let html = $.ajax({
                        type: "POST",
                        url: '{{ route('update_status') }}',
                        data: {
                            id_c: id_c
                        },
                        async: false
                    }).responseText;

                    swal.fire({
                        position: 'mid-end',
                        icon: 'info',
                        title: 'Data Sudah Di Ubah',
                        showConfirmButton: false,
                        timer: 5000
                    });

                    datatable.ajax.reload();
                }
            });
        };

        function printMarker(kodeMarker) {
            let fileName = kodeMarker;

            // Show Loading
            Swal.fire({
                title: 'Please Wait...',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            $.ajax({
                url: '{{ route('print-marker') }}/' + kodeMarker.replace(/\//g, '_'),
                type: 'post',
                processData: false,
                contentType: false,
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(res) {
                    if (res) {
                        var blob = new Blob([res], {
                            type: 'application/pdf'
                        });

                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = fileName + ".pdf";
                        link.click();

                        swal.close();
                    }
                }
            });
        }

        function fixMarkerBalanceQty() {
            Swal.fire({
                title: 'Please Wait...',
                html: 'Fixing Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            $.ajax({
                url: '{{ route('fix-marker-balance-qty') }}',
                method: 'POST',
                dataType: 'json',
                success: async function(res) {
                    console.log(res);

                    await swal.close();

                    Swal.fire({
                        icon: 'success',
                        title: res.message,
                        showCancelButton: false,
                        showConfirmButton: true,
                        confirmButtonText: 'Oke',
                    }).then(() => {
                        if (isNotNull(res.redirect)) {
                            if (res.redirect != 'reload') {
                                location.href = res.redirect;
                            } else {
                                location.reload();
                            }
                        } else {
                            location.reload();
                        }
                    });
                },
                error: function(jqXHR) {
                    console.log(jqXHR);
                },
            });
        }
    </script>
@endsection
