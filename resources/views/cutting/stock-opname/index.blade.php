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
    <div class="container-fluid mt-3">
        <div class="row">
            <div class="col-12">
                <div class="card card-sb">
                    <div class="card-header">
                        <h3 class="card-title">Stock Opname Fabric</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-end mb-3">
                            <div class="d-flex gap-3 align-items-end">
                                <div>
                                    <label for="dateFrom" class="form-label">Tanggal Awal</label>
                                    <input type="date" class="form-control" id="dateFrom" name="dateFrom" value="{{ date('Y-m-d', strtotime(date("Y-m-d")." -7 days")) }}">
                                </div>
                                <span class="mb-2 d-flex align-items-center">s/d</span>
                                <div>
                                    <label for="dateTo" class="form-label">Tanggal Akhir</label>
                                    <input type="date" class="form-control" id="dateTo" name="dateTo" value="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                            <div>
                                <button class="btn btn-success" id="exportExcel" data-title="Stock Opname Fabric" data-url="{{ route('export-stock-opname-cutting') }}"><i class="fa fa-file-excel"></i> Export Excel</button>
                            </div>
                        </div>
                        <table id="datatable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Tanggal Opname</th>
                                    <th>Nomor Opname</th>
                                    <th>Status</th>
                                    <th>No BPPB</th>
                                    <th>Id Roll</th>
                                    <th>Id Item</th>
                                    <th>No Lot</th>
                                    <th>No Roll</th>
                                    <th>No Roll Buyer</th>
                                    <th>Item Desc</th>
                                    <th>No WS Aktual</th>
                                    <th>Style Aktual</th>
                                    <th>Color</th>
                                    <th>Qty Sistem</th>
                                    <th>Unit Sistem</th>
                                    <th>Qty Aktual</th>
                                    <th>Unit Aktual</th>
                                    <th>User NIK</th>
                                    <th>User Name </th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
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

        let datatable = $("#datatable").DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            scrollX: "500px",
            scrollY: "500px",
            pageLength: 50,
            ajax: {
                url: '{{ route('stock-opname-cutting') }}',
                data: function(d) {
                    d.dateFrom = $('#dateFrom').val();
                    d.dateTo = $('#dateTo').val();
                },
            },
            columns: [
                { data: 'tanggal_opname' },
                { data: 'nomor_opname' },
                { data: 'status' },
                { data: 'no_bppb' },
                { data: 'id_roll' },
                { data: 'id_item' },
                { data: 'no_lot' },
                { data: 'no_roll' },
                { data: 'no_roll_buyer' },
                { data: 'item_desc' },
                { data: 'no_ws_aktual' },
                { data: 'style_aktual' },
                { data: 'color' },
                { data: 'qty' },
                { data: 'unit'},
                { data: 'qty_aktual' },
                { data: 'unit_aktual'},
                { data: 'created_by' },
                { data: 'created_by_username' }
            ],
            columnDefs: [
                {
                    targets: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 14, 16, 17, 18],
                    className: "text-nowrap",
                    render: function(data, type, row, meta) {
                        return data ? data : '-';
                    }
                },
                {
                    targets: [13, 15],
                    className: "text-nowrap",
                    render: function(data, type, row, meta) {
                        return data ? Number(data).round(4) : '-';
                    }
                }
            ]
        });

        document.getElementById("exportExcel").addEventListener("click", function() {
            let dateFrom = $('#dateFrom').val();
            let dateTo = $('#dateTo').val();

            exportExcelGlobal(this, {
                dateFrom: dateFrom,
                dateTo: dateTo
            });
        });
    </script>
@endsection
