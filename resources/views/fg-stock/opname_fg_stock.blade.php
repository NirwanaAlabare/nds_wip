@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

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

        .detail-info-chip {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: #eef4ff;
            border: 1px solid #d7e3ff;
            color: var(--sb-color);
            font-weight: 600;
            font-size: .85rem;
            padding: .4rem .85rem;
            border-radius: 30px;
        }

        .detail-total-badge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            background: #e7f6ec;
            color: #1f8f4d;
            font-weight: 700;
            font-size: .85rem;
            padding: .4rem .85rem;
            border-radius: 30px;
        }

        #detail_item_table thead th {
            background: #f4f6fb;
            border-bottom: 2px solid #e3e7f1;
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: #5a5f73;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .detail-table-scroll {
            max-height: 400px;
            overflow-y: auto;
        }

        .qty-pill {
            display: inline-block;
            min-width: 2.2rem;
            padding: .2rem .55rem;
            border-radius: 20px;
            background: #e7f6ec;
            color: #1f8f4d;
            font-weight: 700;
            font-size: .8rem;
        }
    </style>
@endsection

@section('content')
    <!-- Modal Detail Item -->
    <div class="modal fade" id="modalDetailItem" tabindex="-1" role="dialog" aria-labelledby="modalDetailItemLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: none;">
                <div class="modal-header bg-sb text-light">
                    <h3 class="modal-title fs-5 mb-0"><i class="fas fa-list"></i> Detail Item Opname</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <span class="detail-info-chip" id="detail_item_header"></span>
                        <span class="detail-total-badge">
                            <i class="fas fa-cubes"></i> Total Qty: <span id="detail_total_qty">0</span>
                        </span>
                    </div>
                    <div class="form-group mb-2">
                        <input type="text" class="form-control form-control-sm" id="inp_search_detail_item"
                            placeholder="Cari Buyer / WS / Style / Dest / Color / Size / Grade..." autocomplete="off">
                    </div>
                    <div class="table-responsive detail-table-scroll">
                        <table class="table table-borderless table-sm table-hover align-middle" id="detail_item_table">
                            <thead>
                                <tr style="text-align:center; vertical-align:middle">
                                    <th style="width: 5%;">No</th>
                                    <th>Buyer</th>
                                    <th>WS</th>
                                    <th>Style</th>
                                    <th>Dest</th>
                                    <th>Color</th>
                                    <th>Size</th>
                                    <th>Grade</th>
                                    <th>Qty</th>
                                </tr>
                            </thead>
                            <tbody id="detail_item_body">
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Tutup</button>
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
                <div class="col-12 mb-3">
                    <a href="{{ route('create-opname-fg-stock') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus fa-sm"></i>
                        New
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
                            <th>No. Carton</th>
                            <th>No. Pallet</th>
                            <th>Status</th>
                            <th>Total Qty</th>
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

    <script>
        $(document).ready(function() {
            dataTableReload();
        })

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
                        data: 'no_carton'
                    },
                    {
                        data: 'no_pallet',
                        render: (data) => data || '-'
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
                        data: 'total_qty'
                    },
                    {
                        data: null,
                        render: (data, type, row) => {
                            let isOpen = row.status === 'OPEN';
                            let printBtn = isOpen ?
                                `<a class="btn btn-outline-primary btn-sm" href="javascript:void(0)"
                                    onclick="notifBelumClosed()" title="Cetak QR">
                                    <i class="fas fa-qrcode fa-sm"></i>
                                </a>` :
                                `<a class="btn btn-outline-primary btn-sm" target="_blank"
                                    href="{{ route('print-qr-opname-fg-stock') }}?no_carton=${encodeURIComponent(row.no_carton)}&periode=${encodeURIComponent(row.periode)}&no_opname=${encodeURIComponent(row.no_opname)}"
                                    title="Cetak QR">
                                    <i class="fas fa-qrcode fa-sm"></i>
                                </a>`;

                            let editBtn = isOpen ?
                                `<a class="btn btn-outline-warning btn-sm"
                                    href="{{ route('create-opname-fg-stock') }}?no_carton=${encodeURIComponent(row.no_carton)}&periode=${encodeURIComponent(row.periode)}"
                                    title="Edit">
                                    <i class="fas fa-edit fa-sm"></i>
                                </a>` :
                                `<a class="btn btn-outline-warning btn-sm" href="javascript:void(0)"
                                    onclick="notifSudahClosed()" title="Edit">
                                    <i class="fas fa-edit fa-sm"></i>
                                </a>`;

                            return `
                                <div class="d-flex gap-1 justify-content-center">
                                    <a class="btn btn-outline-secondary btn-sm" href="javascript:void(0)"
                                        onclick='viewDetailItem(${JSON.stringify(row)})' title="View">
                                        <i class="fas fa-eye fa-sm"></i>
                                    </a>
                                    ${editBtn}
                                    ${printBtn}
                                </div>`;
                        }
                    },
                ],
                columnDefs: [{
                    "className": "dt-center",
                    "targets": "_all"
                }, ]
            });
        }

        function notif() {
            alert("Maaf, Fitur belum tersedia!");
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

        function notifBelumClosed() {
            Swal.fire({
                title: 'Opname masih OPEN!',
                text: 'Selesaikan (Finish) opname terlebih dahulu sebelum bisa cetak QR.',
                icon: 'warning',
                showConfirmButton: true,
            });
        }

        function notifSudahClosed() {
            Swal.fire({
                title: 'Opname sudah CLOSED!',
                text: 'Data ini tidak bisa diedit lagi. Gunakan tombol View untuk melihat detailnya.',
                icon: 'warning',
                showConfirmButton: true,
            });
        }

        function viewDetailItem(row) {
            $('#inp_search_detail_item').val('');
            $('#detail_total_qty').text(0);
            $('#detail_item_body').html(
                '<tr><td colspan="9" class="text-center">Memuat data...</td></tr>');
            let statusCls = row.status === 'CLOSED' ? 'badge-status-closed' : 'badge-status-open';
            let statusBadge = row.status ?
                `<span class="badge ${statusCls}">${row.status}</span>` : '-';

            $('#detail_item_header').html(
                `<i class="fas fa-box-open"></i> No. Opname: <b>${row.no_opname}</b> &nbsp;|&nbsp; No. Carton: <b>${row.no_carton}</b> &nbsp;|&nbsp; No. Pallet: <b>${row.no_pallet || '-'}</b> &nbsp;|&nbsp; Status: ${statusBadge}`
            );

            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDetailItem')).show();

            $.get('{{ route('get-opname-items-fg-stock') }}', {
                no_carton: row.no_carton,
                periode: row.periode
            }, function(response) {
                $('#detail_item_body').empty();

                if (!response.items || response.items.length === 0) {
                    $('#detail_item_body').html(
                        '<tr><td colspan="9" class="text-center">Belum ada item</td></tr>');
                    updateDetailTotalQty();
                    return;
                }

                response.items.forEach(function(item, i) {
                    let destLabel = item.dest ? item.dest : '-';
                    let searchText =
                        `${item.buyer} ${item.ws} ${item.styleno} ${item.dest} ${item.color} ${item.size} ${item.grade}`
                        .toLowerCase();
                    $('#detail_item_body').append(`
                        <tr data-qty="${item.qty}" data-search="${searchText}">
                            <td class="text-center">${i + 1}</td>
                            <td class="text-center">${item.buyer}</td>
                            <td class="text-center">${item.ws}</td>
                            <td class="text-center">${item.styleno}</td>
                            <td class="text-center">${destLabel}</td>
                            <td class="text-center">${item.color}</td>
                            <td class="text-center">${item.size}</td>
                            <td class="text-center">${item.grade}</td>
                            <td class="text-center"><span class="qty-pill">${item.qty}</span></td>
                        </tr>`);
                });

                updateDetailTotalQty();
            }).fail(function() {
                $('#detail_item_body').html(
                    '<tr><td colspan="9" class="text-center text-danger">Gagal memuat data</td></tr>');
                updateDetailTotalQty();
            });
        }

        function updateDetailTotalQty() {
            let total = 0;
            $('#detail_item_body tr[data-qty]:visible').each(function() {
                total += parseFloat($(this).data('qty')) || 0;
            });
            $('#detail_total_qty').text(total);
        }

        $('#inp_search_detail_item').on('keyup', function() {
            let keyword = $(this).val().toLowerCase().trim();

            $('#detail_item_body tr[data-search]').each(function() {
                let match = $(this).data('search').toString().includes(keyword);
                $(this).toggle(match);
            });

            updateDetailTotalQty();
        });
    </script>
@endsection
