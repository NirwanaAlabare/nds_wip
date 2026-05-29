@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0">Data Sewing Out</h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-end gap-1 mb-1">
                <div class="col-md-12">
                    <div class="form-group row">
                        <div class="col-md-2">
                            <div class="mb-1">
                                <label class="form-label">From</label>
                                <input type="date" class="form-control form-control-sm" id="tgl_awal" name="tgl_awal"
                                    value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-1">
                                <label class="form-label">To</label>
                                <input type="date" class="form-control form-control-sm" id="tgl_akhir" name="tgl_akhir"
                                    value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="col-md-6" style="padding-top:0.5rem;">
                            <div class="mt-4">
                                <button class="btn btn-sm btn-primary" onclick="dataTableReload()"><i
                                        class="fas fa-search"></i> Search</button>
                                <a href="{{ route('create-sewing-out') }}" class="btn btn-sm btn-info"><i
                                        class="fas fa-plus"></i> Add Data</a>
                                <button class="btn btn-sm btn-success" onclick="exportExcel()"><i
                                        class="fas fa-file-excel"></i> Export Excel</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped table-head-fixed w-100 text-nowrap">
                    <thead>
                        <tr>
                            <th class="text-center">No Trans</th>
                            <th class="text-center">Tgl Pengeluaran</th>
                            <th class="text-center">No PO</th>
                            <th class="text-center">Supplier</th>
                            <th class="text-center">Buyer</th>
                            <th class="text-center">Jenis Pengeluaran</th>
                            <th class="text-center">Jenis Dokumen</th>
                            <th class="text-center">Dibuat Oleh</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal Detail --}}
    <div class="modal fade" id="modalDetail" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h5 class="modal-title">Detail Sewing Out</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-6 mb-1"><strong>No Trans</strong><br><span id="d_no_bppb">-</span></div>
                        <div class="col-6 mb-1"><strong>Supplier</strong><br><span id="d_supplier">-</span></div>
                        <div class="col-6 mb-1"><strong>PO</strong><br><span id="d_no_po">-</span></div>
                        <div class="col-6 mb-1"><strong>Buyer</strong><br><span id="d_buyer">-</span></div>
                        <div class="col-6 mb-1"><strong>Tgl Trans</strong><br><span id="d_tgl_bppb">-</span></div>
                        <div class="col-6 mb-1"><strong>Status</strong><br><span id="d_status"
                                class="badge bg-secondary">-</span></div>
                    </div>
                    <hr>
                    <table class="table table-bordered table-striped table-sm" id="detailTable">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>No WS</th>
                                <th>Style</th>
                                <th>Item Desc</th>
                                <th>Color</th>
                                <th>Size</th>
                                <th class="text-end">Qty</th>
                                <th>Unit</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr>
                                <th colspan="6" class="text-end">Total</th>
                                <th class="text-end" id="tfoot_qty">0</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <script>
        let detailDT = null;

        let datatable = $("#datatable").DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            paging: true,
            searching: true,
            scrollY: '300px',
            scrollX: '300px',
            scrollCollapse: true,
            ajax: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('sewing-out') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: function(d) {
                    d.tgl_awal = $('#tgl_awal').val();
                    d.tgl_akhir = $('#tgl_akhir').val();
                },
            },
            columns: [{
                    data: 'no_bppb'
                },
                {
                    data: 'tgl_bppb'
                },
                {
                    data: 'no_po'
                },
                {
                    data: 'supplier'
                },
                {
                    data: 'buyer'
                },
                {
                    data: 'jenis_pengeluaran'
                },
                {
                    data: 'jenis_dok'
                },
                {
                    data: 'created_by'
                },
                {
                    data: 'status'
                },
                {
                    data: 'id'
                },
            ],
            columnDefs: [{
                    targets: [3, 4, 5, 6, 8],
                    render: (data) => data ? data.toUpperCase() : '-'
                },
                {
                    targets: [9],
                    render: (data, type, row) => {
                        let pdfUrl = "{{ route('print-pdf-sewing-out', ':id') }}".replace(':id', row.id);
                        let editUrl = "{{ route('edit-sewing-out', ':id') }}".replace(':id', row.id);
                        let exportUrl = "{{ route('export-excel-sewing-out-detail', ':id') }}".replace(
                            ':id', row.id);

                        if (row.status === 'DRAFT') {
                            return `<div class='d-flex gap-1 justify-content-center'>
                        <a href='${editUrl}' class='btn btn-sm btn-warning'><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                        <button class='btn btn-sm btn-info' onclick='showDetail("${row.id}")'><i class="fa-solid fa-eye"></i> Detail</button>
                        <a href='${pdfUrl}' class='btn btn-sm btn-secondary' target='_blank'><i class="fa-solid fa-print"></i> PDF</a>
                        <button class='btn btn-sm btn-danger' onclick='cancelSewingOut("${row.no_bppb}")'><i class="fa-solid fa-trash"></i></button>
                    </div>`;
                        } else if (row.status === 'APPROVED') {
                            return `<div class='d-flex gap-1 justify-content-center'>
                        <button class='btn btn-sm btn-info' onclick='showDetail("${row.id}")'><i class="fa-solid fa-eye"></i> Detail</button>
                        <a href='${pdfUrl}' class='btn btn-sm btn-secondary' target='_blank'><i class="fa-solid fa-print"></i> PDF</a>
                    </div>`;
                        } else {
                            return `<div class='d-flex gap-1 justify-content-center'>
                        <button class='btn btn-sm btn-info' onclick='showDetail("${row.id}")'><i class="fa-solid fa-eye"></i> Detail</button>
                    </div>`;
                        }
                    }
                }
            ]
        });

        function dataTableReload() {
            datatable.ajax.reload();
        }

        function exportExcel() {
            let from = $('#tgl_awal').val();
            let to = $('#tgl_akhir').val();
            let url = "{{ route('export-excel-sewing-out') }}?from=" + from + "&to=" + to;
            window.open(url, '_blank');
        }

        function showDetail(id) {
            let url = "{{ route('detail-sewing-out', ':id') }}".replace(':id', id);
            $.get(url, function(res) {
                $("#d_no_bppb").text(res?.header?.no_bppb ?? '-');
                $("#d_tgl_bppb").text(res?.header?.tgl_bppb ?? '-');
                $("#d_no_po").text(res?.header?.no_po ?? '-');
                $("#d_supplier").text(res?.header?.supplier ?? '-');
                $("#d_buyer").text(res?.header?.buyer ?? '-');

                let status = (res?.header?.status ?? '-').toUpperCase();
                let badge = status === 'APPROVED' ? 'bg-success' : (status === 'DRAFT' ? 'bg-warning text-dark' :
                    'bg-danger');
                $("#d_status").removeClass().addClass('badge ' + badge).text(status);

                if (detailDT !== null) detailDT.clear().destroy();
                $("#detailTable tbody").html('');

                let rows = '';
                res.detail.forEach((d, i) => {
                    rows += `<tr>
                <td>${i+1}</td><td>${d.kpno ?? ''}</td><td>${d.styleno ?? ''}</td>
                <td>${d.itemdesc ?? ''}</td><td>${d.color ?? ''}</td><td>${d.size ?? ''}</td>
                <td>${parseFloat(d.qty ?? 0)}</td><td>${d.unit ?? ''}</td>
            </tr>`;
                });
                $("#detailTable tbody").html(rows);

                detailDT = $("#detailTable").DataTable({
                    searching: true,
                    paging: true,
                    ordering: true,
                    info: false,
                    lengthChange: false,
                    pageLength: 10,
                    columnDefs: [{
                        targets: 6,
                        className: 'text-end',
                        render: (d) => (parseFloat(d) || 0).toLocaleString('en-US')
                    }],
                    footerCallback: function(row, data, start, end, display) {
                        let api = this.api();
                        let total = api.column(6, {
                            search: 'applied'
                        }).data().reduce((a, b) => {
                            let toNum = v => typeof v === 'string' ? parseFloat(v.replace(/,/g,
                                '')) || 0 : (typeof v === 'number' ? v : 0);
                            return toNum(a) + toNum(b);
                        }, 0);
                        $("#tfoot_qty").text(total.toLocaleString('en-US'));
                    }
                });

                $("#modalDetail").modal('show');
            });
        }

        function cancelSewingOut(no_bppb) {
            Swal.fire({
                title: 'Konfirmasi',
                text: 'Yakin ingin membatalkan ' + no_bppb + '?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Batalkan',
                cancelButtonText: 'Tidak',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('cancel-sewing-out') }}",
                        type: "POST",
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            no_bppb: no_bppb
                        },
                        success: function(res) {
                            if (res.status === 200) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: res.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                datatable.ajax.reload(null, false);
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: res.message
                                });
                            }
                        }
                    });
                }
            });
        }
    </script>
@endsection
