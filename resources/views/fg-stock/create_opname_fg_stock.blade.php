@extends('layouts.index')

@section('custom-link')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <style>
        .opname-hero {
            background: var(--sb-color);
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
            color: var(--light-color);
            margin-bottom: 1.25rem;
            box-shadow: 0 6px 18px rgba(0, 0, 0, .12);
        }

        .opname-hero h5 {
            margin: 0;
            font-weight: 700;
        }

        .opname-hero small {
            opacity: .85;
        }

        .opname-panel {
            background: #fff;
            border: 1px solid #eef0f5;
            border-radius: 12px;
            padding: 1.1rem 1.25rem;
            margin-bottom: 1.25rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .04);
        }

        .opname-panel-compact {
            padding: .75rem 1rem;
            margin-bottom: .75rem;
        }

        .opname-panel-compact .opname-panel-title {
            margin-bottom: .5rem;
        }

        .opname-panel-title {
            font-size: .8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: var(--sb-color);
            margin-bottom: .9rem;
            display: flex;
            align-items: center;
            gap: .4rem;
        }

        .carton-chip {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: #eef4ff;
            border: 1px solid #d7e3ff;
            color: var(--sb-color);
            font-weight: 600;
            font-size: .85rem;
            padding: .45rem .9rem;
            border-radius: 30px;
            transition: all .2s ease;
        }

        .carton-chip.is-empty {
            background: #f4f5f7;
            border-color: #e3e5ea;
            color: #8a8f9a;
        }

        .carton-chip.chip-edit-pallet {
            border-radius: .5rem;
            padding: .3rem .5rem .3rem .9rem;
        }

        .carton-chip i {
            font-size: .8rem;
        }

        .btn-add-item {
            width: 100%;
            height: calc(1.5em + .5rem + 2px);
            border-radius: .5rem;
            font-weight: 600;
            box-shadow: 0 2px 6px rgba(13, 110, 253, .25);
            transition: box-shadow .15s ease, transform .15s ease;
        }

        .btn-add-item:hover:not(:disabled) {
            box-shadow: 0 4px 10px rgba(13, 110, 253, .35);
            transform: translateY(-1px);
        }

        #tabel_item_carton thead th {
            background: #f4f6fb;
            border-bottom: 2px solid #e3e7f1;
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: #5a5f73;
        }

        #tabel_item_carton tbody tr {
            transition: background-color .15s ease;
        }

        #tabel_item_carton tbody tr:hover {
            background-color: #f8f9ff;
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

        .empty-state {
            text-align: center;
            padding: 2rem 1rem;
            color: #a3a8b5;
        }

        .empty-state i {
            font-size: 2rem;
            margin-bottom: .5rem;
            display: block;
        }

        .table-scroll {
            max-height: 280px;
            overflow-y: auto;
        }

        .table-scroll thead th {
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .status-chip {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            font-weight: 700;
            font-size: .8rem;
            padding: .45rem .9rem;
            border-radius: 30px;
        }

        .status-chip.status-open {
            background: #e7f6ec;
            border: 1px solid #c7ecd3;
            color: #1f8f4d;
        }

        .status-chip.status-closed {
            background: #fdeaea;
            border: 1px solid #f5c6c6;
            color: #c0392b;
        }

        .status-chip i {
            font-size: .6rem;
        }

        .total-qty-badge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            background: #e7f6ec;
            color: #1f8f4d;
            font-weight: 700;
            font-size: .85rem;
            padding: .4rem .9rem;
            border-radius: 30px;
        }

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
    <!-- Modal Tambah No. Carton -->
    <div class="modal fade" id="modalCarton" tabindex="-1" role="dialog" aria-labelledby="modalCartonLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: none;">
                <div class="modal-header bg-sb text-light">
                    <h3 class="modal-title fs-5 mb-0"><i class="fas fa-box-open"></i> Tambah No. Carton</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label"><small><b>No. Carton</b></small></label>
                        <input type="text" class="form-control form-control-sm" id="modal_no_carton"
                            placeholder="Contoh: CTN-00123" autocomplete="off" style="text-transform: uppercase;"
                            oninput="this.value = this.value.replace(/\s+/g, '')">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="simpanCarton()">
                        <i class="fas fa-check fa-sm"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah No. Pallet -->
    <div class="modal fade" id="modalPallet" tabindex="-1" role="dialog" aria-labelledby="modalPalletLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: none;">
                <div class="modal-header bg-sb text-light">
                    <h3 class="modal-title fs-5 mb-0"><i class="fas fa-pallet"></i> Tambah No. Pallet</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-4 form-group">
                            <label class="form-label"><small><b>Zone</b></small></label>
                            <input type="text" class="form-control form-control-sm" id="modal_pallet_zone"
                                placeholder="A" autocomplete="off" style="text-transform: uppercase;"
                                oninput="this.value = this.value.toUpperCase().replace(/\s+/g, '')">
                        </div>
                        <div class="col-4 form-group">
                            <label class="form-label"><small><b>Baris</b></small></label>
                            <input type="text" inputmode="numeric" class="form-control form-control-sm"
                                id="modal_pallet_baris" placeholder="1" autocomplete="off"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                        <div class="col-4 form-group mb-0">
                            <label class="form-label"><small><b>Kolom</b></small></label>
                            <input type="text" inputmode="numeric" class="form-control form-control-sm"
                                id="modal_pallet_kolom" placeholder="1" autocomplete="off"
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                    </div>
                    <div class="form-group mb-0 mt-2">
                        <small class="text-muted">No. Pallet: <b id="modal_pallet_preview">-</b></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="simpanPallet()">
                        <i class="fas fa-check fa-sm"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Isi Item Carton -->
    <div class="modal fade" id="modalIsiItem" tabindex="-1" role="dialog" aria-labelledby="modalIsiItemLabel"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: none;">
                <div class="modal-header bg-sb text-light py-2">
                    <div>
                        <h3 class="modal-title fs-5 mb-0"><i class="fas fa-box-open"></i> Isi Item Carton</h3>
                        <small>Kelola item hasil opname untuk carton ini</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-2">
                    <div class="d-flex align-items-center flex-wrap gap-2 mb-3">
                        <span class="carton-chip">
                            <i class="fas fa-box"></i>
                            <span>No. Carton: <b id="modal_isi_item_carton">-</b></span>
                        </span>
                        <span class="carton-chip" id="modal_isi_item_pallet_view">
                            <i class="fas fa-pallet"></i>
                            <span>No. Pallet: <b id="modal_isi_item_pallet">-</b></span>
                            <a href="javascript:void(0)" id="btn_edit_pallet" title="Ubah No. Pallet"
                                class="text-decoration-none">
                                <i class="fas fa-pen fa-xs"></i>
                            </a>
                        </span>
                        <span class="carton-chip chip-edit-pallet d-none" id="modal_isi_item_pallet_edit">
                            <i class="fas fa-pallet"></i>
                            <select class="form-control form-control-sm select2bs4" id="cbo_edit_pallet"
                                style="width: 140px;"></select>
                            <a href="javascript:void(0)" id="btn_save_pallet" title="Simpan"
                                class="text-success text-decoration-none">
                                <i class="fas fa-check"></i>
                            </a>
                            <a href="javascript:void(0)" id="btn_cancel_edit_pallet" title="Batal"
                                class="text-danger text-decoration-none">
                                <i class="fas fa-times"></i>
                            </a>
                        </span>
                    </div>

                    <div class="opname-panel opname-panel-compact">
                        <div class="opname-panel-title"><i class="fas fa-plus-circle"></i> Tambah Item ke Carton Ini
                        </div>
                        <div class="row align-items-end gy-2">
                            <div class="col-md-8 form-group">
                                <label class="form-label"><small><b>Buyer / WS</b></small></label>
                                <select class="form-control form-control-sm select2bs4-ajax" id="inp_buyer_ws"
                                    style="width: 100%;">
                                </select>
                                <input type="hidden" id="inp_buyer">
                                <input type="hidden" id="inp_ws">
                            </div>
                            <input type="hidden" id="inp_style">
                            <div class="col-md-4 form-group">
                                <label class="form-label"><small><b>Dest</b></small></label>
                                <select class="form-control form-control-sm select2bs4" id="inp_dest"
                                    style="width: 100%;" disabled>
                                    <option value="" selected disabled>- Pilih Dest -</option>
                                </select>
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="form-label"><small><b>Color</b></small></label>
                                <select class="form-control form-control-sm select2bs4" id="inp_color"
                                    style="width: 100%;" disabled>
                                    <option value="" selected disabled>- Pilih Color -</option>
                                </select>
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="form-label"><small><b>Size</b></small></label>
                                <select class="form-control form-control-sm select2bs4" id="inp_size"
                                    style="width: 100%;" disabled>
                                    <option value="" selected disabled>- Pilih Size -</option>
                                </select>
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="form-label"><small><b>Grade</b></small></label>
                                <select class="form-control form-control-sm select2bs4" id="inp_grade"
                                    style="width: 100%;">
                                    <option value="" selected disabled>- Pilih Grade -</option>
                                </select>
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="form-label"><small><b>Qty</b></small></label>
                                <input type="text" inputmode="numeric" class="form-control form-control-sm"
                                    id="inp_qty" autocomplete="off"
                                    oninput="this.value = this.value.replace(/[^0-9,]/g, '')">
                            </div>
                            <div class="col-md-4 form-group">
                                <button type="button" onclick="tambahItem()"
                                    class="btn btn-primary btn-sm btn-add-item">
                                    <i class="fas fa-plus fa-sm"></i> Tambah Item
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="opname-panel opname-panel-compact mb-0">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                            <div class="opname-panel-title mb-0"><i class="fas fa-list"></i> Detail Item Terdaftar
                            </div>
                            <span class="total-qty-badge" id="total_qty_badge">
                                <i class="fas fa-cubes"></i> Total Qty: <span id="total_qty_value">0</span>
                            </span>
                        </div>
                        <div class="form-group mb-2">
                            <input type="text" class="form-control form-control-sm" id="inp_search_item"
                                placeholder="Cari Buyer / WS / Style / Dest / Color / Size / Grade..." autocomplete="off">
                        </div>
                        <div class="table-responsive table-scroll">
                            <table class="table table-borderless table-sm table-hover align-middle"
                                id="tabel_item_carton">
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
                                        <th style="width: 12%;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tabel_item_carton_body">
                                    <tr id="row_empty">
                                        <td colspan="10">
                                            <div class="empty-state">
                                                <i class="fas fa-inbox"></i>
                                                Belum ada item ditambahkan
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between align-items-center flex-wrap gap-2 py-2">
                    <span class="status-chip status-open" id="modal_isi_item_status">
                        <i class="fas fa-circle"></i>
                        <span id="modal_isi_item_status_text">OPEN</span>
                    </span>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-success btn-sm" id="btn_finish_carton"
                            onclick="finishCarton()">
                            <i class="fas fa-qrcode fa-sm"></i> Finish &amp; Cetak QR
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Update Item -->
    <div class="modal fade" id="modalUpdateItem" tabindex="-1" role="dialog" aria-labelledby="modalUpdateItemLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: none;">
                <div class="modal-header bg-sb text-light">
                    <h3 class="modal-title fs-5 mb-0"><i class="fas fa-edit"></i> Update Item</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="modal_update_id">
                    <div class="form-group">
                        <label class="form-label"><small><b>Grade</b></small></label>
                        <select class="form-control form-control-sm select2bs4" id="modal_update_grade"
                            style="width: 100%;">
                            <option value="" selected disabled>- Pilih Grade -</option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label"><small><b>Qty</b></small></label>
                        <input type="text" inputmode="numeric" class="form-control form-control-sm"
                            id="modal_update_qty" autocomplete="off"
                            oninput="this.value = this.value.replace(/[^0-9,]/g, '')">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="simpanUpdateItem()">
                        <i class="fas fa-check fa-sm"></i> Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="opname-hero d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5><i class="fas fa-clipboard-list"></i> New Opname FG Stock</h5>
            <small>Pilih no. carton, lalu tambahkan item hasil opname per carton.</small>
        </div>
        <a href="{{ route('opname-fg-stock') }}" class="btn btn-light btn-sm">
            <i class="fas fa-arrow-left fa-sm"></i> Back
        </a>
    </div>

    <div class="opname-panel d-flex align-items-center flex-wrap gap-3" id="opname_header_bar"
        style="display:none !important;">
        <span class="carton-chip">
            <i class="fas fa-hashtag"></i>
            <span>No. Opname: <b id="hdr_no_opname">-</b></span>
        </span>
        <span class="carton-chip">
            <i class="fas fa-calendar-day"></i>
            <span>Tgl. Opname: <b id="hdr_tgl_opname">-</b></span>
        </span>
        <span class="carton-chip">
            <i class="fas fa-calendar-alt"></i>
            <span>Periode: <b id="hdr_periode">-</b></span>
        </span>
        <span class="status-chip status-open" id="hdr_status">
            <i class="fas fa-circle"></i>
            <span id="hdr_status_text">OPEN</span>
        </span>
    </div>

    <div class="opname-panel">
        <div class="opname-panel-title"><i class="fas fa-box"></i> No. Carton</div>
        <div class="row g-2 align-items-end">
            <div class="col-12 col-sm-6 col-lg-4">
                <label class="form-label mb-1"><small><b>No. Carton</b></small></label>
                <div class="d-flex gap-2">
                    <div class="flex-grow-1" style="min-width: 0;">
                        <select class="form-control form-control-sm select2bs4" id="cbo_no_carton" style="width: 100%;">
                            <option value="" selected disabled>- Pilih No. Carton -</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm flex-shrink-0" id="btn-add-carton"
                        data-bs-toggle="modal" data-bs-target="#modalCarton" title="Tambah No. Carton Baru">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-4">
                <label class="form-label mb-1"><small><b>No. Pallet</b></small></label>
                <div class="d-flex gap-2">
                    <div class="flex-grow-1" style="min-width: 0;">
                        <select class="form-control form-control-sm select2bs4" id="cbo_no_pallet" style="width: 100%;">
                            <option value="" selected disabled>- Pilih No. Pallet -</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm flex-shrink-0" id="btn-add-pallet"
                        data-bs-toggle="modal" data-bs-target="#modalPallet" title="Tambah No. Pallet Baru">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <button type="button" class="btn btn-success btn-sm w-100" id="btn_tambah_ke_list"
                    onclick="tambahKeList()">
                    <i class="fas fa-list fa-sm"></i> Tambah ke List
                </button>
            </div>
        </div>
    </div>

    <div class="opname-panel">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
            <div class="opname-panel-title mb-0"><i class="fas fa-list-ol"></i> List No Carton</div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-sm table-hover align-middle" id="tabel_carton_list"
                style="width: 100%;">
                <thead class="table-primary">
                    <tr style="text-align:center; vertical-align:middle">
                        <th style="width: 5%;" data-priority="5">No</th>
                        <th data-priority="1">No. Carton</th>
                        <th data-priority="6">No. Pallet</th>
                        <th data-priority="4">Total Qty</th>
                        <th data-priority="3">Status</th>
                        <th data-priority="9">User</th>
                        <th data-priority="7">Tgl Ditambahkan</th>
                        <th data-priority="8">Last Update</th>
                        <th style="width: 18%;" data-priority="2">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        <div class="d-flex justify-content-end mt-3">
            <button type="button" onclick="finishOpname()" class="btn btn-success btn-sm" id="btn_finish_opname">
                <i class="fas fa-flag-checkered fa-sm"></i> Finish Opname
            </button>
        </div>
    </div>
@endsection

@section('custom-script')
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script>
        // Select2 Autofocus
        $(document).on('select2:open', () => {
            document.querySelector('.select2-container--open .select2-search__field').focus();
        });

        // Initialize Select2BS4 Elements
        // Elemen di dalam modal butuh dropdownParent supaya dropdown-nya nempel
        // ke modal (bukan ke body), kalau tidak search box-nya kerebut fokus
        // oleh focus-trap Bootstrap modal dan jadi tidak bisa diketik.
        $('#modalIsiItem .select2bs4').select2({
            theme: 'bootstrap4',
            containerCssClass: 'form-control-sm',
            dropdownParent: $('#modalIsiItem'),
        });

        $('#inp_buyer_ws').select2({
            theme: 'bootstrap4',
            containerCssClass: 'form-control-sm',
            dropdownParent: $('#modalIsiItem'),
            placeholder: '- Ketik min. 2 huruf Product Item / Buyer / WS / Style -',
            allowClear: true,
            minimumInputLength: 2,
            ajax: {
                url: '{{ route('get-buyer-ws-opname-fg-stock') }}',
                dataType: 'json',
                delay: 300,
                data: function(params) {
                    return {
                        q: params.term,
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.map(function(row) {
                            return {
                                id: row.buyer + '||' + row.ws + '||' + row.styleno,
                                text: row.product_item + ' - ' + row.buyer + ' - ' + row.ws + ' - ' +
                                    row.styleno,
                                buyer: row.buyer,
                                ws: row.ws,
                                styleno: row.styleno,
                            };
                        }),
                    };
                },
            },
            language: {
                inputTooShort: function() {
                    return 'Ketik minimal 2 huruf...';
                },
                searching: function() {
                    return 'Mencari...';
                },
                noResults: function() {
                    return 'Tidak ditemukan';
                },
            },
        });

        $('#inp_buyer_ws').on('select2:select', function(e) {
            let data = e.params.data;
            $('#inp_buyer').val(data.buyer);
            $('#inp_ws').val(data.ws);
            $('#inp_style').val(data.styleno).trigger('change');
        });

        $('#inp_buyer_ws').on('select2:clear', function() {
            $('#inp_buyer').val('');
            $('#inp_ws').val('');
            $('#inp_style').val('').trigger('change');
        });

        $('#modalUpdateItem .select2bs4').select2({
            theme: 'bootstrap4',
            containerCssClass: 'form-control-sm',
            dropdownParent: $('#modalUpdateItem'),
        });

        $('.select2bs4').not('#modalIsiItem .select2bs4, #modalUpdateItem .select2bs4').select2({
            theme: 'bootstrap4',
            containerCssClass: 'form-control-sm',
        });

        const canChangeCartonStatus =
            {{ Auth::user()->roles()->whereIn('nama_role', ['superadmin', 'accounting'])->exists()? 'true': 'false' }};

        let itemCounter = 0;
        let sizeIdSoDetMap = {};
        let currentNoOpname = {!! json_encode(request('no_opname', '')) !!};
        let currentStatus = null;
        let allItems = [];
        let allCartons = [];
        let activeCarton = null;
        let activeCartonStatus = null;
        let activeCartonHasItems = false;

        let cartonListTable = $('#tabel_carton_list').DataTable({
            processing: true,
            serverSide: true,
            ordering: false,
            paging: false,
            searching: true,
            info: false,
            responsive: true,
            ajax: {
                url: '{{ route('get-carton-list-opname-fg-stock') }}',
                data: function(d) {
                    d.no_opname = currentNoOpname;
                },
            },
            language: {
                emptyTable: 'Belum ada No. Carton ditambahkan',
                zeroRecords: 'Tidak ditemukan',
                searchPlaceholder: 'Cari No. Carton / No. Pallet...',
                search: '',
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'no_carton',
                    name: 'no_carton',
                },
                {
                    data: 'no_pallet',
                    name: 'no_pallet',
                },
                {
                    data: 'qty',
                    name: 'qty',
                },
                {
                    data: 'status_badge',
                    name: 'status_badge',
                },
                {
                    data: 'created_by',
                    name: 'created_by',
                },
                {
                    data: 'created_at',
                    name: 'created_at',
                },
                {
                    data: 'updated_at',
                    name: 'updated_at',
                },
                {
                    data: 'aksi',
                    name: 'aksi',
                    orderable: false,
                    searchable: false,
                },
            ],
            columnDefs: [{
                className: 'text-center',
                targets: '_all',
            }],
        });

        function refreshCartonTable() {
            cartonListTable.ajax.reload(null, false);
        }

        $(document).ready(function() {
            loadMasterCarton();
            loadMasterPallet();
            loadGrade();

            if (!currentNoOpname) {
                Swal.fire({
                    title: 'Data opname tidak ditemukan!',
                    text: 'Silakan mulai dari tombol New di halaman List Opname.',
                    icon: 'warning',
                    showConfirmButton: true,
                });
                applyStatusLock();
                return;
            }

            loadOpnameHeader();
            loadOpnameItems();
        });

        function loadOpnameHeader() {
            $.get('{{ route('get-opname-header-fg-stock') }}', {
                no_opname: currentNoOpname
            }, function(response) {
                currentStatus = response.status;

                $('#hdr_no_opname').text(response.no_opname);
                $('#hdr_tgl_opname').text(response.tgl_opname);
                $('#hdr_periode').text(response.periode);

                let isClosed = currentStatus === 'CLOSED';
                $('#hdr_status').removeClass('status-open status-closed')
                    .addClass(isClosed ? 'status-closed' : 'status-open');
                $('#hdr_status_text').text(currentStatus);

                $('#opname_header_bar').css('display', 'flex');
                applyStatusLock();
            }).fail(function() {
                Swal.fire({
                    title: 'Data opname tidak ditemukan!',
                    icon: 'warning',
                    showConfirmButton: true,
                });
            });
        }

        function loadGrade() {
            $.get('{{ route('get-grade-opname-fg-stock') }}', function(data) {
                let $cbo = $('#inp_grade');
                $cbo.empty().append('<option value="" selected disabled>- Pilih Grade -</option>');

                let $cboModal = $('#modal_update_grade');
                $cboModal.empty().append('<option value="" selected disabled>- Pilih Grade -</option>');

                data.forEach(function(row) {
                    $cbo.append(new Option(row.grade, row.grade));
                    $cboModal.append(new Option(row.grade, row.grade));
                });

                $cbo.trigger('change');
                $cboModal.trigger('change');
            });
        }

        let masterCartonList = [];

        function loadMasterCarton(selected = null) {
            $.get('{{ route('get-master-carton-opname-fg-stock') }}', function(data) {
                masterCartonList = data;
                renderCartonDropdown(selected);
            });
        }

        function getUsedCartons() {
            let used = new Set();
            allCartons.forEach(row => used.add(row.no_carton));
            return used;
        }

        function renderCartonDropdown(selected = null) {
            let used = getUsedCartons();
            let $cbo = $('#cbo_no_carton');
            let current = selected !== null ? selected : $cbo.val();

            $cbo.empty().append('<option value="" disabled>- Pilih No. Carton -</option>');

            masterCartonList.forEach(function(row) {
                if (!used.has(row.no_carton) || row.no_carton === current) {
                    $cbo.append(new Option(row.no_carton, row.no_carton));
                }
            });

            $cbo.val(current).trigger('change');
        }

        function loadMasterPallet(selected = null) {
            $.get('{{ route('get-master-pallet-opname-fg-stock') }}', function(data) {
                let $cbo = $('#cbo_no_pallet');
                $cbo.empty().append('<option value="" disabled>- Pilih No. Pallet -</option>');

                data.forEach(function(row) {
                    $cbo.append(new Option(row.no_pallet, row.no_pallet));
                });

                $cbo.val(selected).trigger('change');
            });
        }

        $('#inp_style').on('change', function() {
            let buyer = $('#inp_buyer').val();
            let ws = $('#inp_ws').val();
            let styleno = $(this).val();

            $('#inp_dest').empty().append('<option value="" selected disabled>- Pilih Dest -</option>')
                .trigger('change').prop('disabled', true);
            $('#inp_color').empty().append('<option value="" selected disabled>- Pilih Color -</option>')
                .trigger('change').prop('disabled', true);
            $('#inp_size').empty().append('<option value="" selected disabled>- Pilih Size -</option>')
                .trigger('change').prop('disabled', true);

            if (!buyer || !ws || !styleno) {
                return;
            }

            $.get('{{ route('get-dest-opname-fg-stock') }}', {
                buyer: buyer,
                ws: ws,
                styleno: styleno
            }, function(data) {
                let $cbo = $('#inp_dest');
                $cbo.empty().append('<option value="" selected disabled>- Pilih Dest -</option>');

                data.forEach(function(row) {
                    let label = row.dest ? row.dest : '(Tanpa Dest)';
                    let value = row.dest ? row.dest : '__EMPTY__';
                    $cbo.append(new Option(label, value));
                });

                $cbo.prop('disabled', false);

                if (data.length === 1) {
                    $cbo.val(data[0].dest ? data[0].dest : '__EMPTY__');
                }

                $cbo.trigger('change');
            });
        });

        $('#inp_dest').on('change', function() {
            let buyer = $('#inp_buyer').val();
            let ws = $('#inp_ws').val();
            let styleno = $('#inp_style').val();
            let dest = $(this).val();

            $('#inp_color').empty().append('<option value="" selected disabled>- Pilih Color -</option>')
                .trigger('change').prop('disabled', true);
            $('#inp_size').empty().append('<option value="" selected disabled>- Pilih Size -</option>')
                .trigger('change').prop('disabled', true);

            if (!buyer || !ws || !styleno || !dest) {
                return;
            }

            $.get('{{ route('get-color-opname-fg-stock') }}', {
                buyer: buyer,
                ws: ws,
                styleno: styleno,
                dest: dest === '__EMPTY__' ? '' : dest
            }, function(data) {
                let $cbo = $('#inp_color');
                $cbo.empty().append('<option value="" selected disabled>- Pilih Color -</option>');

                data.forEach(function(row) {
                    $cbo.append(new Option(row.color, row.color));
                });

                $cbo.prop('disabled', false);

                if (data.length === 1) {
                    $cbo.val(data[0].color);
                }

                $cbo.trigger('change');
            });
        });

        $('#inp_color').on('change', function() {
            let ws = $('#inp_ws').val();
            let styleno = $('#inp_style').val();
            let dest = $('#inp_dest').val();
            let color = $(this).val();

            $('#inp_size').empty().append('<option value="" selected disabled>- Pilih Size -</option>')
                .trigger('change').prop('disabled', true);

            if (!ws || !styleno || !color) {
                return;
            }

            $.get('{{ route('get-size-opname-fg-stock') }}', {
                ws: ws,
                styleno: styleno,
                dest: dest === '__EMPTY__' ? '' : dest,
                color: color
            }, function(data) {
                let $cbo = $('#inp_size');
                $cbo.empty().append('<option value="" selected disabled>- Pilih Size -</option>');
                sizeIdSoDetMap = {};

                data.forEach(function(row) {
                    sizeIdSoDetMap[row.size] = row.id_so_det;
                    $cbo.append(new Option(row.size, row.size));
                });

                $cbo.prop('disabled', false).trigger('change');
            });
        });

        function applyStatusLock() {
            let isClosed = currentStatus === 'CLOSED';
            let isLocked = !currentNoOpname || isClosed;

            $('#cbo_no_carton, #cbo_no_pallet, #btn-add-carton, #btn-add-pallet, #btn_tambah_ke_list')
                .prop('disabled', isLocked);

            if (isClosed) {
                $('#inp_buyer_ws, #inp_style, #inp_dest, #inp_color, #inp_size, #inp_grade, #inp_qty, .btn-add-item')
                    .prop('disabled', true);
            } else {
                $('#inp_buyer_ws, #inp_grade, #inp_qty, .btn-add-item').prop('disabled', false);
            }

            $('#tabel_item_carton_body .btn-outline-danger, #tabel_item_carton_body .btn-outline-primary')
                .prop('disabled', isClosed).toggle(!isClosed);

            $('#btn_finish_opname').toggle(!isClosed);
        }

        function loadOpnameItems() {
            if (!currentNoOpname) {
                allItems = [];
                allCartons = [];
                renderCartonDropdown();
                return;
            }

            $.get('{{ route('get-opname-items-fg-stock') }}', {
                no_opname: currentNoOpname
            }, function(response) {
                allItems = response.items;
                allCartons = response.cartons;
                renderCartonDropdown();
                refreshCartonTable();

                if (activeCarton) {
                    renderModalItems();
                }
            });
        }

        function tambahKeList() {
            let noCarton = $('#cbo_no_carton').val();
            let noPallet = $('#cbo_no_pallet').val();

            if (!currentNoOpname || currentStatus === 'CLOSED') {
                Swal.fire({
                    title: 'Opname sudah CLOSED atau tidak ditemukan!',
                    icon: 'warning',
                    showConfirmButton: true,
                });
                return;
            }

            if (!noCarton) {
                Swal.fire({
                    title: 'Pilih No. Carton terlebih dahulu!',
                    icon: 'warning',
                    showConfirmButton: true,
                });
                return;
            }

            if (!noPallet) {
                Swal.fire({
                    title: 'Pilih No. Pallet terlebih dahulu!',
                    icon: 'warning',
                    showConfirmButton: true,
                });
                return;
            }

            let exists = allCartons.some(r => r.no_carton === noCarton && r.no_pallet === noPallet);

            if (exists) {
                Swal.fire({
                    title: 'No. Carton & No. Pallet ini sudah ada di list!',
                    icon: 'warning',
                    showConfirmButton: true,
                });
                return;
            }

            let usedElsewhere = allCartons.some(r => r.no_carton === noCarton && r.no_pallet !== noPallet);

            if (usedElsewhere) {
                Swal.fire({
                    title: 'No. Carton ini sudah terdaftar di Pallet lain pada opname ini!',
                    icon: 'warning',
                    showConfirmButton: true,
                });
                return;
            }

            $.ajax({
                type: 'POST',
                url: '{{ route('store-carton-header-opname-fg-stock') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    no_opname: currentNoOpname,
                    no_carton: noCarton,
                    no_pallet: noPallet,
                },
                success: function() {
                    $('#cbo_no_carton').val('').trigger('change');
                    $('#cbo_no_pallet').val('').trigger('change');
                    loadOpnameItems();
                },
                error: function(xhr) {
                    let message = xhr.responseJSON && xhr.responseJSON.message ?
                        xhr.responseJSON.message : 'Gagal menambahkan No. Carton ke list!';
                    Swal.fire({
                        title: message,
                        icon: 'warning',
                        showConfirmButton: true,
                    });
                },
            });
        }

        function hapusCartonRow(noCarton, noPallet) {
            if (currentStatus === 'CLOSED') {
                Swal.fire({
                    title: 'Opname sudah CLOSED, tidak bisa menghapus!',
                    icon: 'warning',
                    showConfirmButton: true,
                });
                return;
            }

            let itemCount = allItems.filter(i => i.no_carton === noCarton && i.no_pallet === noPallet)
                .length;

            let text = itemCount > 0 ?
                `Semua item (${itemCount}) pada carton ${noCarton} / pallet ${noPallet} akan dihapus.` :
                `Carton ${noCarton} / pallet ${noPallet} akan dihapus dari list.`;

            Swal.fire({
                title: 'Hapus No. Carton ini?',
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    type: 'POST',
                    url: '{{ route('hapus-carton-opname-fg-stock') }}',
                    data: {
                        _token: '{{ csrf_token() }}',
                        no_opname: currentNoOpname,
                        no_carton: noCarton,
                        no_pallet: noPallet,
                    },
                    success: function() {
                        loadOpnameItems();
                    },
                    error: function(xhr) {
                        let message = xhr.responseJSON && xhr.responseJSON.message ?
                            xhr.responseJSON.message : 'Gagal menghapus No. Carton!';
                        Swal.fire({
                            title: message,
                            icon: 'warning',
                            showConfirmButton: true,
                        });
                    },
                });
            });
        }

        function bukaIsiItem(noCarton, noPallet) {
            activeCarton = {
                no_carton: noCarton,
                no_pallet: noPallet
            };

            $('#modal_isi_item_carton').text(noCarton);
            $('#modal_isi_item_pallet').text(noPallet);
            cancelEditPallet();

            resetTambahItemForm();
            renderModalItems();

            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalIsiItem')).show();
        }

        function startEditPallet() {
            let isClosed = currentStatus === 'CLOSED' || activeCartonStatus === 'CLOSED';

            if (isClosed || !activeCarton || !activeCartonHasItems) {
                return;
            }

            $.get('{{ route('get-master-pallet-opname-fg-stock') }}', function(data) {
                let $cbo = $('#cbo_edit_pallet');
                $cbo.empty();

                data.forEach(function(row) {
                    $cbo.append(new Option(row.no_pallet, row.no_pallet));
                });

                $cbo.val(activeCarton.no_pallet).trigger('change');

                $('#modal_isi_item_pallet_view').addClass('d-none');
                $('#modal_isi_item_pallet_edit').removeClass('d-none');
            });
        }

        function cancelEditPallet() {
            $('#modal_isi_item_pallet_edit').addClass('d-none');
            $('#modal_isi_item_pallet_view').removeClass('d-none');
        }

        function savePallet() {
            let noPalletBaru = $('#cbo_edit_pallet').val();

            if (!noPalletBaru) {
                ToastNotif.fire({
                    icon: 'warning',
                    title: 'Pilih No. Pallet terlebih dahulu!',
                });
                return;
            }

            if (noPalletBaru === activeCarton.no_pallet) {
                cancelEditPallet();
                return;
            }

            let idDetails = allItems.filter(i => i.no_carton === activeCarton.no_carton && i.no_pallet ===
                activeCarton.no_pallet).map(i => i.id_detail);

            if (idDetails.length === 0) {
                ToastNotif.fire({
                    icon: 'error',
                    title: 'Item pada carton ini tidak ditemukan!',
                });
                return;
            }

            $.ajax({
                url: '{{ route('update-carton-pallet-opname-fg-stock') }}',
                type: 'POST',
                data: {
                    no_opname: currentNoOpname,
                    id_details: idDetails,
                    no_pallet_baru: noPalletBaru,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    ToastNotif.fire({
                        icon: 'success',
                        title: response.message,
                    });

                    activeCarton.no_pallet = response.no_pallet;
                    $('#modal_isi_item_pallet').text(response.no_pallet);
                    cancelEditPallet();

                    loadOpnameItems();
                },
                error: function(xhr) {
                    let message = 'Terjadi kesalahan!';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    ToastNotif.fire({
                        icon: 'error',
                        title: message,
                    });
                }
            });
        }

        $('#btn_edit_pallet').on('click', startEditPallet);
        $('#btn_cancel_edit_pallet').on('click', cancelEditPallet);
        $('#btn_save_pallet').on('click', savePallet);

        $('#modalIsiItem').on('hidden.bs.modal', function() {
            activeCarton = null;
            activeCartonStatus = null;
            loadOpnameItems();
        });

        function resetTambahItemForm() {
            $('#inp_buyer_ws').val(null).trigger('change');
            $('#inp_buyer').val('');
            $('#inp_ws').val('');
            $('#inp_style').val('').trigger('change');
            $('#inp_grade').val('').trigger('change');
            $('#inp_qty').val('');
            $('#inp_search_item').val('');
        }

        function renderModalItems() {
            $('#tabel_item_carton_body').empty();
            itemCounter = 0;
            updateTotalQty();

            let items = allItems.filter(i => i.no_carton === activeCarton.no_carton && i.no_pallet ===
                activeCarton.no_pallet);

            activeCartonStatus = items.length > 0 ? items[0].status : 'OPEN';
            activeCartonHasItems = items.length > 0;
            applyCartonLock();

            if (items.length === 0) {
                showEmptyRow();
                return;
            }

            items.forEach(function(item) {
                appendItemRow(item.id_detail, item.buyer, item.ws, item.styleno, item.dest, item.color,
                    item.size, item.grade, item.qty);
            });
        }

        function applyCartonLock() {
            let isClosed = currentStatus === 'CLOSED' || activeCartonStatus === 'CLOSED';

            if (isClosed) {
                $('#inp_buyer_ws, #inp_style, #inp_dest, #inp_color, #inp_size, #inp_grade, #inp_qty, .btn-add-item')
                    .prop('disabled', true);
            } else {
                $('#inp_buyer_ws, #inp_grade, #inp_qty, .btn-add-item').prop('disabled', false);
            }

            $('#tabel_item_carton_body .btn-outline-danger, #tabel_item_carton_body .btn-outline-primary')
                .prop('disabled', isClosed).toggle(!isClosed);

            $('#btn_finish_carton').toggle(!isClosed);
            $('#btn_edit_pallet').toggle(!isClosed && activeCartonHasItems);

            if (isClosed || !activeCartonHasItems) {
                cancelEditPallet();
            }

            let $status = $('#modal_isi_item_status');
            $status.removeClass('status-open status-closed').addClass(isClosed ? 'status-closed' :
                'status-open');
            $('#modal_isi_item_status_text').text(activeCartonStatus || 'OPEN');
        }

        function appendItemRow(idDetail, buyer, ws, styleno, dest, color, size, grade, qty) {
            $('#row_empty').remove();

            itemCounter++;
            let isClosed = currentStatus === 'CLOSED' || activeCartonStatus === 'CLOSED';
            let destLabel = dest ? dest : '-';
            let searchText = `${buyer} ${ws} ${styleno} ${dest} ${color} ${size} ${grade}`.toLowerCase();
            let row = `
                <tr data-id="${itemCounter}" data-detail-id="${idDetail}" data-qty="${qty}" data-grade="${grade}" data-search="${searchText}">
                    <td class="text-center">${itemCounter}</td>
                    <td class="text-center">${buyer}</td>
                    <td class="text-center">${ws}</td>
                    <td class="text-center">${styleno}</td>
                    <td class="text-center">${destLabel}</td>
                    <td class="text-center">${color}</td>
                    <td class="text-center">${size}</td>
                    <td class="text-center cell-grade">${grade}</td>
                    <td class="text-center"><span class="qty-pill">${qty}</span></td>
                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center">
                            <button type="button" class="btn btn-outline-primary btn-sm" ${isClosed ? 'style="display:none;"' : ''}
                                onclick="editItem(${itemCounter}, ${idDetail})">
                                <i class="fas fa-edit fa-sm"></i>
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm" ${isClosed ? 'style="display:none;"' : ''}
                                onclick="hapusItem(${itemCounter}, ${idDetail})">
                                <i class="fas fa-trash fa-sm"></i>
                            </button>
                        </div>
                    </td>
                </tr>`;
            $('#tabel_item_carton_body').append(row);
            updateTotalQty();
        }

        function updateTotalQty() {
            let total = 0;
            $('#tabel_item_carton_body tr[data-qty]').not('.d-none').each(function() {
                total += parseFloat($(this).data('qty')) || 0;
            });
            $('#total_qty_value').text(total);
        }

        $('#inp_search_item').on('keyup', function() {
            let keyword = $(this).val().toLowerCase().trim();

            $('#tabel_item_carton_body tr[data-search]').each(function() {
                let match = $(this).data('search').toString().includes(keyword);
                $(this).toggleClass('d-none', !match);
            });

            updateTotalQty();
        });


        function simpanCarton() {
            let noCarton = $('#modal_no_carton').val().replace(/\s+/g, '');

            if (noCarton === '') {
                Swal.fire({
                    title: 'No. Carton belum diisi!',
                    icon: 'warning',
                    showConfirmButton: true,
                });
                return;
            }

            $.ajax({
                type: 'POST',
                url: '{{ route('store-master-carton-opname-fg-stock') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    no_carton: noCarton,
                },
                success: function() {
                    $('#modal_no_carton').val('');
                    bootstrap.Modal.getInstance(document.getElementById('modalCarton')).hide();
                    loadMasterCarton(noCarton);
                },
                error: function(xhr) {
                    let message = xhr.responseJSON && xhr.responseJSON.message ?
                        xhr.responseJSON.message : 'Gagal menyimpan No. Carton!';
                    Swal.fire({
                        title: message,
                        icon: 'warning',
                        showConfirmButton: true,
                    });
                },
            });
        }

        $('#modal_pallet_zone, #modal_pallet_baris, #modal_pallet_kolom').on('input', function() {
            updatePalletPreview();
        });

        $('#modalPallet').on('show.bs.modal', function() {
            $('#modal_pallet_zone').val('');
            $('#modal_pallet_baris').val('');
            $('#modal_pallet_kolom').val('');
            updatePalletPreview();
        });

        function updatePalletPreview() {
            let zone = $('#modal_pallet_zone').val();
            let baris = $('#modal_pallet_baris').val();
            let kolom = $('#modal_pallet_kolom').val();

            $('#modal_pallet_preview').text((zone && baris && kolom) ? `${zone}.${baris}.${kolom}` :
                '-');
        }

        function simpanPallet() {
            let zone = $('#modal_pallet_zone').val();
            let baris = $('#modal_pallet_baris').val();
            let kolom = $('#modal_pallet_kolom').val();

            if (!zone || !baris || !kolom) {
                Swal.fire({
                    title: 'Lengkapi Zone, Baris, dan Kolom!',
                    icon: 'warning',
                    showConfirmButton: true,
                });
                return;
            }

            $.ajax({
                type: 'POST',
                url: '{{ route('store-master-pallet-opname-fg-stock') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    zone: zone,
                    baris: baris,
                    kolom: kolom,
                },
                success: function(response) {
                    bootstrap.Modal.getInstance(document.getElementById('modalPallet')).hide();
                    loadMasterPallet(response.no_pallet);
                },
                error: function(xhr) {
                    let message = xhr.responseJSON && xhr.responseJSON.message ?
                        xhr.responseJSON.message : 'Gagal menyimpan No. Pallet!';
                    Swal.fire({
                        title: message,
                        icon: 'warning',
                        showConfirmButton: true,
                    });
                },
            });
        }

        function tambahItem() {
            let buyer = $('#inp_buyer').val();
            let ws = $('#inp_ws').val();
            let styleno = $('#inp_style').val();
            let dest = $('#inp_dest').val();
            let color = $('#inp_color').val();
            let size = $('#inp_size').val();
            let grade = $('#inp_grade').val();
            let qty = $('#inp_qty').val().replace(/,/g, '');
            let idSoDet = sizeIdSoDetMap[size];
            let destValue = dest === '__EMPTY__' ? '' : dest;

            if (!currentNoOpname || !activeCarton) {
                Swal.fire({
                    title: 'Data opname / carton tidak ditemukan!',
                    icon: 'warning',
                    showConfirmButton: true,
                });
                return;
            }

            if (currentStatus === 'CLOSED' || activeCartonStatus === 'CLOSED') {
                Swal.fire({
                    title: 'Opname / carton sudah CLOSED, tidak bisa menambah item!',
                    icon: 'warning',
                    showConfirmButton: true,
                });
                return;
            }

            if (!buyer || !ws || !styleno || !dest || !color || !size || !grade || qty === '' || qty <=
                0) {
                Swal.fire({
                    title: 'Lengkapi data Buyer, WS, Style, Dest, Color, Size, Grade, dan Qty!',
                    icon: 'warning',
                    showConfirmButton: true,
                });
                return;
            }

            if (!idSoDet) {
                Swal.fire({
                    title: 'Data SO untuk Size ini tidak ditemukan!',
                    icon: 'warning',
                    showConfirmButton: true,
                });
                return;
            }

            let noCarton = activeCarton.no_carton;
            let noPallet = activeCarton.no_pallet;
            let $btnTambah = $('.btn-add-item').prop('disabled', true);

            $.ajax({
                type: 'POST',
                url: '{{ route('store-opname-fg-stock') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    no_opname: currentNoOpname,
                    no_carton: noCarton,
                    no_pallet: noPallet,
                    id_so_det: idSoDet,
                    qty: qty,
                    grade: grade,
                },
                success: function(response) {
                    allItems.push({
                        id_detail: response.id_detail,
                        no_carton: noCarton,
                        no_pallet: noPallet,
                        status: 'OPEN',
                        buyer: buyer,
                        ws: ws,
                        styleno: styleno,
                        dest: destValue,
                        color: color,
                        size: size,
                        grade: grade,
                        qty: qty,
                    });

                    appendItemRow(response.id_detail, buyer, ws, styleno, destValue, color, size,
                        grade, qty);
                    refreshCartonTable();

                    $('#inp_size').val('').trigger('change');
                    $('#inp_qty').val('').focus();
                },
                error: function(xhr) {
                    let message = xhr.responseJSON && xhr.responseJSON.message ?
                        xhr.responseJSON.message : 'Gagal menyimpan item!';
                    Swal.fire({
                        title: message,
                        icon: 'warning',
                        showConfirmButton: true,
                    });
                },
                complete: function() {
                    $btnTambah.prop('disabled', false);
                },
            });
        }

        function hapusItem(id, idDetail) {
            if (currentStatus === 'CLOSED' || activeCartonStatus === 'CLOSED') {
                Swal.fire({
                    title: 'Opname / carton sudah CLOSED, tidak bisa menghapus item!',
                    icon: 'warning',
                    showConfirmButton: true,
                });
                return;
            }

            $.ajax({
                type: 'POST',
                url: '{{ route('cancel-opname-item-fg-stock') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    id_detail: idDetail,
                },
                success: function() {
                    allItems = allItems.filter(i => i.id_detail != idDetail);
                    $(`tr[data-id="${id}"]`).remove();
                    if ($('#tabel_item_carton_body tr').length === 0) {
                        showEmptyRow();
                    }
                    updateTotalQty();
                    refreshCartonTable();
                },
                error: function() {
                    Swal.fire({
                        title: 'Gagal menghapus item!',
                        icon: 'warning',
                        showConfirmButton: true,
                    });
                },
            });
        }

        function editItem(id, idDetail) {
            if (currentStatus === 'CLOSED' || activeCartonStatus === 'CLOSED') {
                Swal.fire({
                    title: 'Opname / carton sudah CLOSED, tidak bisa mengubah item!',
                    icon: 'warning',
                    showConfirmButton: true,
                });
                return;
            }

            let $row = $(`tr[data-id="${id}"]`);
            let grade = $row.data('grade');
            let qty = $row.data('qty');

            $('#modal_update_id').val(idDetail);
            $('#modal_update_grade').val(grade).trigger('change');
            $('#modal_update_qty').val(qty);

            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalUpdateItem')).show();
        }

        function simpanUpdateItem() {
            let idDetail = $('#modal_update_id').val();
            let grade = $('#modal_update_grade').val();
            let qty = $('#modal_update_qty').val().replace(/,/g, '');

            if (!grade || qty === '' || qty <= 0) {
                Swal.fire({
                    title: 'Lengkapi Grade dan Qty!',
                    icon: 'warning',
                    showConfirmButton: true,
                });
                return;
            }

            $.ajax({
                type: 'POST',
                url: '{{ route('update-opname-item-fg-stock') }}',
                data: {
                    _token: '{{ csrf_token() }}',
                    id_detail: idDetail,
                    grade: grade,
                    qty: qty,
                },
                success: function() {
                    let $row = $(`tr[data-detail-id="${idDetail}"]`);
                    $row.attr('data-qty', qty).attr('data-grade', grade);
                    $row.data('qty', qty).data('grade', grade);
                    $row.find('.cell-grade').text(grade);
                    $row.find('.qty-pill').text(qty);

                    let buyer = $row.find('td').eq(1).text();
                    let ws = $row.find('td').eq(2).text();
                    let styleno = $row.find('td').eq(3).text();
                    let dest = $row.find('td').eq(4).text();
                    let color = $row.find('td').eq(5).text();
                    let size = $row.find('td').eq(6).text();
                    let newSearchText =
                        `${buyer} ${ws} ${styleno} ${dest} ${color} ${size} ${grade}`.toLowerCase();
                    $row.attr('data-search', newSearchText).data('search', newSearchText);

                    let item = allItems.find(i => i.id_detail == idDetail);
                    if (item) {
                        item.qty = qty;
                        item.grade = grade;
                    }

                    updateTotalQty();
                    refreshCartonTable();
                    bootstrap.Modal.getInstance(document.getElementById('modalUpdateItem')).hide();
                },
                error: function(xhr) {
                    let message = xhr.responseJSON && xhr.responseJSON.message ?
                        xhr.responseJSON.message : 'Gagal mengupdate item!';
                    Swal.fire({
                        title: message,
                        icon: 'warning',
                        showConfirmButton: true,
                    });
                },
            });
        }

        function showEmptyRow() {
            $('#tabel_item_carton_body').append(`
                    <tr id="row_empty">
                        <td colspan="10">
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                Belum ada item ditambahkan
                            </div>
                        </td>
                    </tr>`);
        }

        function finishOpname() {
            if (!currentNoOpname || currentStatus === 'CLOSED') {
                return;
            }

            if (allItems.length === 0) {
                Swal.fire({
                    title: 'Tambahkan minimal 1 item sebelum finish!',
                    icon: 'warning',
                    showConfirmButton: true,
                });
                return;
            }

            Swal.fire({
                title: 'Selesaikan Opname?',
                text: 'Opname ' + currentNoOpname + ' akan diselesaikan.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Finish',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    type: 'POST',
                    url: '{{ route('finish-opname-fg-stock') }}',
                    data: {
                        _token: '{{ csrf_token() }}',
                        no_opname: currentNoOpname,
                    },
                    success: function() {
                        loadOpnameHeader();
                        loadOpnameItems();
                        Swal.fire({
                            title: 'Opname berhasil diselesaikan.',
                            icon: 'success',
                            showConfirmButton: true,
                        });
                    },
                    error: function(xhr) {
                        let message = xhr.responseJSON && xhr.responseJSON.message ?
                            xhr.responseJSON.message : 'Gagal menyelesaikan opname!';
                        Swal.fire({
                            title: message,
                            icon: 'warning',
                            showConfirmButton: true,
                        });
                    },
                });
            });
        }

        function buildPrintQrUrl(noCarton) {
            return '{{ route('print-qr-opname-fg-stock') }}?no_carton=' + encodeURIComponent(noCarton) +
                '&no_opname=' + encodeURIComponent(currentNoOpname);
        }

        const ToastNotif = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true,
        });

        function closeCartonModal(noCarton, noPallet) {
            if (!canChangeCartonStatus) {
                ToastNotif.fire({
                    icon: 'error',
                    title: 'Anda tidak memiliki izin untuk mengubah status carton.',
                });
                return;
            }

            closeCarton(noCarton, noPallet);
        }

        function closeCarton(noCarton, noPallet) {
            $.ajax({
                url: '{{ route('change-carton-status-opname-fg-stock') }}',
                type: 'POST',
                data: {
                    no_opname: currentNoOpname,
                    no_carton: noCarton,
                    no_pallet: noPallet,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    ToastNotif.fire({
                        icon: 'success',
                        title: response.message,
                    });
                    loadOpnameHeader();
                    loadOpnameItems();
                },
                error: function(xhr) {
                    let message = 'Terjadi kesalahan!';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    ToastNotif.fire({
                        icon: 'error',
                        title: message,
                    });
                }
            });
        }

        function reopenCartonModal(noCarton, noPallet) {
            if (!canChangeCartonStatus) {
                ToastNotif.fire({
                    icon: 'error',
                    title: 'Anda tidak memiliki izin untuk mengubah status carton.',
                });
                return;
            }

            reopenCarton(noCarton, noPallet);
        }

        function reopenCarton(noCarton, noPallet) {
            $.ajax({
                url: '{{ route('reopen-carton-opname-fg-stock') }}',
                type: 'POST',
                data: {
                    no_opname: currentNoOpname,
                    no_carton: noCarton,
                    no_pallet: noPallet,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    ToastNotif.fire({
                        icon: 'success',
                        title: response.message,
                    });
                    loadOpnameHeader();
                    loadOpnameItems();
                },
                error: function(xhr) {
                    let message = 'Terjadi kesalahan!';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    ToastNotif.fire({
                        icon: 'error',
                        title: message,
                    });
                }
            });
        }

        function printQr(noCarton) {
            window.open(buildPrintQrUrl(noCarton), '_blank');
        }

        function finishCarton() {
            if (!currentNoOpname || !activeCarton) {
                return;
            }

            if (currentStatus === 'CLOSED' || activeCartonStatus === 'CLOSED') {
                return;
            }

            let items = allItems.filter(i => i.no_carton === activeCarton.no_carton && i.no_pallet ===
                activeCarton.no_pallet);

            if (items.length === 0) {
                Swal.fire({
                    title: 'Tambahkan minimal 1 item sebelum finish!',
                    icon: 'warning',
                    showConfirmButton: true,
                });
                return;
            }

            let noCarton = activeCarton.no_carton;
            let noPallet = activeCarton.no_pallet;

            Swal.fire({
                title: 'Selesaikan Carton Ini?',
                text: 'No. Carton ' + noCarton + ' akan diselesaikan dan QR akan dicetak.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Finish',
                cancelButtonText: 'Batal',
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                // Buka tab kosong dulu selagi masih dalam konteks klik user,
                // supaya tidak ke-block popup blocker browser (window.open yang
                // dipanggil setelah ajax selesai dianggap bukan aksi user langsung).
                let printWindow = window.open('', '_blank');

                $.ajax({
                    type: 'POST',
                    url: '{{ route('finish-carton-opname-fg-stock') }}',
                    data: {
                        _token: '{{ csrf_token() }}',
                        no_opname: currentNoOpname,
                        no_carton: noCarton,
                        no_pallet: noPallet,
                    },
                    success: function() {
                        if (printWindow) {
                            printWindow.location.href = buildPrintQrUrl(noCarton);
                        }

                        loadOpnameItems();
                    },
                    error: function(xhr) {
                        if (printWindow) {
                            printWindow.close();
                        }

                        let message = xhr.responseJSON && xhr.responseJSON.message ?
                            xhr.responseJSON.message : 'Gagal menyelesaikan carton!';
                        Swal.fire({
                            title: message,
                            icon: 'warning',
                            showConfirmButton: true,
                        });
                    },
                });
            });
        }
    </script>
@endsection
