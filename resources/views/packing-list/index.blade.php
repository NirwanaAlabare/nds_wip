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

        .pl-chips { display: flex; flex-wrap: wrap; gap: 6px; }
        .pl-chip { border: 1px solid #dfe3ea; background: #fff; border-radius: 999px; padding: 4px 11px; font-size: 12px; font-weight: 800; color: #09244c; }
        .pl-chip:hover { border-color: #b9c2d0; }
        .pl-chip.on { background: #09244c; border-color: #09244c; color: #fff; }
        .pl-chip .n { opacity: .55; margin-left: 3px; }

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

        /* Tombol Panduan di header: hover tetap terang, bukan hitam (timpa style.css global) */
        .pl-head-r .btn-light:hover,
        .pl-head-r .btn-light:focus-visible {
            background-color: #e2e6ea !important;
            border-color: #e2e6ea !important;
            color: #1d2433 !important;
        }

        /* ===== Panduan ===== */
        #plGuide, .pl-spot-l {
            --g-ink: #1d2433; --g-muted: #6b7385; --g-line: #ebe7de; --g-soft: #f7f5f0;
            --g-pri: #4057e3; --g-ok: #1a9d63; --g-wip: #e39a12; --g-err: #e0483e; --g-empty: #e4e1d8; --g-acc: #ff971f;
        }
        .pl-g { padding: 0 20px 20px; color: var(--g-ink); font-size: 13.5px; }
        .pl-g-nav { position: sticky; top: 0; z-index: 2; display: flex; flex-wrap: wrap; gap: 6px; padding: 12px 0; background: #fff; border-bottom: 1px solid var(--g-line); margin-bottom: 4px; }
        .pl-g-nav a { font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px; background: var(--g-soft); color: var(--g-pri); text-decoration: none; border: 1px solid var(--g-line); }
        .pl-g-nav a:hover { background: var(--g-pri); color: #fff; }
        .pl-g section { padding-top: 16px; scroll-margin-top: 56px; }
        .pl-g h6 { display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap; font-weight: 700; color: var(--g-pri); border-left: 4px solid var(--g-acc); padding-left: 8px; margin-bottom: 10px; }
        .pl-g ul { padding-left: 18px; margin-bottom: 0; }
        .pl-g li { margin-bottom: 6px; line-height: 1.5; }
        .pl-g-t { margin-bottom: 0; }
        .pl-g-t td { vertical-align: top; padding: 7px 8px; line-height: 1.5; }
        .pl-g-t td:first-child { width: 210px; white-space: nowrap; color: var(--g-pri); }
        .pl-g-btn { display: inline-block; font-size: 12px; padding: 2px 8px; border: 1px solid #c5ccd6; border-radius: .3rem; background: #fff; }
        .pl-g-ic { display: inline-grid; place-items: center; width: 26px; height: 22px; border-radius: .25rem; color: #fff; font-size: 11px; }
        .pl-g-flow { display: flex; align-items: stretch; gap: 8px; margin-bottom: 12px; flex-wrap: wrap; }
        .pl-g-flow > div { flex: 1; min-width: 130px; background: var(--g-soft); border: 1px solid var(--g-line); border-radius: .5rem; padding: 8px 12px; }
        .pl-g-flow > div.ok { background: #e7f7ee; border-color: #b9e8cf; }
        .pl-g-flow b { display: block; color: var(--g-pri); } .pl-g-flow span { font-size: 12px; color: var(--g-muted); }
        .pl-g-flow > i { align-self: center; font-style: normal; font-weight: 700; color: var(--g-acc); }
        .pl-g-faq dt { color: var(--g-pri); margin-top: 8px; } .pl-g-faq dd { margin: 2px 0 0; }
        .pl-g-show { border: 1px solid var(--g-pri); background: #fff; color: var(--g-pri); font-size: 11.5px; font-weight: 600; border-radius: 20px; padding: 2px 10px; }
        .pl-g-show:hover, .pl-g-show.pri { background: var(--g-pri); color: #fff; }
        .pl-g-show.pri:hover { filter: brightness(.92); }
        .pl-g-note { font-size: 12.5px; color: var(--g-muted); margin-bottom: 10px; }

        /* Panduan: peta halaman */
        .pl-map { background: var(--g-soft); border: 1px solid var(--g-line); border-radius: .6rem; padding: 14px; max-width: 760px; }
        .pl-map-z { position: relative; cursor: pointer; transition: box-shadow .15s; }
        .pl-map-z:hover { box-shadow: 0 0 0 2px var(--g-acc); z-index: 6; }
        .pl-map-n { position: absolute; top: -9px; left: -9px; z-index: 2; width: 22px; height: 22px; border-radius: 50%; background: var(--g-acc); color: #fff !important; font-size: 12px; font-weight: 700; display: grid; place-items: center; text-decoration: none; box-shadow: 0 0 0 2px #fff; }
        .pl-map-n.sm { width: 18px; height: 18px; font-size: 10.5px; top: -7px; left: -7px; }
        .pl-map-n:hover, .pl-map-n:focus-visible { background: var(--g-pri); z-index: 5; }
        .pl-map-tip { position: absolute; top: calc(100% + 8px); left: -4px; width: 230px; padding: 8px 10px; border-radius: .45rem; background: #1d2433; color: #fff; font-size: 12px; font-weight: 400; line-height: 1.45; text-align: left; box-shadow: 0 6px 18px rgba(0,0,0,.25); opacity: 0; visibility: hidden; transform: translateY(-4px); transition: opacity .15s, transform .15s, visibility .15s; pointer-events: none; }
        .pl-map-tip:before { content: ""; position: absolute; top: -5px; left: 10px; width: 10px; height: 10px; background: #1d2433; transform: rotate(45deg); }
        .pl-map-n.tip-l .pl-map-tip { left: auto; right: -4px; }
        .pl-map-n.tip-l .pl-map-tip:before { left: auto; right: 10px; }
        .pl-map-tip b { display: block; font-size: 12.5px; margin-bottom: 2px; color: #ffc98a; }
        .pl-map-tip small { display: block; margin-top: 5px; font-size: 10.5px; color: rgba(255,255,255,.65); }
        .pl-map-n:hover .pl-map-tip, .pl-map-n:focus-visible .pl-map-tip { opacity: 1; visibility: visible; transform: none; }
        .pl-map-head { display: flex; justify-content: space-between; align-items: center; gap: 10px; padding: 6px 8px 6px 10px; border-radius: .4rem .4rem 0 0; background: var(--sb-color, #082149); }
        .pl-map-head b { font-size: 12px; color: #fff; }
        .pl-map-head .r { display: flex; gap: 4px; padding: 3px; border-radius: .35rem; }
        .pl-map-head .r i { width: 46px; height: 14px; border-radius: 3px; background: #fff; }
        .pl-map-body { background: #fff; border: 1px solid var(--g-line); border-top: 0; border-radius: 0 0 .45rem .45rem; padding: 12px 10px; display: flex; flex-direction: column; gap: 12px; }
        .pl-map-new { width: 54px; height: 16px; border-radius: .25rem; border: 1.5px solid var(--g-pri); }
        .pl-map-fl { display: flex; gap: 6px; width: max-content; border-radius: .3rem; }
        .pl-map-fl i { width: 80px; height: 14px; border-radius: .25rem; border: 1px solid #c5ccd6; }
        .pl-map-tb { display: grid; grid-template-columns: 50px repeat(9, 1fr) 70px; gap: 3px; border-radius: .3rem; }
        .pl-map-tb i { height: 12px; border-radius: .15rem; background: var(--g-soft); border: 1px solid var(--g-line); }
        .pl-map-tb i.th { background: #cfe2ff; border-color: #b6d4fe; }
        .pl-map-tb i.f { background: #fff; border-color: #c5ccd6; }
        .pl-map-tb i.ok { background: #dff4e7; border-color: #b9e8cf; }
        .pl-map-tb i.wip { background: #fde8c4; border-color: #f5d49b; }
        .pl-map-tb i.pd { background: #e2e8f0; border-color: #cbd5e1; }
        .pl-map-tb i.ac { background: linear-gradient(90deg, #0dcaf0 0 18%, transparent 18% 22%, #0d6efd 22% 40%, transparent 40% 44%, #dc3545 44% 62%, transparent 62% 66%, #0d6efd 66% 80%, transparent 80% 82%, #198754 82%); border: 0; }
        .pl-map-tbw { position: relative; }
        .pl-map-acz { position: absolute; right: 0; top: 30px; bottom: 0; width: 70px; }

        /* Panduan: sorot elemen & tur */
        .pl-spot { position: fixed; z-index: 2000; border-radius: .6rem; box-shadow: 0 0 0 9999px rgba(17,24,39,.6); outline: 3px solid #ff971f; outline-offset: 3px; pointer-events: none; animation: plSpot 1.2s ease-in-out infinite; }
        @keyframes plSpot { 50% { outline-offset: 8px; outline-color: rgba(255,151,31,.6); } }
        .pl-spot-cv { position: fixed; inset: 0; z-index: 1999; cursor: pointer; }
        .pl-spot-l { position: fixed; z-index: 2001; background: #fff; color: var(--g-ink); font-size: 13px; padding: 10px 12px; border-radius: .5rem; box-shadow: 0 6px 18px rgba(0,0,0,.25); width: 340px; max-width: calc(100vw - 16px); }
        .pl-spot-l .st { display: flex; justify-content: space-between; align-items: center; font-size: 10.5px; font-weight: 700; letter-spacing: .6px; text-transform: uppercase; color: var(--g-pri); margin-bottom: 4px; }
        .pl-spot-l .dots { display: flex; gap: 4px; }
        .pl-spot-l .dots i { width: 7px; height: 7px; border-radius: 50%; background: #e2e6ec; }
        .pl-spot-l .dots i.done { background: #b7c2f5; }
        .pl-spot-l .dots i.on { background: var(--g-acc); width: 16px; border-radius: 4px; }
        .pl-spot-l > b { display: block; font-weight: 700; }
        .pl-spot-l > span.d { display: block; color: #3d4450; font-size: 12.5px; line-height: 1.5; margin-top: 3px; }
        .pl-spot-l > span.d b { color: var(--g-ink); }
        .pl-spot-l .nav { display: flex; justify-content: space-between; gap: 8px; margin-top: 10px; }
        .pl-spot-l .nav button { border: 1px solid #c5ccd6; background: #fff; color: var(--g-ink); font-size: 12.5px; font-weight: 600; border-radius: .35rem; padding: 4px 12px; }
        .pl-spot-l .nav button:disabled { opacity: .4; }
        .pl-spot-l .nav button.pri { background: var(--g-pri); border-color: var(--g-pri); color: #fff; }
        .pl-spot-l .nav button:not(:disabled):hover { filter: brightness(.95); }
        .pl-spot-l small { display: block; color: var(--g-muted); font-size: 11.5px; margin-top: 6px; padding-top: 5px; border-top: 1px solid #e2e6ec; }
        .pl-spot-l kbd { font-size: 10px; padding: 0 4px; }

        @media (max-width: 576px) {
            .pl-g-t td:first-child { width: auto; white-space: normal; }
            .pl-g-flow > i { display: none; }
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

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                        <div class="d-flex align-items-center flex-wrap gap-2">
                            <span class="fw-bold" style="font-size: 13px; color: #09244c;"><i class="fas fa-boxes"></i> Grid
                                Karton Order:</span>
                            <div class="pl-chips" id="cartonFilters"></div>
                        </div>
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
        <div class="card-header d-flex align-items-center">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-box-open"></i> Packing List</h5>
            <div class="pl-head-r ms-auto" style="margin-block: -.5rem;">
                <button type="button" class="btn btn-light btn-sm" id="guideBtn">
                    <i class="fas fa-book-open"></i> Panduan
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-end gap-3 mb-3" id="plFilter">
                <div>
                    <label class="form-label"><small><b>Tgl Shipment Awal</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-awal" name="tgl_awal" autocomplete="off"
                        value="{{ $tgl_awal_fix }}">
                </div>
                <div>
                    <label class="form-label"><small><b>Tgl Shipment Akhir</b></small></label>
                    <input type="date" class="form-control form-control-sm" id="tgl-akhir" name="tgl_akhir" autocomplete="off"
                        value="{{ $tgl_akhir_fix }}">
                </div>
                <div>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="dataTableReload()">
                        <i class="fas fa-search"></i> Cari
                    </button>
                </div>
                <div class="ms-auto">
                    <a class="btn btn-outline-primary btn-sm" id="btn-new-packing-list"
                        href="{{ route('create-packing-list-karton') }}">
                        <i class="fas fa-plus"></i> Tambah
                    </a>
                </div>
            </div>
            <div class="table-responsive" id="plTable">
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

    {{-- Panduan halaman --}}
    <div class="modal fade" id="plGuide" tabindex="-1" aria-labelledby="plGuideTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <div>
                        <small class="text-uppercase fw-bold" style="letter-spacing: .6px; opacity: .75;">Panduan</small>
                        <h5 class="modal-title fw-bold" id="plGuideTitle">Packing List</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body pl-g">
                    <nav class="pl-g-nav">
                        <a href="#plg-peta">Peta halaman</a><a href="#plg-alur">Alur singkat</a><a href="#plg-baru">Tombol Baru</a><a href="#plg-filter">Filter tanggal</a>
                        <a href="#plg-tabel">Tabel &amp; status</a><a href="#plg-aksi">Tombol aksi</a><a href="#plg-tips">Tips &amp; FAQ</a>
                    </nav>

                    <section id="plg-peta">
                        <h6><span>Peta halaman</span> <button type="button" class="pl-g-show pri" data-tour="0"><i class="fa fa-play"></i> Mulai tur</button></h6>
                        <p class="pl-g-note">Klik <b>nomor</b> untuk membaca penjelasannya, atau klik <b>kotaknya</b> untuk melihat langsung di halaman.</p>
                        <div class="pl-map">
                            <div class="pl-map-head">
                                <b>Packing List</b>
                                <div class="pl-map-z r" data-spot=".pl-head-r"><a href="#plg-peta" class="pl-map-n tip-l" aria-label="Tombol Panduan">1<span class="pl-map-tip" role="tooltip"><b>Panduan</b>Membuka halaman bantuan ini kapan saja.<small>Klik kotak untuk melihat di halaman</small></span></a><i></i></div>
                            </div>
                            <div class="pl-map-body">
                                <div class="pl-map-z pl-map-new" data-spot="#btn-new-packing-list"><a href="#plg-baru" class="pl-map-n sm" aria-label="Tombol Baru">2<span class="pl-map-tip" role="tooltip"><b>Baru</b>Membuat packing list baru untuk satu PO.<small>Klik nomor untuk penjelasan lengkap</small></span></a></div>
                                <div class="pl-map-z pl-map-fl" data-spot="#plFilter"><a href="#plg-filter" class="pl-map-n sm" aria-label="Filter tanggal">3<span class="pl-map-tip" role="tooltip"><b>Filter Tgl Shipment</b>Menampilkan PO dengan tanggal shipment di rentang ini.<small>Klik nomor untuk penjelasan lengkap</small></span></a><i></i><i></i></div>
                                <div class="pl-map-tbw">
                                    <div class="pl-map-z pl-map-tb" data-spot="#plTable">
                                        <a href="#plg-tabel" class="pl-map-n sm" aria-label="Tabel">4<span class="pl-map-tip" role="tooltip"><b>Tabel packing list</b>Satu baris per PO + Dest, dengan status, jumlah carton dan qty.<small>Klik nomor untuk penjelasan lengkap</small></span></a>
                                        <i class="th"></i><i class="th"></i><i class="th"></i><i class="th"></i><i class="th"></i><i class="th"></i><i class="th"></i><i class="th"></i><i class="th"></i><i class="th"></i><i class="th"></i>
                                        <i class="f"></i><i class="f"></i><i class="f"></i><i class="f"></i><i class="f"></i><i class="f"></i><i class="f"></i><i class="f"></i><i class="f"></i><i class="f"></i><i></i>
                                        <i class="ok"></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i class="ac"></i>
                                        <i class="wip"></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i class="ac"></i>
                                        <i class="pd"></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i class="ac"></i>
                                    </div>
                                    <div class="pl-map-z pl-map-acz" data-spot="#datatable tbody tr:first-child td:last-child"><a href="#plg-aksi" class="pl-map-n sm tip-l" aria-label="Tombol aksi">5<span class="pl-map-tip" role="tooltip"><b>Tombol aksi</b>Preview, Detail, Hapus karton, Tambah, dan Export Excel.<small>Klik nomor untuk penjelasan lengkap</small></span></a></div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section id="plg-alur">
                        <h6>Alur singkat</h6>
                        <div class="pl-g-flow">
                            <div><b>Baru</b><span>buat packing list per PO</span></div><i>→</i>
                            <div><b>Tampil di tabel</b><span>status PENDING</span></div><i>→</i>
                            <div><b>Scan Packing Out</b><span>karton mulai terisi → PROSES</span></div><i>→</i>
                            <div class="ok"><b>FULL</b><span>semua karton terisi penuh</span></div>
                        </div>
                        <ul>
                            <li>Pantau pemenuhan karton lewat tombol <b>Preview</b> di kolom Action.</li>
                            <li>Kalau qty PO belum semuanya masuk packing list, tombol <b>Tambah</b> muncul untuk menambah carton.</li>
                        </ul>
                    </section>

                    <section id="plg-baru">
                        <h6><span>Tombol Baru</span> <button type="button" class="pl-g-show" data-spot="#btn-new-packing-list"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                        <p class="mb-0">Membuka halaman <b>Packing List — Baru</b>: pilih PO, tentukan nomor carton, isi qty per size, lalu simpan. Panduan lengkapnya ada di halaman tersebut.</p>
                    </section>

                    <section id="plg-filter">
                        <h6><span>Filter Tgl Shipment</span> <button type="button" class="pl-g-show" data-spot="#plFilter"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                        <table class="table table-sm pl-g-t">
                            <tr><td><b>Tgl Shipment Awal / Akhir</b></td><td>Tabel hanya menampilkan PO dengan tanggal shipment di rentang ini. Tabel langsung dimuat ulang setiap tanggal diganti.</td></tr>
                        </table>
                        <ul class="mt-2">
                            <li>Memuat data bisa butuh beberapa detik; tunggu sampai tulisan <b>Loading…</b> hilang.</li>
                        </ul>
                    </section>

                    <section id="plg-tabel">
                        <h6><span>Tabel &amp; status</span> <button type="button" class="pl-g-show" data-spot="#plTable"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                        <table class="table table-sm pl-g-t">
                            <tr><td><span class="badge-status badge-status-pending">PENDING</span></td><td>Belum ada karton yang di-scan di Packing Out.</td></tr>
                            <tr><td><span class="badge-status badge-status-partial">PROSES</span></td><td>Sudah ada scan, tapi belum semua karton full.</td></tr>
                            <tr><td><span class="badge-status badge-status-full">FULL</span></td><td>Semua karton sudah terisi penuh.</td></tr>
                            <tr><td><b>Tot. Carton</b></td><td>Karton full / total karton, misal <b>3/10</b>.</td></tr>
                            <tr><td><b>Tot. Qty</b></td><td>Qty sudah terisi (scan) / total qty di packing list.</td></tr>
                            <tr><td><b>Kotak pencarian</b></td><td>Baris kedua di judul tabel: ketik untuk menyaring per kolom (misal PO atau Buyer).</td></tr>
                        </table>
                    </section>

                    <section id="plg-aksi">
                        <h6><span>Tombol aksi</span> <button type="button" class="pl-g-show" data-spot="#datatable tbody tr:first-child td:last-child"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                        <table class="table table-sm pl-g-t">
                            <tr><td><span class="pl-g-ic bg-info"><i class="fas fa-eye"></i></span> Preview</td><td>Visualisasi pemenuhan karton: kotak per karton berwarna sesuai status, plus persentase karton &amp; qty. Klik satu kotak untuk melihat rincian isi dan kekurangannya.</td></tr>
                            <tr><td><span class="pl-g-ic bg-primary"><i class="fas fa-search"></i></span> Detail</td><td>Daftar lengkap per barcode / carton beserta Qty, Qty Scan, Qty FG In &amp; Out.</td></tr>
                            <tr><td><span class="pl-g-ic bg-danger"><i class="fas fa-trash"></i></span> Hapus Karton</td><td>Centang baris karton yang ingin dihapus, lalu klik <b>Hapus</b>.</td></tr>
                            <tr><td><span class="pl-g-ic bg-primary"><i class="fas fa-plus"></i></span> Tambah</td><td>Hanya muncul bila masih ada qty PO yang belum masuk packing list. Membuka form untuk menambah carton ke PO + Dest ini.</td></tr>
                            <tr><td><span class="pl-g-ic bg-success"><i class="fas fa-file-excel"></i></span> Export</td><td>Unduh packing list sebagai Excel (.xlsx) atau CSV.</td></tr>
                        </table>
                    </section>

                    <section id="plg-tips">
                        <h6>Tips &amp; FAQ</h6>
                        <dl class="pl-g-faq mb-0">
                            <dt>PO yang dicari tidak muncul?</dt>
                            <dd>Cek rentang Tgl Shipment. Perlebar rentangnya, atau gunakan kotak pencarian kolom PO.</dd>
                            <dt>Tombol Tambah tidak ada?</dt>
                            <dd>Seluruh qty PO sudah masuk packing list, jadi tidak ada sisa untuk ditambahkan.</dd>
                            <dt>Salah input carton?</dt>
                            <dd>Hapus karton yang salah lewat tombol <b>Hapus Karton</b>, lalu tambahkan lagi lewat tombol <b>Tambah</b>.</dd>
                        </dl>
                    </section>
                </div>
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

        // Paksa tanggal kembali ke default server saat reload (cegah browser restore isian lama)
        $('#tgl-awal').val('{{ $tgl_awal_fix }}');
        $('#tgl-akhir').val('{{ $tgl_akhir_fix }}');

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
            cartonFilter = 'all';
            $('#cartonFilters').empty();
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
                        <div class="carton-box ${c.status === 'full' ? '' : c.status}" data-status="${c.status}" title="Detail CTN #${escHtml(c.no_carton)}"
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
                    render_carton_filters();

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

        // filter grid karton: Semua / Pending / Proses / Full
        let cartonFilter = 'all';

        function render_carton_filters() {
            let cnt = s => previewCartons.filter(c => c.status === s).length;
            let fl = [['all', 'Semua', previewCartons.length], ['empty', 'Pending', cnt('empty')],
                ['partial', 'Proses', cnt('partial')], ['full', 'Full', cnt('full')]];
            $('#cartonFilters').html(fl.map(([k, l, n]) =>
                `<button type="button" class="pl-chip ${cartonFilter === k ? 'on' : ''}" onclick="set_carton_filter('${k}')">${l}<span class="n">${n.toLocaleString()}</span></button>`
            ).join(''));
            $('#cartonGrid .carton-box').each(function() {
                $(this).toggle(cartonFilter === 'all' || $(this).data('status') === cartonFilter);
            });
        }

        function set_carton_filter(k) {
            cartonFilter = k;
            render_carton_filters();
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

                // Qty FG IN global per id_so_det, jadi dijumlah sekali per id_so_det (bukan per karton)
                var fgInPerSoDet = {};
                api.rows().data().each(function(r) {
                    fgInPerSoDet[r.id_so_det] = intVal(r.qty_fg_in);
                });
                var sumTotalT = Object.values(fgInPerSoDet).reduce(function(a, b) {
                    return a + b;
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
                    // digrup per id_so_det (+ qty) seperti packing list lama
                    data: 'fg_in_key',
                    render: function(data, type, row) {
                        return row.qty_fg_in;
                    }
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
                11,
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

        // ===== Panduan: sorot elemen asli + tur langkah demi langkah =====
        const gEl = document.getElementById('plGuide'), gBs = bootstrap.Modal.getOrCreateInstance(gEl);
        const TOUR_KEY = 'pl_index_tour_seen_{{ Auth::id() }}';
        const STEPS = [
            { sel: '.pl-head-r', sec: 'plg-peta', lbl: 'Panduan',
              d: 'Tombol <b>Panduan</b> membuka halaman bantuan ini kapan saja.' },
            { sel: '#btn-new-packing-list', sec: 'plg-baru', lbl: 'Buat packing list baru',
              d: 'Klik <b>Baru</b> untuk membuat packing list satu PO: tentukan carton, isi qty per size, lalu simpan.' },
            { sel: '#plFilter', sec: 'plg-filter', lbl: 'Filter Tgl Shipment',
              d: 'Tabel hanya menampilkan PO dengan tanggal shipment di rentang ini. Ganti tanggal → tabel dimuat ulang.' },
            { sel: '#plTable', sec: 'plg-tabel', lbl: 'Tabel packing list',
              d: 'Status <b>PENDING</b> belum di-scan · <b>PROSES</b> sebagian · <b>FULL</b> semua karton penuh. Tot. Carton &amp; Tot. Qty = terisi / total.' },
            { sel: '#datatable tbody tr:first-child td:last-child', fb: '#plTable', sec: 'plg-aksi', lbl: 'Tombol aksi',
              d: '<b>Preview</b> pemenuhan karton · <b>Detail</b> · <b>Hapus</b> karton · <b>Tambah</b> carton (bila masih ada sisa qty) · <b>Export</b> Excel/CSV.' },
        ];
        let spotTok = 0, spotIdx = -1, tourFirst = false;

        function gGo(id, smooth) {
            const t = gEl.querySelector(id);
            if (t) t.scrollIntoView({ behavior: smooth ? 'smooth' : 'auto', block: 'start' });
        }

        function spotClear() {
            spotTok++;
            document.querySelectorAll('.pl-spot, .pl-spot-l, .pl-spot-cv').forEach(x => x.remove());
            removeEventListener('scroll', spotOff, true);
            removeEventListener('resize', spotOff);
        }

        // Tutup tur & kembali ke panduan di bagian langkah terakhir yang dilihat
        function spotOff() {
            const s = STEPS[spotIdx], first = tourFirst;
            spotClear();
            spotIdx = -1;
            if (first) {
                tourFirst = false;
                try { localStorage.setItem(TOUR_KEY, '1'); } catch (e) {}
            }
            gEl.addEventListener('shown.bs.modal', () => gGo(first || !s ? '#plg-peta' : '#' + s.sec), { once: true });
            gBs.show();
        }

        function visibleEl(sel) {
            return sel ? [...document.querySelectorAll(sel)].find(x => x.offsetParent !== null && !x.classList.contains('dataTables_empty')) : null;
        }

        function spotGo(i) {
            if (i < 0 || i >= STEPS.length) return;
            spotClear();
            spotIdx = i;

            const s = STEPS[i], n = STEPS.length;
            // tabel kosong: sorot tabelnya saja
            const target = visibleEl(s.sel) || visibleEl(s.fb);
            if (!target) { spotOff(); return; }

            const tok = spotTok;
            if (document.activeElement) document.activeElement.blur();
            target.scrollIntoView({ behavior: 'smooth', block: 'center' });

            // Tunggu scroll berhenti (posisi elemen stabil beberapa frame), baru pasang sorotan
            let last = null, still = 0;
            const t0 = performance.now();
            (function wait() {
                if (tok !== spotTok) return;
                const top = target.getBoundingClientRect().top;
                still = last !== null && Math.abs(top - last) < 0.5 ? still + 1 : 0;
                last = top;
                if (still >= 6 || performance.now() - t0 > 2000) draw(); else requestAnimationFrame(wait);
            })();

            function draw() {
                const r = target.getBoundingClientRect(), pad = 6;

                const cv = document.createElement('div');
                cv.className = 'pl-spot-cv';
                cv.onclick = spotOff;

                const box = document.createElement('div');
                box.className = 'pl-spot';
                Object.assign(box.style, { left: r.left - pad + 'px', top: r.top - pad + 'px', width: r.width + pad * 2 + 'px', height: r.height + pad * 2 + 'px' });

                const l = document.createElement('div');
                l.className = 'pl-spot-l';
                l.innerHTML = `<div class="st"><span>Langkah ${i + 1} dari ${n}</span><span class="dots">${STEPS.map((_, k) => `<i class="${k === i ? 'on' : k < i ? 'done' : ''}"></i>`).join('')}</span></div>
                    <b>${escHtml(s.lbl)}</b><span class="d">${s.d}</span>
                    <div class="nav"><button type="button" data-nav="prev" ${i ? '' : 'disabled'}>‹ Sebelumnya</button>
                        <button type="button" data-nav="next" class="pri">${i === n - 1 ? 'Selesai ✓' : 'Berikutnya ›'}</button></div>
                    <small><kbd>←</kbd><kbd>→</kbd> pindah langkah · <kbd>Esc</kbd>, scroll, atau klik area gelap untuk kembali ke panduan</small>`;
                l.onclick = e => {
                    const b = e.target.closest('[data-nav]');
                    if (!b) return;
                    if (b.dataset.nav === 'prev') spotGo(i - 1);
                    else if (i === n - 1) spotOff();
                    else spotGo(i + 1);
                };

                document.body.append(cv, box, l);

                const lh = l.offsetHeight, below = r.bottom + 16 + lh < innerHeight;
                l.style.left = Math.max(8, Math.min(innerWidth - l.offsetWidth - 8, r.left)) + 'px';
                l.style.top = (below ? r.bottom + 16 : Math.max(8, r.top - lh - 16)) + 'px';

                const nx = l.querySelector('[data-nav="next"]');
                if (nx) nx.focus({ preventScroll: true });

                // Mulai dengarkan scroll user setelah sorotan tampil
                setTimeout(() => {
                    if (tok !== spotTok || !document.querySelector('.pl-spot')) return;
                    addEventListener('scroll', spotOff, true);
                    addEventListener('resize', spotOff);
                }, 250);
            }
        }

        document.getElementById('guideBtn').addEventListener('click', e => {
            e.stopPropagation();
            gBs.show();
        });

        // Klik di dalam panduan: nomor/nav → lompat ke bagian; kotak/tombol "Tunjukkan" → sorot di halaman
        gEl.addEventListener('click', e => {
            const a = e.target.closest('.pl-map-n, .pl-g-nav a');
            if (a) {
                e.preventDefault();
                e.stopPropagation();
                gGo(a.getAttribute('href'), true);
                return;
            }

            const b = e.target.closest('[data-spot], [data-tour]');
            if (!b) return;

            const idx = b.dataset.tour !== undefined ? 0 : Math.max(0, STEPS.findIndex(x => x.sel === b.dataset.spot));
            gEl.addEventListener('hidden.bs.modal', () => spotGo(idx), { once: true });
            gBs.hide();
        });

        document.addEventListener('keydown', e => {
            if (spotIdx < 0) return;
            if (e.key === 'Escape') { e.preventDefault(); spotOff(); return; }
            if (!document.querySelector('.pl-spot')) return;
            if (e.key === 'ArrowRight') { e.preventDefault(); spotIdx === STEPS.length - 1 ? spotOff() : spotGo(spotIdx + 1); }
            else if (e.key === 'ArrowLeft') { e.preventDefault(); spotGo(spotIdx - 1); }
        }, true);

        // Tur otomatis saat halaman pertama kali dibuka oleh user ini (per user per browser),
        // dijalankan setelah data tabel selesai dimuat
        function autoTour() {
            let seen = false;
            try { seen = localStorage.getItem(TOUR_KEY) === '1'; } catch (e) { seen = true; }
            if (seen) return;

            setTimeout(() => {
                if (spotIdx < 0 && !document.querySelector('.modal.show') && !Swal.isVisible()) {
                    tourFirst = true;
                    spotGo(0);
                }
            }, 600);
        }
        $('#datatable').one('init.dt', autoTour);
    </script>
@endsection
