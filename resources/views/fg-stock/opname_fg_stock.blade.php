@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <!-- Tempusdominus Datetimepicker -->
    <link rel="stylesheet" href="{{ asset('plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">

    <style>
        .badge-status-open {
            background-color: #e7f6ec;
            color: #1f8f4d;
            border: 1px solid #c7ecd3;
        }

        .badge-status-closed {
            background-color: #fdeaea;
            color: #c0392b;
            border: 1px solid #f5c6c6;
        }
    </style>
@endsection

@section('content')
    <!-- Modal New Opname -->
    <div class="modal fade" id="modalNewOpname" tabindex="-1" role="dialog" aria-labelledby="modalNewOpnameLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: none;">
                <div class="modal-header bg-sb text-light">
                    <h3 class="modal-title fs-5 mb-0"><i class="fas fa-clipboard-list"></i> New Opname</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label"><small><b>Periode</b></small></label>
                        <div class="input-group input-group-sm date" id="new_periode_picker" data-target-input="nearest">
                            <input type="text" class="form-control form-control-sm datetimepicker-input"
                                id="new_inp_periode" data-target="#new_periode_picker" readonly
                                title="Periode (bulan opname)">
                            <div class="input-group-append" data-target="#new_periode_picker" data-toggle="datetimepicker">
                                <div class="input-group-text bg-white"><i class="fas fa-calendar-alt"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label"><small><b>Tgl. Opname</b></small></label>
                        <input type="date" class="form-control form-control-sm" id="new_inp_tgl_opname">
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label"><small><b>Keterangan</b></small></label>
                        <textarea class="form-control form-control-sm" id="new_inp_ket" rows="2" placeholder="Keterangan (opsional)"
                            autocomplete="off"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary btn-sm" id="btn_lanjutkan_new_opname"
                        onclick="lanjutkanNewOpname()">
                        <i class="fas fa-arrow-right fa-sm"></i> Lanjutkan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> List Opname</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12 mb-3 d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                        data-bs-target="#modalNewOpname">
                        <i class="fas fa-plus fa-sm"></i>
                        New
                    </button>
                    <a href="{{ route('dashboard-analytics-opname-fg-stock') }}" target="_blank"
                        class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-chart-pie fa-sm"></i>
                        Dashboard Analytics
                    </a>
                </div>
            </div>
            <div class="row align-items-end">
                <div class="col-md-2 form-group">
                    <label class="form-label"><small><b>Tgl Awal</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal"
                        oninput="dataTableReload()" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-2 form-group">
                    <label class="form-label"><small><b>Tgl Akhir</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir"
                        oninput="dataTableReload()" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-5 form-group d-flex gap-2">
                    <a onclick="exportExcel()" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-file-excel fa-sm"></i>
                        Export Excel
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-hover display nowrap" style="width: 100%;">
                    <thead class="table-primary">
                        <tr style="text-align:center; vertical-align:middle">
                            <th>No. Opname</th>
                            <th>Tgl. Opname</th>
                            <th>Periode</th>
                            <th>Keterangan</th>
                            <th>Total Carton</th>
                            <th>Total Qty</th>
                            <th>Status</th>
                            <th style="width: 8%;">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/moment/moment.min.js') }}"></script>
    <script src="{{ asset('plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            dataTableReload();
        })

        // Modal New Opname
        $('#new_periode_picker').datetimepicker({
            format: 'YYYY-MM',
            viewMode: 'months',
        });

        $('#modalNewOpname').on('show.bs.modal', function() {
            let today = new Date().toISOString().slice(0, 10);
            $('#new_inp_periode').val(today.slice(0, 7));
            $('#new_inp_tgl_opname').val(today);
            $('#new_inp_ket').val('');
            updateNewTglOpnameRange();
        });

        $('#new_periode_picker').on('change.datetimepicker', function() {
            updateNewTglOpnameRange();
        });

        function updateNewTglOpnameRange() {
            let periode = $('#new_inp_periode').val();

            if (!periode) {
                return;
            }

            let periodeStart = periode + '-01';
            let periodeEnd = new Date(periode + '-01T00:00:00');
            periodeEnd.setMonth(periodeEnd.getMonth() + 1);
            periodeEnd.setDate(0);
            let periodeEndStr = periodeEnd.toISOString().slice(0, 10);

            let $inpTgl = $('#new_inp_tgl_opname');
            $inpTgl.attr('min', periodeStart).attr('max', periodeEndStr);

            if (!$inpTgl.val() || $inpTgl.val() < periodeStart || $inpTgl.val() > periodeEndStr) {
                let todayStr = new Date().toISOString().slice(0, 10);
                $inpTgl.val((todayStr >= periodeStart && todayStr <= periodeEndStr) ? todayStr :
                    periodeStart);
            }
        }

        function lanjutkanNewOpname() {
            let periode = $('#new_inp_periode').val();
            let tglOpname = $('#new_inp_tgl_opname').val();
            let ket = $('#new_inp_ket').val();

            if (!periode || !tglOpname) {
                Swal.fire({
                    title: 'Lengkapi Periode dan Tgl. Opname!',
                    icon: 'warning',
                    showConfirmButton: true,
                });
                return;
            }

            let $btn = $('#btn_lanjutkan_new_opname').prop('disabled', true);

            $.ajax({
                type: 'POST',
                url: '{{ route('store-opname-header-fg-stock') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    periode: periode,
                    tgl_opname: tglOpname,
                    ket: ket,
                },
                success: function(response) {
                    Swal.fire({
                        title: response.message,
                        icon: 'success',
                        showConfirmButton: true,
                    }).then(() => {
                        window.location.href = '{{ route('create-opname-fg-stock') }}?no_opname=' +
                            encodeURIComponent(response.no_opname);
                    });
                },
                error: function(xhr) {
                    let message = xhr.responseJSON && xhr.responseJSON.message ?
                        xhr.responseJSON.message : 'Gagal membuat data opname!';
                    Swal.fire({
                        title: message,
                        icon: 'warning',
                        showConfirmButton: true,
                    });
                },
                complete: function() {
                    $btn.prop('disabled', false);
                },
            });
        }

        function dataTableReload() {
            $("#datatable").DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                paging: true,
                searching: true,
                destroy: true,
                scrollX: true,
                ajax: {
                    url: '{{ route('opname-fg-stock') }}',
                    data: function(d) {
                        d.dateFrom = $('#tgl-awal').val();
                        d.dateTo = $('#tgl-akhir').val();
                    },
                },
                columns: [{
                        data: 'no_opname'
                    },
                    {
                        data: 'tgl_opname_fix'
                    },
                    {
                        data: 'periode_fix'
                    },
                    {
                        data: 'ket',
                        render: (data) => data || '-'
                    },
                    {
                        data: 'total_carton'
                    },
                    {
                        data: 'total_qty'
                    },
                    {
                        data: 'status',
                        render: (data) => {
                            if (!data) {
                                return '-';
                            }
                            let cls = data === 'CLOSED' ? 'badge-status-closed' :
                                'badge-status-open';
                            return `<span class="badge ${cls}">${data}</span>`;
                        }
                    },
                    {
                        data: null,
                        render: (data, type, row) => {
                            return `
                                <a class="btn btn-outline-primary btn-sm"
                                    href="{{ route('create-opname-fg-stock') }}?no_opname=${encodeURIComponent(row.no_opname)}"
                                    title="Buka">
                                    <i class="fas fa-folder-open fa-sm"></i> Buka
                                </a>`;
                        }
                    },
                ],
                columnDefs: [{
                    "className": "dt-center",
                    "targets": "_all"
                }, ]
            });
        }

        function exportExcel() {
            let from = $('#tgl-awal').val();
            let to = $('#tgl-akhir').val();

            Swal.fire({
                title: 'Please Wait...',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading();
                },
                allowOutsideClick: false,
            });

            $.ajax({
                type: 'get',
                url: '{{ route('export-excel-opname-fg-stock') }}',
                data: {
                    dateFrom: from,
                    dateTo: to
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    swal.close();
                    Swal.fire({
                        title: 'Data Sudah Di Export!',
                        icon: 'success',
                        showConfirmButton: true,
                        allowOutsideClick: false
                    });
                    let blob = new Blob([response]);
                    let link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = 'Laporan Opname FG Stock ' + from + ' sd ' + to + '.xlsx';
                    link.click();
                },
                error: function() {
                    swal.close();
                    Swal.fire({
                        title: 'Gagal export data!',
                        icon: 'error',
                        showConfirmButton: true,
                    });
                },
            });
        }

    </script>
@endsection
