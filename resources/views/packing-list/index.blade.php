@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">

    <style type="text/css">
        .checkbox-xl .form-check-input {
            scale: 1.5;
        }

        #datatable th,
        #datatable td {
            white-space: nowrap;
            vertical-align: middle;
        }

        /* hilangkan padding-right 30px (ruang ikon sort) dari dataTables.bootstrap4 */
        table.dataTable#datatable>thead>tr>th {
            padding-right: .75rem !important;
        }

        .badge-status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
        }

        .badge-status-full {
            background: #dff4e7;
            color: #19713b;
        }

        .badge-status-partial {
            background: #fde8c4;
            color: #a15c00;
        }

        .badge-status-pending {
            background: #e2e8f0;
            color: #64748b;
        }

        /* Preview Pemenuhan Karton */
        .preview-info {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            background: #f8fafc;
            padding: 12px 16px;
            border-radius: 6px;
            border: 1px solid #e1e6eb;
            margin-bottom: 20px;
        }

        .preview-info div {
            font-size: 13px;
            color: #4a5568;
        }

        .preview-info b {
            color: #09244c;
        }

        .carton-grid-visual {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 16px;
            max-height: 380px;
            overflow-y: auto;
            padding: 8px 4px;
        }

        .carton-box {
            position: relative;
            border-radius: 8px;
            padding: 22px 12px 10px 12px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            font-size: 11px;
            border: 2px solid #37a866;
            background: #f0fdf4;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.06);
            cursor: pointer;
            user-select: none;
            transition: transform 0.15s, box-shadow 0.15s, border-color 0.15s;
            overflow: hidden;
        }

        .carton-box:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.12);
            border-color: #087cff;
        }

        .carton-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 12px;
            background: rgba(55, 168, 102, 0.25);
            border-bottom-left-radius: 3px;
            border-bottom-right-radius: 3px;
            border: 1px dashed #2e8b55;
            border-top: none;
        }

        .carton-box.empty {
            border-color: #cbd5e1;
            background: #f8fafc;
        }

        .carton-box.empty::before {
            background: rgba(203, 213, 225, 0.4);
            border-color: #94a3b8;
        }

        .carton-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }

        .carton-num {
            font-size: 13px;
            font-weight: 800;
            color: #09244c;
        }

        .carton-tipe-tag {
            font-size: 9px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 4px;
            background: #e2e8f0;
            color: #334155;
            white-space: nowrap;
        }

        .carton-status-tag {
            font-size: 10px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 4px;
            background: #dff4e7;
            color: #19713b;
        }

        .carton-box.empty .carton-status-tag {
            background: #e2e8f0;
            color: #64748b;
        }

        .carton-summary-body .summary-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            white-space: nowrap;
        }

        .summary-row .label {
            color: #64748b;
            font-weight: 600;
        }

        .summary-row .val {
            font-weight: 700;
            color: #09244c;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .carton-qty-badge {
            margin-top: 8px;
            background: #09244c;
            color: #fff;
            display: flex;
            justify-content: space-between;
            padding: 5px 8px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 11px;
        }

        .carton-box.empty .carton-qty-badge {
            background: #94a3b8;
        }

        .carton-box.partial {
            border-color: #f0ad4e;
            background: #fffbeb;
        }

        .carton-box.partial::before {
            background: rgba(240, 173, 78, 0.25);
            border-color: #d48806;
        }

        .carton-box.partial .carton-status-tag {
            background: #fde8c4;
            color: #a15c00;
        }

        .carton-box.partial .carton-qty-badge {
            background: #d48806;
        }

        .carton-box.active {
            border-color: #087cff;
            box-shadow: 0 0 0 3px rgba(8, 124, 255, 0.2);
        }

        /* Modal detail karton (ditumpuk di atas modal preview) */
        #exampleModalCartonDetail {
            z-index: 1065;
        }

        .modal-backdrop.carton-detail-backdrop {
            z-index: 1060;
        }

        .carton-detail-summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            text-align: center;
            border: 1px solid #37a866;
            background: #f0fdf4;
            border-radius: 6px;
            padding: 10px 12px;
            margin-bottom: 16px;
        }

        .carton-detail-summary.kurang {
            border-color: #dc3545;
            background: #fef2f2;
        }

        .carton-detail-summary.lebih {
            border-color: #f0ad4e;
            background: #fffbeb;
        }

        .carton-detail-summary .lbl {
            font-size: 12px;
            color: #64748b;
        }

        .carton-detail-summary .num {
            font-size: 16px;
            font-weight: 800;
            color: #09244c;
        }

        .carton-detail-summary .num.ok {
            color: #19713b;
        }

        .carton-detail-summary .num.warn {
            color: #dc3545;
        }

        .carton-detail-summary .num.over {
            color: #a15c00;
        }

        .carton-detail-table {
            font-size: 13px;
            margin-bottom: 0;
        }

        .carton-detail-table thead th {
            background: #f1f5f9;
            color: #09244c;
        }

        @media (max-width: 768px) {
            .preview-info {
                grid-template-columns: 1fr;
            }
        }

        /* loading tabel utama pakai Swal, sembunyikan kotak processing bawaan DataTables */
        #datatable_processing {
            display: none !important;
        }
    </style>
@endsection

@section('content')
    <div class="modal fade" id="exampleModalCheck" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalCheckLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h3 class="modal-title fs-5">List Detail Packing List</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class='row'>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label"><small><b>Buyer</b></small></label>
                                <input type="text" class="form-control form-control-sm" id = "modal_buyer"
                                    name = "modal_buyer" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label"><small><b>PO</b></small></label>
                                <input type="text" class="form-control form-control-sm" id = "modal_po"
                                    name = "modal_po" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label"><small><b>Dest</b></small></label>
                                <input type="text" class="form-control form-control-sm" id = "modal_dest"
                                    name = "modal_dest" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label"><small><b>Style</b></small></label>
                                <input type="text" class="form-control form-control-sm" id = "modal_style"
                                    name = "modal_style" readonly>
                            </div>
                        </div>
                    </div>
                    <div class='row'>
                        <div class="col-md-12 table-responsive">
                            <table id="datatable_detail_packing_list" class="table table-bordered table-hover 100 nowrap">
                                <thead>
                                    <tr>
                                        <th>PO</th>
                                        <th>Dest</th>
                                        <th>Style</th>
                                        <th>Barcode</th>
                                        <th>WS</th>
                                        <th>Color</th>
                                        <th>Size</th>
                                        <th>No.Carton</th>
                                        <th>Tipe Pack</th>
                                        <th>Qty</th>
                                        <th>Qty Scan</th>
                                        <th>Qty FG IN</th>
                                        <th>Qty FG Out</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th colspan="9"></th>
                                        <th> <input type = 'text' class="form-control form-control-sm"
                                                style="width:75px" readonly id = 'total_qty_chk'> </th>
                                        <th> <input type = 'text' class="form-control form-control-sm"
                                                style="width:75px" readonly id = 'total_qty_scan'> </th>
                                        <th> <input type = 'text' class="form-control form-control-sm"
                                                style="width:75px" readonly id = 'total_qty_fg_in'> </th>
                                        <th> <input type = 'text' class="form-control form-control-sm"
                                                style="width:75px" readonly id = 'total_qty_fg_out'> </th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                {{-- <div class="modal-footer">
            <button type="submit" class="btn btn-outline-success btn-sm"><i class="fas fa-check"></i> Simpan
            </button>
        </div> --}}
            </div>
        </div>
    </div>

    <div class="modal fade" id="exampleModalHapus" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalHapusLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h3 class="modal-title fs-5"><i class="far fa-edit"></i> Hapus Karton</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="form_h_karton" name='form_h_karton' method='post'
                        action="{{ route('hapus_packing_list') }}" onsubmit="submitForm(this, event)">
                        <div class='row'>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label"><small><b>Buyer</b></small></label>
                                    <input type="text" class="form-control form-control-sm" id = "modal_h_buyer"
                                        name = "modal_h_buyer" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label"><small><b>PO</b></small></label>
                                    <input type="text" class="form-control form-control-sm" id = "modal_h_po"
                                        name = "modal_h_po" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label"><small><b>Dest</b></small></label>
                                    <input type="text" class="form-control form-control-sm" id = "modal_h_dest"
                                        name = "modal_h_dest" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label"><small><b>Style</b></small></label>
                                    <input type="text" class="form-control form-control-sm" id = "modal_h_style"
                                        name = "modal_h_style" readonly>
                                </div>
                            </div>
                        </div>
                        <div class='row'>
                            <div class="col-md-12 table-responsive">
                                <table id="datatable_detail_packing_list_hapus" class="table table-bordered text-nowrap"
                                    style="width:100%;">
                                    <thead class="table-primary">
                                        <tr style='text-align:center; vertical-align:middle'>
                                            <th>
                                                <input class="form-check checkbox-xl" type="checkbox"
                                                    onclick="toggle(this);">
                                            </th>
                                            <th>PO</th>
                                            <th>Dest</th>
                                            <th>Style</th>
                                            <th>Barcode</th>
                                            <th>WS</th>
                                            <th>Color</th>
                                            <th>Size</th>
                                            <th>No.Carton</th>
                                            <th>Tipe Pack</th>
                                            <th>Qty</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between">
                                <div class="p-2 bd-highlight">
                                </div>
                                <div class="p-2 bd-highlight">
                                    <button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash"></i>
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="exampleModalPreview" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h3 class="modal-title fs-5"><i class="fas fa-box-open"></i> Visualisasi Pemenuhan Karton</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="preview-info" id="previewMeta"></div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold" style="font-size: 13px; color: #09244c;"><i class="fas fa-boxes"></i> Grid
                            Karton Order:</span>
                        <small class="text-primary">Klik salah satu kotak karton untuk melihat rincian isi &
                            kekurangan Qty</small>
                    </div>

                    <div class="carton-grid-visual" id="cartonGrid"></div>

                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-1" style="font-size: 12px;">
                            <span>Persentase Pemenuhan Karton</span>
                            <b id="previewCartonPct">0%</b>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar" id="previewCartonBar" style="width: 0%;"></div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="d-flex justify-content-between mb-1" style="font-size: 12px;">
                            <span>Persentase Pemenuhan Qty Items</span>
                            <b id="previewQtyPct">0%</b>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" id="previewQtyBar" style="width: 0%;"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="exampleModalCartonDetail" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h3 class="modal-title fs-5 fw-bold" id="cartonDetailTitle">Detail Rincian CTN</h3>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="carton-detail-summary" id="cartonDetailSummary"></div>

                    <div class="fw-bold mb-2" style="font-size: 13px; color: #09244c;"><i
                            class="fas fa-clipboard-list"></i> Rincian Items dalam Karton Ini:</div>
                    <div class="table-responsive">
                        <table class="table table-bordered carton-detail-table text-nowrap">
                            <thead>
                                <tr>
                                    <th>Worksheet</th>
                                    <th>Style</th>
                                    <th>Color</th>
                                    <th class="text-center">Size</th>
                                    <th class="text-end">Qty</th>
                                </tr>
                            </thead>
                            <tbody id="cartonDetailBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Tutup Detail</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-box-open"></i> Packing List</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <a class="btn btn-outline-primary" id="btn-new-packing-list"
                    href="{{ route('create-packing-list-karton') }}">
                    <i class="fas fa-plus"></i>
                    Baru
                </a>
            </div>
            <div class="d-flex align-items-end gap-3 mb-3">
                <div>
                    <label class="form-label"><small><b>Tgl Shipment Awal</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal"
                        oninput="dataTableReload()" value="{{ $tgl_awal_fix }}">
                </div>
                <div>
                    <label class="form-label"><small><b>Tgl Shipment Akhir</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir"
                        oninput="dataTableReload()" value="{{ $tgl_akhir_fix }}">
                </div>
            </div>
            <div class="table-responsive">
                <table id="datatable" class="table table-bordered table-striped w-100 text-nowrap">
                    <thead class="table-primary">
                        <tr style='text-align:center; vertical-align:middle'>
                            <th>Status</th>
                            <th>Tgl Shipment</th>
                            <th>PO</th>
                            <th>Dest</th>
                            <th>Buyer</th>
                            <th>Style</th>
                            <th>Worksheet</th>
                            <th>Tot. Carton</th>
                            <th>Tot. Qty</th>
                            <th>Tgl Input</th>
                            <th>User</th>
                            <th>Action</th>
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
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>
    <script>
        $('#datatable thead tr').clone(true).appendTo('#datatable thead');
        $('#datatable thead tr:eq(1) th').each(function(i) {
            if (i === 11) {
                $(this).html('');
                return;
            }
            $(this).html('<input type="text" class="form-control form-control-sm"/>');
            $('input', this).on('keyup change', function() {
                if (datatable.column(i).search() !== this.value) {
                    datatable.column(i).search(this.value).draw();
                }
            });
        });

        // dipasang sebelum DataTable dibuat supaya loading pertama ikut tertangkap
        $('#datatable').on('processing.dt', function(e, settings, show) {
            if (show) {
                Swal.fire({
                    title: 'Loading...',
                    text: 'Please wait while data is loading.',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
            } else {
                Swal.close();
            }
        });

        let datatable = $('#datatable').DataTable({
            orderCellsTop: true,
            ordering: false,
            processing: true,
            serverSide: false,
            paging: true,
            pageLength: 10,
            lengthChange: false,
            autoWidth: false,
            // query berat (±10 detik), tampilkan loading
            language: {
                loadingRecords: '&nbsp;',
            },
            ajax: {
                url: '{{ route('packing-list-karton') }}',
                data: function(d) {
                    d.dateFrom = $('#tgl-awal').val();
                    d.dateTo = $('#tgl-akhir').val();
                },
            },
            columns: [{
                    data: 'status',
                    className: 'text-center',
                    render: function(data) {
                        let cls = {
                            FULL: 'badge-status-full',
                            PROSES: 'badge-status-partial',
                            PENDING: 'badge-status-pending'
                        };
                        return `<span class="badge-status ${cls[data] ?? 'badge-status-pending'}">${data ?? '-'}</span>`;
                    }
                },
                {
                    data: 'tgl_shipment_fix'
                },
                {
                    data: 'po'
                },
                {
                    data: 'dest'
                },
                {
                    data: 'buyer'
                },
                {
                    data: 'styleno'
                },
                {
                    data: 'ws'
                },
                {
                    data: 'tot_carton',
                    className: 'text-end',
                    render: function(data, type, row) {
                        let full = parseInt(row.carton_full) || 0;
                        let tot = parseInt(data) || 0;
                        return `${full.toLocaleString()}/${tot.toLocaleString()}`;
                    }
                },
                {
                    data: 'tot_qty',
                    className: 'text-end',
                    render: function(data, type, row) {
                        let isi = parseInt(row.qty_isi) || 0;
                        let tot = parseInt(data) || 0;
                        return `${isi.toLocaleString()}/${tot.toLocaleString()}`;
                    }
                },
                {
                    data: 'tgl_input'
                },
                {
                    data: 'created_by'
                },
                {
                    data: null,
                    className: 'text-center',
                    render: function(data, type, row, meta) {
                        return `
                            <div class="d-flex gap-1 justify-content-center">
                                <button type="button" class="btn btn-info btn-sm" title="Preview Pemenuhan Karton"
                                    onclick="show_preview_row(${meta.row})">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-primary btn-sm" title="Detail Packing List"
                                    onclick="act_row(${meta.row}, 'detail')">
                                    <i class="fas fa-search"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" title="Hapus Karton"
                                    onclick="act_row(${meta.row}, 'hapus')">
                                    <i class="fas fa-trash"></i>
                                </button>
                                ${parseInt(row.qty_sisa) > 0 ? `
                                <button type="button" class="btn btn-primary btn-sm" title="Tambah Packing List"
                                    onclick="act_row(${meta.row}, 'tambah')">
                                    <i class="fas fa-plus"></i>
                                </button>` : ''}
                                <button type="button" class="btn btn-success btn-sm" title="Export Excel"
                                    onclick="act_row(${meta.row}, 'export')">
                                    <i class="fas fa-file-excel"></i>
                                </button>
                            </div>
                        `;
                    }
                },
            ],
            columnDefs: [{
                targets: '_all',
                defaultContent: '-'
            }],
        });

        function dataTableReload() {
            datatable.ajax.reload();
        }

        function show_preview_row(rowIdx) {
            let row = datatable.row(rowIdx).data();
            if (!row) return;

            $('#exampleModalPreview').modal('show');
            show_preview(row.po, row.dest, row.buyer, row.styleno, row.tgl_shipment_fix, row.tot_carton, row.tot_qty);
        }

        // Preview Pemenuhan Karton
        let previewCartons = [];

        function escHtml(v) {
            return $('<div>').text(v ?? '').html();
        }

        function uniqueJoin(rows, key) {
            let vals = [...new Set(rows.map(r => r[key]).filter(v => v !== null && v !== ''))];
            if (vals.length === 0) return '-';
            if (vals.length > 2) return `Mix (${vals.length})`;
            return vals.join(', ');
        }

        function show_preview(po_s, dest_s, buyer_s, style_s, tgl_shipment_s, tot_carton_s, tot_qty_s) {
            let renderMeta = (ws, buyer, style, totCarton) => {
                $('#previewMeta').html(`
                    <div><b>PO:</b> ${escHtml(po_s)}</div>
                    <div><b>WS:</b> ${escHtml(ws)}</div>
                    <div><b>Buyer:</b> ${escHtml(buyer)}</div>
                    <div><b>Style:</b> ${escHtml(style)}</div>
                    <div><b>Dest:</b> ${escHtml(dest_s)}</div>
                    <div><b>Shipment:</b> ${escHtml(tgl_shipment_s)}</div>
                `);
            };

            previewCartons = [];
            renderMeta('-', buyer_s, style_s, parseInt(tot_carton_s) || 0);
            $('#cartonGrid').html(
                `<div class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin"></i> Memuat data karton...</div>`);
            set_preview_progress(0, 0, 0, 0);

            $.ajax({
                url: '{{ route('show_preview_packing_list') }}',
                method: 'GET',
                data: {
                    po: po_s,
                    dest: dest_s
                },
                success: function(rows) {
                    // group per no_carton
                    let map = new Map();
                    rows.forEach(r => {
                        if (!map.has(r.no_carton)) map.set(r.no_carton, []);
                        map.get(r.no_carton).push(r);
                    });

                    previewCartons = [...map.entries()].map(([no_carton, items]) => {
                        let qty = items.reduce((a, r) => a + (parseInt(r.qty) || 0), 0);
                        let qtyScan = items.reduce((a, r) => a + (parseInt(r.qty_scan) || 0), 0);
                        let isFull = items.every(r => (parseInt(r.qty_scan) || 0) >= (parseInt(r.qty) || 0));
                        let status = qtyScan <= 0 ? 'empty' : (isFull ? 'full' : 'partial');
                        return {
                            no_carton,
                            items,
                            qty,
                            qtyScan,
                            status,
                            tipe: carton_tipe(items)
                        };
                    });

                    let buyerVal = uniqueJoin(rows, 'buyer');
                    let styleVal = uniqueJoin(rows, 'styleno');
                    renderMeta(uniqueJoin(rows, 'ws'), buyerVal !== '-' ? buyerVal : buyer_s,
                        styleVal !== '-' ? styleVal : style_s, previewCartons.length);

                    let statusLabel = {
                        full: 'FULL',
                        partial: 'PROSES',
                        empty: 'PENDING'
                    };

                    let boxesHTML = previewCartons.map((c, idx) => `
                        <div class="carton-box ${c.status === 'full' ? '' : c.status}" title="Detail CTN #${escHtml(c.no_carton)}"
                            onclick="show_preview_carton(${idx}, this)">
                            <div>
                                <div class="carton-header">
                                    <span class="carton-num"><i class="fas fa-box"></i> CTN #${escHtml(c.no_carton)}</span>
                                    <span class="carton-tipe-tag">${c.tipe}</span>
                                    <span class="carton-status-tag">${statusLabel[c.status]}</span>
                                </div>
                                <div class="carton-summary-body">
                                    <div class="summary-row"><span class="label">WS:</span> <span class="val">${escHtml(uniqueJoin(c.items, 'ws'))}</span></div>
                                    <div class="summary-row"><span class="label">Style:</span> <span class="val">${escHtml(uniqueJoin(c.items, 'styleno'))}</span></div>
                                    <div class="summary-row"><span class="label">Color:</span> <span class="val">${escHtml(uniqueJoin(c.items, 'color'))}</span></div>
                                    <div class="summary-row"><span class="label">Size:</span> <span class="val">${escHtml(uniqueJoin(c.items, 'size'))}</span></div>
                                    <div class="summary-row"><span class="label">Tipe Pack:</span> <span class="val">${escHtml(uniqueJoin(c.items, 'tipe_pack'))}</span></div>
                                </div>
                            </div>
                            <div class="carton-qty-badge">
                                <span>Total Isi:</span>
                                <span>${c.qtyScan.toLocaleString()} / ${c.qty.toLocaleString()} Pcs</span>
                            </div>
                        </div>
                    `).join('');

                    $('#cartonGrid').html(boxesHTML ||
                        `<div class="text-center text-muted py-3">Tidak ada data karton.</div>`);

                    let cartonFull = previewCartons.filter(c => c.status === 'full').length;
                    let totQty = previewCartons.reduce((a, c) => a + c.qty, 0);
                    let totScan = previewCartons.reduce((a, c) => a + Math.min(c.qtyScan, c.qty), 0);
                    set_preview_progress(cartonFull, previewCartons.length, totScan, totQty);
                },
                error: function() {
                    $('#cartonGrid').html(
                        `<div class="text-center text-danger py-3">Gagal memuat data karton.</div>`);
                }
            });
        }

        // tipe isi karton: SOLID / MIX SIZE / MIX COLOR / MIX COLOR & SIZE
        function carton_tipe(items) {
            let nColor = new Set(items.map(r => r.color)).size;
            let nSize = new Set(items.map(r => r.size)).size;
            if (nColor > 1 && nSize > 1) return 'MIX COLOR & SIZE';
            if (nColor > 1) return 'MIX COLOR';
            if (nSize > 1) return 'MIX SIZE';
            return 'SOLID';
        }

        function set_preview_progress(cartonFull, cartonTot, qtyScan, qtyTot) {
            let pctCarton = cartonTot > 0 ? Math.round(cartonFull / cartonTot * 100) : 0;
            let pctQty = qtyTot > 0 ? Math.round(qtyScan / qtyTot * 100) : 0;

            $('#previewCartonPct').text(`${pctCarton}% (${cartonFull.toLocaleString()}/${cartonTot.toLocaleString()} CTN)`);
            $('#previewCartonBar').css('width', pctCarton + '%');
            $('#previewQtyPct').text(`${pctQty}% (${qtyScan.toLocaleString()}/${qtyTot.toLocaleString()} Pcs)`);
            $('#previewQtyBar').css('width', pctQty + '%');
        }

        function show_preview_carton(idx, el) {
            let c = previewCartons[idx];
            if (!c) return;

            $('#cartonGrid .carton-box').removeClass('active');
            $(el).addClass('active');

            $('#cartonDetailTitle').text(`Detail Rincian CTN #${c.no_carton} (${c.tipe})`);

            let selisih = c.qty - c.qtyScan;
            let summaryCls = selisih === 0 ? '' : (selisih > 0 ? 'kurang' : 'lebih');
            let shortageHTML = selisih === 0 ?
                `<div class="num ok">0 Pcs (Lengkap)</div>` :
                (selisih > 0 ?
                    `<div class="num warn">${selisih.toLocaleString()} Pcs (Kurang)</div>` :
                    `<div class="num over">${Math.abs(selisih).toLocaleString()} Pcs (Lebih)</div>`);

            $('#cartonDetailSummary').removeClass('kurang lebih').addClass(summaryCls).html(`
                <div>
                    <div class="lbl">Kapasitas Target</div>
                    <div class="num">${c.qty.toLocaleString()} Pcs</div>
                </div>
                <div>
                    <div class="lbl">Qty Terisi</div>
                    <div class="num">${c.qtyScan.toLocaleString()} Pcs</div>
                </div>
                <div>
                    <div class="lbl">Kekurangan (Shortage)</div>
                    ${shortageHTML}
                </div>
            `);

            $('#cartonDetailBody').html(c.items.map(r => {
                let qty = parseInt(r.qty) || 0;
                let scan = parseInt(r.qty_scan) || 0;
                let cls = scan === qty ? '' : (scan < qty ? 'text-danger' : 'text-warning');
                return `
                    <tr>
                        <td class="fw-bold">${escHtml(r.ws)}</td>
                        <td>${escHtml(r.styleno)}</td>
                        <td>${escHtml(r.color)}</td>
                        <td class="text-center fw-bold">${escHtml(r.size)}</td>
                        <td class="text-end fw-bold ${cls}">${scan.toLocaleString()}/${qty.toLocaleString()} Pcs</td>
                    </tr>
                `;
            }).join(''));

            $('#exampleModalCartonDetail').modal('show');
        }

        // backdrop modal detail harus di atas modal preview
        $('#exampleModalCartonDetail').on('shown.bs.modal', function() {
            $('.modal-backdrop').last().addClass('carton-detail-backdrop');
        });

        // body tetap modal-open selama modal preview masih terbuka
        $('#exampleModalCartonDetail').on('hidden.bs.modal', function() {
            if ($('#exampleModalPreview').hasClass('show')) {
                $('body').addClass('modal-open');
            }
        });

        // ===== Action (sama dengan packing-packing-list) =====
        function act_row(rowIdx, act) {
            let row = datatable.row(rowIdx).data();
            if (!row) return;

            if (act === 'detail') {
                show_data(row.po, row.dest, row.buyer, row.styleno);
                $('#exampleModalCheck').modal('show');
            } else if (act === 'hapus') {
                show_data_h(row.po, row.dest, row.buyer, row.styleno);
                $('#exampleModalHapus').modal('show');
            } else if (act === 'tambah') {
                window.location.href = '{{ route('create-repeat-packing-list-karton') }}?' + $.param({ po: row.po, dest: row.dest });
            } else if (act === 'export') {
                export_excel(row.po, row.dest, row.buyer, row.styleno);
            }
        }

        // ceklis semua hanya di tabel hapus
        function toggle(source) {
            $('#datatable_detail_packing_list_hapus input[name^="cek_data"]').prop('checked', source.checked);
        }

        // refresh tabel utama setelah hapus karton
        $('#exampleModalHapus').on('hidden.bs.modal', function() {
            dataTableReload();
        });

        // tabel di modal baru diukur ulang setelah modal tampil
        $('#exampleModalCheck, #exampleModalHapus').on('shown.bs.modal', function() {
            $.fn.dataTable.tables({
                visible: true,
                api: true
            }).columns.adjust();
        });

        function show_data(po_s, dest_s, buyer_s, style_s) {

            $('#modal_po').val(po_s);
            $('#modal_dest').val(dest_s);
            $('#modal_buyer').val(buyer_s);
            $('#modal_style').val(style_s);
            dataTableDetailPackingListReload();
        }

        function dataTableDetailPackingListReload() {
            datatable_detail_packing_list.ajax.reload();
        }

        $('#datatable_detail_packing_list thead tr').clone(true).appendTo('#datatable_detail_packing_list thead');
        $('#datatable_detail_packing_list thead tr:eq(1) th').each(function(i) {
            var title = $(this).text();
            $(this).html('<input type="text" class="form-control form-control-sm"/>');
            $('input', this).on('keyup change', function() {
                if (datatable_detail_packing_list.column(i).search() !== this.value) {
                    datatable_detail_packing_list
                        .column(i)
                        .search(this.value)
                        .draw();
                }
            });
        });

        let datatable_detail_packing_list = $("#datatable_detail_packing_list").DataTable({
            "footerCallback": function(row, data, start, end, display) {
                var api = this.api(),
                    data;

                // converting to interger to find total
                var intVal = function(i) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '') * 1 :
                        typeof i === 'number' ?
                        i : 0;
                };

                // computing column Total of the complete result
                var sumTotal = api
                    .column(9)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var sumTotalS = api
                    .column(10)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var sumTotalT = api
                    .column(11)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                var sumTotalU = api
                    .column(12)
                    .data()
                    .reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Update footer by showing the total with the reference of the column index
                $(api.column(0).footer()).html('Total');
                $(api.column(9).footer()).html(sumTotal);
                $(api.column(10).footer()).html(sumTotalS);
                $(api.column(11).footer()).html(sumTotalT);
                $(api.column(12).footer()).html(sumTotalU);
            },

            ordering: false,
            processing: true,
            serverSide: true,
            paging: true,
            searching: true,
            scrollY: '300px',
            scrollX: '300px',
            scrollCollapse: true,
            destroy: true,
            info: true,
            // jangan load saat halaman dibuka, baru load waktu modal dibuka (ajax.reload)
            deferLoading: 0,
            ajax: {
                url: '{{ route('show_detail_packing_list') }}',
                method: 'GET',
                data: function(d) {
                    d.po = $('#modal_po').val();
                    d.dest = $('#modal_dest').val();
                },
            },
            columns: [{
                    data: 'po'

                },
                {
                    data: 'dest'
                },
                {
                    data: 'styleno'
                },
                {
                    data: 'barcode'
                },
                {
                    data: 'ws'
                },
                {
                    data: 'color'
                },
                {
                    data: 'size'
                },
                {
                    data: 'no_carton'
                },
                {
                    data: 'tipe_pack'
                },
                {
                    data: 'qty'
                },
                {
                    data: 'qty_scan'
                },
                {
                    data: 'qty_fg_in'
                },
                {
                    data: 'qty_fg_out'
                },
                {
                    data: 'stat'
                },
            ],
            columnDefs: [{
                "className": "align-middle",
                "targets": "_all"
            }, ],
            rowsGroup: [
                7
            ],
            createdRow: function(row, data, dataIndex) {

                // Compare qty and qty_scan

                if (data.qty == data.qty_scan) {

                    // If they are equal, set font to bold and green for the entire row

                    $(row).css({
                        'font-weight': 'bold',
                        'color': 'green'
                    });

                } else {

                    // If they are not equal, set font color to blue for the entire row

                    $(row).css({
                        'font-weight': 'bold',
                        'color': 'blue'
                    });

                }

            }
        });

        function dataTableDetailPackingListHapusReload() {
            datatable_detail_packing_list_hapus.ajax.reload();
        }

        let datatable_detail_packing_list_hapus = $("#datatable_detail_packing_list_hapus").DataTable({

            ordering: false,
            processing: true,
            serverSide: true,
            paging: true,
            searching: true,
            scrollY: '300px',
            scrollX: '300px',
            scrollCollapse: true,
            destroy: true,
            info: true,
            // jangan load saat halaman dibuka, baru load waktu modal dibuka (ajax.reload)
            deferLoading: 0,
            ajax: {
                url: '{{ route('show_detail_packing_list_hapus') }}',
                method: 'GET',
                data: function(d) {
                    d.po = $('#modal_h_po').val();
                    d.dest = $('#modal_h_dest').val();
                },
            },
            columns: [{
                    data: 'id'

                }, {
                    data: 'po'

                }, {
                    data: 'dest'
                },
                {
                    data: 'styleno'
                },
                {
                    data: 'barcode'
                },
                {
                    data: 'ws'
                },
                {
                    data: 'color'
                },
                {
                    data: 'size'
                },
                {
                    data: 'no_carton'
                },
                {
                    data: 'tipe_pack'
                },
                {
                    data: 'qty'
                },

            ],
            columnDefs: [{
                    "className": "align-middle",
                    "targets": "_all"
                },
                {
                    targets: [0],
                    render: (data, type, row, meta) => {
                        if (row.qty_fg >= '1') {
                            return ``;
                        } else {
                            return `
                    <div
                        class="form-check checkbox-xl" style="text-align:center">
                        <input class="form-check-input" type="checkbox"
                        value="` + row.id + `" id="cek_data"  class = "chk" onchange="ceklis(this)"
                        name="cek_data[` + row.id + `] "/>
                    </div>
                    <div>
                            <input type="hidden" size="10" id="id"
                            name="id[` + row.id + `]" value = "` + row.id + `"/>
                    </div>
                    `;
                        }

                    }
                },
            ],
        });


        function show_data_h(po_s, dest_s, buyer_s, style_s) {

            $('#modal_h_po').val(po_s);
            $('#modal_h_dest').val(dest_s);
            $('#modal_h_buyer').val(buyer_s);
            $('#modal_h_style').val(style_s);
            dataTableDetailPackingListHapusReload();
        }

        function ceklis(checkeds) {
            //get id..and check if checked
            console.log($(checkeds).attr("value"), checkeds.checked)
        }



        function export_excel(po, dest, buyer, styleno) {
            // Step 1: Ask user which format they want
            Swal.fire({
                title: 'Pilih Format Export',
                showDenyButton: true,
                showCancelButton: true,
                confirmButtonText: 'Excel (.xlsx)',
                denyButtonText: 'CSV (.csv)',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (result.isConfirmed) {
                    sendExportRequest(po, dest, buyer, styleno, 'xlsx');
                } else if (result.isDenied) {
                    sendExportRequest(po, dest, buyer, styleno, 'csv');
                }
            });
        }

        // Step 2: The actual export AJAX request
        function sendExportRequest(po, dest, buyer, styleno, format) {
            Swal.fire({
                title: 'Please Wait...',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading();
                },
                allowOutsideClick: false,
            });

            $.ajax({
                type: "get",
                url: '{{ route('export_excel_packing_list') }}',
                data: {
                    po,
                    dest,
                    buyer,
                    styleno,
                    format // send chosen format to backend
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    Swal.close();
                    Swal.fire({
                        title: 'Data Sudah Di Export!',
                        icon: "success",
                        showConfirmButton: true,
                        allowOutsideClick: false
                    });

                    // Determine MIME type dynamically
                    const mimeType =
                        format === 'csv' ?
                        'text/csv;charset=utf-8;' :
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

                    const blob = new Blob([response], {
                        type: mimeType
                    });
                    const link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = `PO${po}.${format}`;
                    link.click();
                },
                error: function(xhr, status, error) {
                    Swal.close();
                    Swal.fire({
                        title: 'Gagal Mengekspor Data',
                        text: 'Terjadi kesalahan saat mengekspor. Silakan coba lagi.',
                        icon: 'error',
                        showConfirmButton: true,
                        allowOutsideClick: false
                    });

                    console.error("Export failed:", {
                        status: status,
                        error: error,
                        response: xhr.responseText
                    });
                }
            });
        }
    </script>
@endsection
