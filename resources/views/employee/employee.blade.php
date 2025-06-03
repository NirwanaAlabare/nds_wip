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

<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" style="max-width: 75%;">
        <div class="modal-content">
            <div class="modal-header bg-sb text-light">
                <h1 class="modal-title fs-5" id="exampleModalLabel"></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detail">
                <div class="col-md-12 table-responsive">
                    <table id="datatable-modal" class="table table-bordered table-striped w-100">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tgl</th>
                                <th>Line</th>
                                <th>Jam Absen</th>
                                <th>NIK</th>
                                <th>Nama</th>
                                <th>Line Asal</th>
                                <th>Update Terakhir</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>


    <a href="{{ route('create-mut-karyawan') }}" class="btn btn-primary btn-sm mb-3">
        <i class="fas fa-qrcode fa-spin fa-lg"></i>
        Scan Perpindahan
    </a>
    <div class="card card-success collapsed-card">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0">Export Data Karyawan</h5>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">

            <form action="{{ route('export_excel_mut_karyawan') }}" method="get">
                <div class="d-flex align-items-end gap-3 mb-3">
                    <div class="mb-3">
                        <label class="form-label"><small>Tgl Awal</small></label>
                        <input type="date" class="form-control form-control-sm" id="from" name="from"
                            value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><small>Tgl Akhir</small></label>
                        <input type="date" class="form-control form-control-sm" id="to" name="to"
                            value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        {{-- <input type='button' class='btn btn-primary btn-sm' onclick="dataTableReload();" value="Tampilkan"> --}}
                        <button type='submit' name='submit' class='btn btn-success btn-sm'>
                            <i class="fas fa-file-excel"></i> Export</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="card card-info">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0">List Data Karyawan Hari Ini</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered 100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Line</th>
                            <th>Total Karyawan</th>
                            <th>Total Karyawan Absen</th>
                            <th>Selisih</th>
                            <th>Act</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
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
    <script>
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        $('.select2').select2()

        $('.select2bs4').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#editMejaModal")
        })
    </script>

    <script>
        window.addEventListener("focus", () => {
            dataTableReload();
        });

        let datatable = $("#datatable").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            paging:false,
            ajax: {
                url: '{{ route('mut-karyawan') }}',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            "fnCreatedRow": function(row, data, index) {
                $('td', row).eq(0).html(index + 1);
            },
            columns: [{
                    data: 'line'

                }, {
                    data: 'line'
                },
                {
                    data: 'tot_orang'
                },
                {
                    data: 'tot_absen'
                },
                {
                    data: 'selisih'
                },
            ],
            columnDefs: [{
                targets: [5],
                render: (data, type, row, meta) => {
                    return `
                        <div class='d-flex gap-1 justify-content-center'>
                            <a class='btn btn-primary btn-sm' data-bs-toggle="modal" data-bs-target="#exampleModal" onclick="getdetail('`+row.line+`');">
                                        <i class='fa fa-search'></i>
                            </a>
                        </div>
                    `
                }
            } ]
        });

        function getdetail(id_c) {
            $("#exampleModalLabel").html('List Data Karyawan');

         datatable = $("#datatable-modal").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            info: false,
            paging: false,
            scrollX: true,
            destroy: true,
            ajax: {
                url: '{{ route('getdatalinekaryawan') }}',
                method: 'GET',
                data: function(d) {
                    d.nm_line = id_c
                },
            },
            "fnCreatedRow": function(row, data, index) {
                $('td', row).eq(0).html(index + 1);
            },
            columns: [{
                    data: 'tgl_pindah'
                }, {
                    data: 'tgl_pindah_fix'
                },
                {
                    data: 'line'
                },
                {
                    data: 'absen_masuk_kerja'
                },
                {
                    data: 'nik'
                },
                {
                    data: 'nm_karyawan'
                },
                {
                    data: 'line_asal'
                },
                {
                    data: 'tgl_update_fix'
                }
            ],
            columnDefs: [
                {
                    targets: '_all',
                    render: (data, type, row, meta) => {
                        var color = 'black';
                        if (row.absen_masuk_kerja == null ) {
                            color = 'red';
                        } else{
                            color = 'green';
                        }
                        return '<span style="color:' + color + '">' + data + '</span>';
                    }
                }
            ],

        }
        );
        };


        let datatableRatio = $("#datatable-ratio").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('getdata_ratio') }}',
                data: function(d) {
                    d.cbomarker = $('#edit_marker_id').val();
                },
            },
            columns: [{
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
    </script>
@endsection
