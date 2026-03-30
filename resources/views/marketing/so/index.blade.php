@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <style>
        #table-material-so thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            background: #f8f9fa;
            vertical-align: middle;
        }
        .modal-xl {
            max-width: 95% !important;
        }

        .table-scroll-modal {
            max-height: 60vh;
            overflow-y: auto;
            overflow-x: auto;
        }

        #table-detail-so thead th,
        #table-material-so thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            background: #f8f9fa;
            vertical-align: middle;
            box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.2);
        }

        .modal-xl {
            max-width: 95% !important;
        }

        #table-material-so thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background-color: #f8f9fa;
            box-shadow: inset 0 -1px 0 #dee2e6, inset 0 1px 0 #dee2e6;
        }
    </style>
@endsection

@section('content')
<div class="card card-sb">
    <div class="card-header">
        <h5 class="card-title fw-bold mb-0"><i class="fas fa-list"></i> List Data Sales Order</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <a href="{{ route('create-so') }}" class="btn btn-outline-primary">
                <i class="fas fa-plus"></i> Create SO
            </a>
        </div>

        <div class="row align-items-end mb-4">
            <div class="col-md-2">
                <label class="small fw-bold">Tgl Awal</label>
                <input type="date" id="date_from" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
            </div>
            <div class="col-md-2">
                <label class="small fw-bold">Tgl Akhir</label>
                <input type="date" id="date_to" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary btn-sm" onclick="refreshTable()">
                    <i class="fas fa-search"></i> Filter
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover w-100" id="table-so">
                <thead>
                    <tr class="text-center">
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>No SO</th>
                        <th>No PO</th>
                        <th>WS</th>
                        <th>Style</th>
                        <th>Buyer</th>
                        <th>Product</th>
                        <th>Item Name</th>
                        <th>Qty</th>
                        <th width="15%">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal fade" id="modal-detail" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-sb text-white">
                <h5 class="modal-title"><i class="fas fa-info-circle"></i> Detail Sales Order</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="row mb-2">
                    <div class="col-md-4">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th width="30%">No SO</th>
                                <td>: <span id="so_no">-</span></td>
                            </tr>
                            <tr>
                                <th>WS</th>
                                <td>: <span id="kpno">-</span></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-8">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th>Style</th>
                                <td>: <span id="style">-</span></td>
                            </tr>
                            <tr>
                                <th width="30%">Buyer</th>
                                <td>: <span id="buyer">-</span></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="table-responsive table-scroll-modal">
                    <table class="table table-bordered table-sm w-100" id="table-detail-so">
                        <thead class="bg-light text-center">
                            <tr>
                                <th>Color</th>
                                <th width="20%">Size</th>
                                <th width="20%">Qty</th>
                                <th width="15%">Action</th>
                            </tr>
                        </thead>
                        <tbody id="dtl_table_body">
                            </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success btn-sm" onclick="saveAllQtySO()"><i class="fas fa-save"></i> Edit All Qty</button>
                <button type="button" class="btn btn-outline-danger btn-sm" data-bs-dismiss="modal"><i class="fas fa-times-circle"></i> Tutup</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modal-detail-material" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document"> <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold"><i class="fas fa-boxes"></i> Detail Material BOM</h5>
                <button type="button" class="close text-dark btn-close-modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive table-scroll-modal">
                    <table class="table table-bordered table-sm w-100 text-nowrap" id="table-material-so" style="font-size: 11px;">
                        <thead class="bg-light text-center">
                            <tr>
                                <th>No</th>
                                <th>ID Item</th>
                                <th>ID Contents</th>
                                <th>Panel</th>
                                <th>Dest</th>
                                <th>Color Gmt</th>
                                <th>Size Gmt</th>
                                <th>Item</th>
                                <th>Qty Gmt</th>
                                <th>Cons</th>
                                <th>Qty BOM</th>
                                <th>Unit</th>
                                <th>Notes</th>
                                <th>Created By</th>
                                <th>Rule BOM</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm btn-close-modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom-script')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>

    <script>

        $('.btn-close-modal').on('click', function() {
            $('#modal-detail-material').modal('hide');
        });

        $('#modal-detail-material').on('hidden.bs.modal', function () {
            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();
        });

        $(".close").click(function(){
            $("#modal-detail").modal("hide");
        });

        let table;
        $(document).ready(function() {
            table = $('#table-so').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('master-marketing-so') }}",
                    data: function (d) {
                        d.date_from = $('#date_from').val();
                        d.date_to = $('#date_to').val();
                    }
                },
                lengthMenu: [[5, 10, 25, -1], [5, 10, 25, "All"]],
                pageLength: -1,
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'd_insert', name: 'so.d_insert', className: 'text-center' },
                    { data: 'so_no', name: 'so.so_no' },
                    { data: 'no_po', name: 'so.no_po' },
                    { data: 'kpno', name: 'act.kpno' },
                    { data: 'styleno', name: 'act.styleno' },
                    { data: 'buyer', name: 'ms.Supplier' },
                    { data: 'product_group', name: 'mp.product_group' },
                    { data: 'product_item', name: 'mp.product_item' },
                    { data: 'qty', name: 'so.qty', className: 'text-right' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ]
            });
        });

        function refreshTable() {
            table.ajax.reload();
        }


        let tableDetail;
        let current_so_id = null;

        function showDetail(id) {
            current_so_id = id;
            $('#modal-detail').modal('show');

            if ($.fn.DataTable.isDataTable('#table-detail-so')) {
                $('#table-detail-so').DataTable().destroy();
            }

            $('#table-detail-so tbody').html('<tr><td colspan="4" class="text-center">Loading...</td></tr>');

            let url = "{{ route('get-detail-so', ':id') }}";
            url = url.replace(':id', id);

            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json'
            })
            .done((res) => {
                const { so_no, kpno, buyer, styleno } = res.header || {};
                $('#so_no').text(so_no || '-');
                $('#kpno').text(kpno || '-');
                $('#buyer').text(buyer || '-');
                $('#style').text(styleno || '-');

                let rows = '';
                res.details.forEach(item => {
                    let detail_id = item.id;

                    let isCanceled = (item.cancel === 'Y');

                    let readonly = isCanceled ? 'readonly' : '';
                    let bgClass = isCanceled ? 'bg-light text-muted' : '';

                    let actionBtn = isCanceled
                        ? `<button type="button" class="btn btn-sm btn-info" onclick="toggleCancelRestoreSO(${detail_id}, 'restore')"><i class="fas fa-undo"></i> Restore</button>`
                        : `<button type="button" class="btn btn-sm btn-danger" onclick="toggleCancelRestoreSO(${detail_id}, 'cancel')"><i class="fas fa-times"></i> Cancel</button>`;

                    rows += `<tr class="${bgClass}">
                        <td class="align-middle">${item.color || '-'}</td>
                        <td class="text-center align-middle">${item.size || '-'}</td>
                        <td>
                            <input type="number" id="qty_input_${detail_id}" data-id="${detail_id}" class="form-control form-control-sm text-right qty-input" value="${item.qty}" ${readonly}>
                        </td>
                        <td class="text-center align-middle">
                            ${actionBtn}
                        </td>
                    </tr>`;
                });

                $('#table-detail-so tbody').html(rows);

                tableDetail = $('#table-detail-so').DataTable({
                    "paging": true,
                    "ordering": true,
                    "info": true,
                    "searching": true,
                    "lengthMenu": [[5, 10, 25, -1], [5, 10, 25, "All"]],
                    "pageLength": -1
                });
            })
            .fail((xhr) => {
                console.error(xhr.responseText);
                Swal.fire('Error', 'Gagal memuat data detail.', 'error');
            });
        }

        function toggleCancelRestoreSO(detail_id, action) {
            let titleText = action === 'cancel' ? 'Batalkan Item ini?' : 'Kembalikan Item ini?';

            Swal.fire({
                title: titleText,
                text: "Total Qty di SO akan dihitung ulang otomatis.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: (action === 'cancel' ? '#d33' : '#17a2b8'),
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Proses!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

                    $.ajax({
                        url: "{{ route('cancel-restore-so') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            id: detail_id,
                            action: action
                        },
                        success: function(res) {
                            if (res.status == 200) {
                                Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 1500, showConfirmButton: false });

                                showDetail(current_so_id);
                                if(table) { table.ajax.reload(null, false); }
                            } else {
                                Swal.fire('Gagal!', res.message, 'error');
                            }
                        },
                        error: function(err) {
                            Swal.fire('Error!', 'Terjadi kesalahan sistem.', 'error');
                        }
                    });
                }
            });
        }

        function saveAllQtySO() {
            let dataToSave = [];
            let isValid = true;

            $('.qty-input').each(function() {
                let id = $(this).data('id');
                let qty = $(this).val();

                if (qty === '' || qty <= 0) {
                    isValid = false;
                }

                dataToSave.push({
                    id: id,
                    qty: qty
                });
            });

            if (!isValid) {
                Swal.fire('Peringatan!', 'Terdapat Qty yang kosong atau 0. Harap periksa kembali.', 'warning');
                return;
            }

            if (dataToSave.length === 0) {
                Swal.fire('Peringatan!', 'Tidak ada data untuk disimpan.', 'warning');
                return;
            }

            Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

            $.ajax({
                url: "{{ route('update-qty-so') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    data: dataToSave
                },
                success: function(res) {
                    if (res.status == 200) {
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 1500, showConfirmButton: false });

                        $("#modal-detail").modal("hide");
                        if(table) { table.ajax.reload(null, false); }
                    } else {
                        Swal.fire('Gagal!', res.message, 'error');
                    }
                },
                error: function(err) {
                    Swal.fire('Error!', 'Terjadi kesalahan saat menyimpan data.', 'error');
                }
            });
        }

        let tableMaterial;

        function showDetailMaterial(id) {
            $('#modal-detail-material').modal('show');

            if ($.fn.DataTable.isDataTable('#table-material-so')) {
                $('#table-material-so').DataTable().destroy();
            }

            $('#table-material-so tbody').html('<tr><td colspan="16" class="text-center">Loading Data Material...</td></tr>');

            let url = "{{ route('get-detail-material-so', ':id') }}";
            url = url.replace(':id', id);

            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json'
            })
            .done((res) => {
                let rows = '';
                res.data.forEach((item, index) => {
                    rows += `<tr>
                        <td class="text-center">${String(index + 1).padStart(3, '0')}</td>
                        <td>${item.id_item || '-'}</td>
                        <td>${item.id_contents || '-'}</td>
                        <td>${item.panel || '-'}</td>
                        <td>${item.dest || '-'}</td>
                        <td>${item.color_gmt || '-'}</td>
                        <td>${item.size_gmt || '-'}</td>
                        <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis;">${item.item_desc || '-'}</td>
                        <td class="text-right">${parseFloat(item.qty_gmt).toFixed(2) || 0}</td>
                        <td class="text-right">${parseFloat(item.cons).toFixed(2) || 0}</td>
                        <td class="text-right">${parseFloat(item.qty_bom).toFixed(2)}</td>
                        <td>${item.unit || '-'}</td>
                        <td>${item.notes || '-'}</td>
                        <td>${item.created_by || '-'}</td>
                        <td>${item.rule_bom || '-'}</td>
                        <td class="text-center">${item.status || '-'}</td>
                    </tr>`;
                });

                $('#table-material-so tbody').html(rows);

                tableMaterial = $('#table-material-so').DataTable({
                    "paging": true,
                    "info": true,
                    "searching": true,
                    "lengthMenu": [
                        [5, 10, 25, -1],
                        [5, 10, 25, "All"]
                    ],
                    "pageLength": -1,
                    "columnDefs": [
                        { "className": "align-middle", "targets": "_all" }
                    ]
                });
            })
            .fail((xhr) => {
                Swal.fire('Error', 'Gagal memuat detail material', 'error');
            });
        }
    </script>
@endsection
