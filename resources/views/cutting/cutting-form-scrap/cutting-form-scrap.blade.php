@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fa-solid fa-scissors"></i> Form Cut Scrap</h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-end gap-3 mb-3">
                <div class="d-flex align-items-end gap-3">
                    <div>
                        <label class="form-label mb-0"><small>Tanggal Awal</small></label>
                        <input type="date" class="form-control form-control-sm" id="tgl-awal" onchange="dataTableReload()">
                    </div>
                    <div>
                        <label class="form-label mb-0"><small>Tanggal Akhir</small></label>
                        <input type="date" class="form-control form-control-sm" id="tgl-akhir" value="{{ date('Y-m-d') }}" onchange="dataTableReload()">
                    </div>
                    <button class="btn btn-primary btn-sm" onclick="dataTableReload()"><i class="fa fa-search"></i></button>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('create-cutting-scrap') }}" class="btn btn-sb btn-sm" target="_blank" data-bs-toggle="tooltip" data-bs-title="Buat Form Scrap Baru"><i class="fa fa-plus"></i></a>
                    <button data-url="{{ route('export-excel-cutting-scrap') }}" data-title="Cutting Scrap Excel" class="btn btn-success btn-sm" target="_blank" data-bs-toggle="tooltip" data-bs-title="Export Data" onclick="exportExcel(this)"><i class="fa fa-file-excel"></i></button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-hover w-100">
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>No. Form</th>
                            <th>Tanggal</th>
                            <th>No. WS</th>
                            <th>Style</th>
                            <th>Color</th>
                            <th>Panel</th>
                            <th>Parts</th>
                            <th>Total Roll</th>
                            <th>Total Roll Qty</th>
                            <th>Size Qty</th>
                            <th>Status</th>
                            <th>Operator</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
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
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            let lastWeek = new Date(new Date().setDate(new Date().getDate() - 7));

            $("#tgl-awal").val(lastWeek.toISOString().slice(0, 10)).trigger("change");
        });

        let datatable = $("#datatable").DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            scrollX: true,
            pageLength: 25,
            ajax: {
                url: '{{ route('cutting-scrap') }}',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [
                { data: 'id' },
                { data: 'no_form' },
                { data: 'tanggal' },
                { data: 'act_costing_ws' },
                { data: 'style' },
                { data: 'color' },
                { data: 'panel' },
                { data: 'parts' },
                { data: 'total_roll' },
                { data: 'total_qty_roll' },
                { data: 'size_qty' },
                { data: 'status' },
                { data: 'operator' },
            ],
            columnDefs: [
                {
                    targets: [0],
                    render: (data, type, row) => {
                        let btnEdit = "<a href='{{ route('edit-cutting-scrap') }}/" + row.id + "' class='btn btn-primary btn-sm'><i class='fa fa-edit'></i></a>";
                        let btnDelete = "<button class='btn btn-danger btn-sm' onclick='destroyData(" + row.id + ", \"" + row.no_form + "\")'><i class='fa fa-trash'></i></button>";

                        return "<div class='d-flex gap-1 justify-content-center'>" + btnEdit + btnDelete + "</div>";
                    }
                },
                {
                    targets: '_all',
                    className: 'text-nowrap'
                }
            ]
        });

        function dataTableReload() {
            datatable.ajax.reload();
        }

        function destroyData(id, noForm) {
            Swal.fire({
                title: 'Hapus Form Cut Scrap?',
                html: 'Form <b>' + noForm + '</b> beserta detailnya akan dihapus.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'HAPUS',
                cancelButtonText: 'BATAL',
                confirmButtonColor: '#dc3545'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                document.getElementById("loading").classList.remove("d-none");

                $.ajax({
                    url: '{{ route('destroy-cutting-scrap') }}/' + id,
                    type: 'post',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'delete'
                    },
                    dataType: 'json',
                    success: function(res) {
                        document.getElementById("loading").classList.add("d-none");

                        Swal.fire({
                            icon: (res.status == 200 ? 'success' : 'error'),
                            title: (res.status == 200 ? 'Berhasil' : 'Gagal'),
                            html: res.message,
                            showConfirmButton: true,
                        });

                        dataTableReload();
                    },
                    error: function(jqXHR) {
                        document.getElementById("loading").classList.add("d-none");

                        console.error(jqXHR);
                    }
                });
            });
        }

        function exportExcel(element) {
            let dateFrom = $('#tgl-awal').val();
            let dateTo = $('#tgl-akhir').val();

            exportExcelGlobal(element, { dateFrom, dateTo });
        }
    </script>
@endsection
