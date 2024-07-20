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
    <div class="container-fluid mt-3 pt-3">
        <div class="card">
            <div class="card-header bg-sb text-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Report Output</h5>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#exportReportOutputmodal">
                            <i class="fa fa-upload"></i>
                        </button>
                        <input type="date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" id="report-output-day">
                    </div>
                </div>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-sm table-bordered" id="report-output-table">
                    <thead>
                        <tr>
                            <th>No. WS</th>
                            <th>Nama Buyer</th>
                            <th>Nama Style</th>
                            <th>CM Price</th>
                            <th>SMV</th>
                            <th>Tanggal Delivery</th>
                            <th>QTY Cutting</th>
                            <th>Total Output</th>
                            <th>Total Balance</th>
                            <th>Total Output Today</th>
                            <th>Total Cumulative Output Today</th>
                            <th>Total Balance Today</th>
                            <th>Earning Today</th>
                            <th>Cumulative Earning Today</th>
                            <th>Tanggal Produksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    {{-- Report Output --}}
    <div class="modal fade" id="exportReportOutputmodal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h5 class="modal-title">Export Report Output</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="select-period mb-3">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="export-period" id="monthly-period" value="monthly" checked="checked">
                            <label class="form-check-label" for="monthly-period">1 Bulan</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="export-period" id="daily-period" value="daily">
                            <label class="form-check-label" for="daily-period">1 Hari</label>
                        </div>
                    </div>
                    <div class="row my-1" id="monthly-export">
                        <div class="col-6">
                            <select class="form-select form-select-sm select2" name="export-month" id="export-month">
                                @foreach ($months as $month)
                                    <option value="{{ $month['angka'] }}">{{ $month['nama'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <select class="form-select form-select-sm select2" name="export-monthyear" id="export-monthyear">
                                @foreach ($years as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="my-1 d-none" id="daily-export">
                        <input class="form-control form-control-sm" type="date" name="export-date" id="export-date" value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="modal-footer ">
                    <button type="button" class="btn btn-sb" id="export-report-output">
                        <i class="fa fa-upload"></i>
                        Export
                    </button>
                </div>
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
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $('.select2').select2({
            theme: 'bootstrap4'
        })
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            $('#report-output-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{!! route('reportOutput') !!}',
                    data: function (d) {
                        d.date = $('#report-output-day').val();
                    }
                },
                columns: [
                    {data: 'no_ws',name: 'no_ws'},
                    {data: 'nama_buyer',name: 'nama_buyer'},
                    {data: 'no_style',name: 'no_style'},
                    {data: 'order_cfm_price',name: 'order_cfm_price', searchable: false},
                    {data: 'smv',name: 'smv', searchable: false},
                    {data: 'tanggal_delivery',name: 'tanggal_delivery'},
                    {data: 'order_qty_cutting',name: 'order_qty_cutting', searchable: false},
                    {data: 'before_output',name: 'before_output', searchable: false},
                    {data: 'before_balance',name: 'before_balance', searchable: false},
                    {data: 'output',name: 'output', searchable: false},
                    {data: 'cumulative_output',name: 'cumulative_output', searchable: false},
                    {data: 'cumulative_balance',name: 'cumulative_balance', searchable: false},
                    {data: 'earning',name: 'earning', searchable: false},
                    {data: 'cumulative_earning',name: 'cumulative_earning', searchable: false},
                    {data: 'tgl_produksi',name: 'tgl_produksi'},
                ],
                columnDefs: [
                    {
                        className: "text-nowrap",
                        targets: [0,1,2,5,14]
                    },
                    {
                        className: "text-nowrap text-right",
                        render: (data, type, row) => data.toLocaleString('id-ID'),
                        targets: [3,4,6,7,8,9,10,11,12,13],
                    },
                ]
            });

            $('#report-output-day').on('change', () => {
                $('#report-output-table').DataTable().ajax.reload();
            });

            $('#export-month').select2({
                theme: 'bootstrap-5',
                dropdownParent: $("#exportReportOutputmodal"),
            })

            $('#export-monthyear').select2({
                theme: 'bootstrap-5',
                dropdownParent: $("#exportReportOutputmodal"),
            })

            $("#monthly-period").prop("checked", true);

            $('#monthly-period').on('change', () => {
                console.log(document.querySelector('input[name="export-period"]:checked').value);
                if (document.querySelector('input[name="export-period"]:checked').value == 'monthly') {
                    document.getElementById('monthly-export').classList.remove('d-none');
                    document.getElementById('daily-export').classList.add('d-none');
                } else {
                    document.getElementById('monthly-export').classList.add('d-none');
                    document.getElementById('daily-export').classList.remove('d-none');
                }
            });

            $('#daily-period').on('change', () => {
                console.log(document.querySelector('input[name="export-period"]:checked').value);
                if (document.querySelector('input[name="export-period"]:checked').value == 'daily') {
                    document.getElementById('daily-export').classList.remove('d-none');
                    document.getElementById('monthly-export').classList.add('d-none');
                } else {
                    document.getElementById('daily-export').classList.add('d-none');
                    document.getElementById('monthly-export').classList.remove('d-none');
                }
            });

            $('#export-report-output').on('click', (elm) => {
                let period = document.querySelector('input[name="export-period"]:checked').value;
                let date = document.getElementById('export-date');

                if (period == 'monthly') {
                    date = document.getElementById('export-monthyear').value+'-'+document.getElementById('export-month').value;
                } else if (period == 'daily') {
                    date = document.getElementById('export-date').value;
                }

                Swal.fire({
                    title: 'Please Wait...',
                    html: 'Exporting Data...',
                    didOpen: () => {
                        Swal.showLoading()
                    },
                    allowOutsideClick: false,
                });

                $.ajax({
                    url: '{!! route('reportOutput.exportData') !!}',
                    type: 'post',
                    data: {
                        periode : period,
                        tanggal : date
                    },
                    xhrFields: { responseType : 'blob' },
                    success: function(res) {
                        swal.close();

                        iziToast.success({
                            title: 'Success',
                            message: 'Data berhasil di export.',
                            position: 'topCenter'
                        });

                        var blob = new Blob([res]);
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = date+" Output Report.xlsx";
                        link.click();
                    }, error: function (jqXHR) {
                        swal.close();

                        let res = jqXHR.responseJSON;
                        let message = '';
                        console.log(res.message);
                        for (let key in res.errors) {
                            message += res.errors[key]+' ';
                            document.getElementById(key).classList.add('is-invalid');
                        };
                        iziToast.error({
                            title: 'Error',
                            message: message,
                            position: 'topCenter'
                        });
                    }
                });
            });
        });
    </script>
@endsection
