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
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold"><i class="fas fa-boxes"></i> Detail Material BOM</h5>
                <button type="button" class="close text-dark btn-close-modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
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
                                <th>Product Set</th>
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
                <button type="button" class="btn btn-secondary btn-sm btn-close-modal" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Merge SO --}}
<div class="modal fade" id="modal-merge-so" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title"><i class="fas fa-code-branch"></i> Merge / Pindah Detail SO</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                {{-- Info SO Sumber --}}
                <div class="alert alert-info py-2 mb-3">
                    <strong><i class="fas fa-arrow-right"></i> SO Sumber:</strong>
                    <span id="merge-src-info">-</span>
                </div>

                {{-- Pilih SO Tujuan --}}
                <div class="form-group row align-items-center mb-3">
                    <label class="col-md-2 col-form-label font-weight-bold">SO Tujuan:</label>
                    <div class="col-md-6">
                        <select id="merge-dst-select" class="form-control select2bs4">
                            <option value="">-- Pilih SO Tujuan --</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted" id="merge-dst-info"></small>
                    </div>
                </div>

                {{-- Opsi No PO setelah Merge --}}
                <div class="form-group row align-items-start mb-3">
                    <label class="col-md-2 col-form-label font-weight-bold">No PO Hasil:</label>
                    <div class="col-md-10">
                        <div class="custom-control custom-radio mb-1">
                            <input type="radio" id="po_opt_combine" name="po_merge_option" value="combine" class="custom-control-input" checked>
                            <label class="custom-control-label" for="po_opt_combine">
                                Gabungkan <span class="text-muted small">(misal: <i>PO-001, PO-002</i>)</span>
                            </label>
                        </div>
                        <div class="custom-control custom-radio mb-1">
                            <input type="radio" id="po_opt_dst" name="po_merge_option" value="use_dst" class="custom-control-input">
                            <label class="custom-control-label" for="po_opt_dst">
                                Pakai No PO Tujuan saja <span class="badge badge-info" id="badge-po-dst">-</span>
                            </label>
                        </div>
                        <div class="custom-control custom-radio mb-1">
                            <input type="radio" id="po_opt_manual" name="po_merge_option" value="manual" class="custom-control-input">
                            <label class="custom-control-label" for="po_opt_manual">
                                Ketik Manual:
                                <input type="text" id="po_manual_input" class="form-control form-control-sm d-inline-block ml-1" style="width:220px;" placeholder="Tulis No PO..." disabled>
                            </label>
                        </div>
                    </div>
                </div>
                {{-- Tabel Detail SO Sumber --}}
                <div class="table-responsive" style="max-height: 55vh; overflow-y:auto;">
                    <table class="table table-bordered table-sm table-hover" id="table-merge-detail" style="font-size:12px;">
                        <thead class="bg-light text-center">
                            <tr>
                                <th width="3%">
                                    <input type="checkbox" id="check-all-merge" title="Pilih Semua">
                                </th>
                                <th>No</th>
                                <th>Color</th>
                                <th>Size</th>
                                <th>Product Set</th>
                                <th>Style</th>
                                <th>Qty</th>
                                <th>Ex FTY</th>
                                <th>Dest</th>
                            </tr>
                        </thead>
                        <tbody id="merge-detail-body">
                            <tr><td colspan="9" class="text-center text-muted">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-2">
                    <span class="badge badge-primary" id="merge-selected-count">0 baris dipilih</span>
                    <span class="ml-3 text-muted small">Centang baris yang ingin dipindahkan ke SO Tujuan.</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="button" class="btn btn-danger btn-sm" id="btn-execute-merge" onclick="executeMerge()">
                    <i class="fas fa-code-branch"></i> Pindahkan Detail
                </button>
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
                    { data: 'style', name: 'so.style' },
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
                const { so_no, kpno, buyer, style } = res.header || {};
                $('#so_no').text(so_no || '-');
                $('#kpno').text(kpno || '-');
                $('#buyer').text(buyer || '-');
                $('#style').text(style || '-');

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
                        <td>${item.product_set || '-'}</td>
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
        function syncBom(id) {
            Swal.fire({
                title: 'Sync BOM?',
                text: "Sistem akan mengecek dan menarik material terbaru dari Master BOM ke SO ini tanpa menghapus data yang sudah ada.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#007bff',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Sync!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Memproses Sync...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

                    let url = "{{ route('so-sync-bom', ':id') }}";
                    url = url.replace(':id', id);

                    $.ajax({
                        url: url,
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(res) {
                            if (res.status == 200) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Sync Berhasil!',
                                    html: `Material Baru: <b>${res.inserted}</b> baris<br>Diupdate: <b>${res.updated}</b> baris<br>Dibatalkan (Cancel): <b>${res.canceled}</b> baris`,
                                });
                                if(table) { table.ajax.reload(null, false); }
                            } else {
                                Swal.fire('Gagal!', res.message, 'error');
                            }
                        },
                        error: function(err) {
                            Swal.fire('Error!', 'Terjadi kesalahan saat proses Sync.', 'error');
                        }
                    });
                }
            });
        }

        // =============================================
        // MERGE SO JAVASCRIPT
        // =============================================

        let merge_so_src_id = null;

        function openMergeModal(id) {
            merge_so_src_id = id;
            $('#merge-detail-body').html('<tr><td colspan="9" class="text-center">Loading...</td></tr>');
            $('#merge-src-info').text('Memuat...');
            $('#merge-dst-select').html('<option value="">-- Pilih SO Tujuan --</option>');
            $('#merge-dst-info').text('');
            $('#merge-selected-count').text('0 baris dipilih');

            let url = "{{ route('so-merge-candidates', ':id') }}".replace(':id', id);

            $.getJSON(url, function(res) {
                if (res.status !== 200) {
                    Swal.fire('Gagal', res.message || 'Error', 'error');
                    return;
                }

                $('#merge-src-info').text(res.source.so_no + ' | PO: ' + (res.source.no_po || '-'));

                if (res.candidates.length === 0) {
                    $('#merge-dst-select').html('<option value="">-- Tidak ada SO dengan BOM yang sama --</option>');
                } else {
                    let opts = '<option value="">-- Pilih SO Tujuan --</option>';
                    res.candidates.forEach(function(c) {
                        opts += `<option value="${c.id}" data-kpno="${c.kpno}" data-po="${c.no_po}" data-qty="${c.qty}">${c.so_no} | WS: ${c.kpno} | PO: ${c.no_po || '-'} | Qty: ${Number(c.qty).toLocaleString()}</option>`;
                    });
                    $('#merge-dst-select').html(opts);
                }

                if (typeof $.fn.select2 !== 'undefined') {
                    $('#merge-dst-select').select2({ dropdownParent: $('#modal-merge-so') });
                }

                loadMergeDetail(id);
            }).fail(function() {
                Swal.fire('Error', 'Gagal memuat data kandidat SO', 'error');
            });

            $('#merge-dst-select').on('change', function() {
                let opt = $(this).find(':selected');
                if ($(this).val()) {
                    $('#merge-dst-info').html(`<b>WS:</b> ${opt.data('kpno')} | <b>Qty saat ini:</b> ${Number(opt.data('qty')).toLocaleString()}`);
                    // Update badge no PO tujuan
                    let poDst = opt.data('po') || '-';
                    $('#badge-po-dst').text(poDst);
                } else {
                    $('#merge-dst-info').text('');
                    $('#badge-po-dst').text('-');
                }
            });

            // Enable/disable input manual
            $(document).off('change', 'input[name=po_merge_option]').on('change', 'input[name=po_merge_option]', function() {
                if ($(this).val() === 'manual') {
                    $('#po_manual_input').prop('disabled', false).focus();
                } else {
                    $('#po_manual_input').prop('disabled', true);
                }
            });

            $('#modal-merge-so').modal('show');
        }

        function loadMergeDetail(id) {
            let url = "{{ route('so-merge-source-detail', ':id') }}".replace(':id', id);
            $.getJSON(url, function(res) {
                if (res.status !== 200) return;
                let rows = '';
                res.data.forEach(function(d, i) {
                    rows += `
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" class="merge-check" value="${d.id}">
                            </td>
                            <td class="text-center">${i + 1}</td>
                            <td>${d.color || '-'}</td>
                            <td>${d.size || '-'}</td>
                            <td>${d.product_set || '-'}</td>
                            <td>${d.styleno_prod || '-'}</td>
                            <td class="text-right">${Number(d.qty).toLocaleString()}</td>
                            <td class="text-center">${d.deldate_det || '-'}</td>
                            <td>${d.dest || '-'}</td>
                        </tr>`;
                });
                $('#merge-detail-body').html(rows || '<tr><td colspan="9" class="text-center text-muted">Tidak ada data</td></tr>');

                // Event: update counter
                $(document).on('change', '.merge-check', function() {
                    let count = $('.merge-check:checked').length;
                    $('#merge-selected-count').text(count + ' baris dipilih');
                });
            });
        }

        // Check All
        $('#check-all-merge').on('change', function() {
            $('.merge-check').prop('checked', $(this).is(':checked'));
            let count = $('.merge-check:checked').length;
            $('#merge-selected-count').text(count + ' baris dipilih');
        });

        function executeMerge() {
            let id_so_dst = $('#merge-dst-select').val();
            let det_ids   = [];
            $('.merge-check:checked').each(function() {
                det_ids.push($(this).val());
            });

            if (!id_so_dst) {
                Swal.fire('Perhatian', 'Silakan pilih SO Tujuan terlebih dahulu.', 'warning');
                return;
            }
            if (det_ids.length === 0) {
                Swal.fire('Perhatian', 'Centang minimal 1 baris detail yang akan dipindahkan.', 'warning');
                return;
            }

            Swal.fire({
                title: 'Konfirmasi Merge',
                html: `Anda akan memindahkan <b>${det_ids.length} baris</b> dari SO Sumber ke SO Tujuan.<br><small class="text-muted">Jika SO Sumber kosong setelah dipindah, SO tersebut otomatis di-void.</small>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Pindahkan!',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#dc3545'
            }).then(function(result) {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                    let po_merge_option = $('input[name=po_merge_option]:checked').val();
                    let po_manual       = $('#po_manual_input').val();

                    $.ajax({
                        url: "{{ route('so-execute-merge') }}",
                        type: 'POST',
                        data: {
                            _token          : "{{ csrf_token() }}",
                            id_so_src       : merge_so_src_id,
                            id_so_dst       : id_so_dst,
                            det_ids         : det_ids,
                            po_merge_option : po_merge_option,
                            po_manual       : po_manual
                        },
                        success: function(res) {
                            if (res.status === 200) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    html: res.message + `<br><small>Qty SO Sumber: <b>${Number(res.qty_src || 0).toLocaleString()}</b> | Qty SO Tujuan: <b>${Number(res.qty_dst || 0).toLocaleString()}</b></small>`
                                }).then(function() {
                                    $('#modal-merge-so').modal('hide');
                                    table.ajax.reload(null, false);
                                });
                            } else {
                                Swal.fire('Gagal', res.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan server';
                            Swal.fire('Error!', msg, 'error');
                        }
                    });
                }
            });
        }

    </script>
@endsection
