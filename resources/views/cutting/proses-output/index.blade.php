@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-fixedcolumns/css/fixedColumns.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style type="text/css">
        #list-table th,
        #list-table td {
            white-space: nowrap;
            vertical-align: middle;
        }

        /* Border "separate" supaya tidak ada celah di kolom freeze saat scroll */
        .dataTables_scroll table.table-bordered {
            border-collapse: separate;
            border-spacing: 0;
            border-width: 1px 0 0 1px;
        }

        .dataTables_scroll table.table-bordered th,
        .dataTables_scroll table.table-bordered td {
            border-width: 0 1px 1px 0;
        }

        /* Header bayangan di dalam body scroll (dipakai DataTables untuk lebar kolom): dibuat setipis mungkin,
           warnanya tetap ikut warna panel (inline style) */
        .dataTables_scrollBody thead th {
            height: 0 !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            border-top-width: 0 !important;
            border-bottom-width: 0 !important;
        }

        /* Bagian header bayangan di bawah Status Order - Size ikut freeze (class & left diisi fixFrozenLeft) */
        .dataTables_scrollBody thead th.frozen-clone {
            position: sticky;
            z-index: 2;
            background-color: #b8daff;
        }

        /* thead.table-primary ikut ter-clone ke body, latarnya bikin garis biru di bawah header */
        .dataTables_scrollBody thead,
        .dataTables_scrollBody thead tr {
            background: transparent !important;
        }

        /* Kolom freeze (Status Order - Size) */
        #list-table tbody td.dtfc-fixed-left {
            z-index: 1;
        }

        #list-table thead th.dtfc-fixed-left,
        .dataTables_scrollHead th.dtfc-fixed-left {
            background-color: #b8daff;
        }

        #list-table tfoot th.dtfc-fixed-left,
        .dataTables_scrollFoot th.dtfc-fixed-left {
            background-color: #fff;
        }

        /* Tombol Panduan di header: hover tetap terang, bukan hitam (timpa style.css global) */
        .po-head-r .btn-light:hover,
        .po-head-r .btn-light:focus-visible {
            background-color: #e2e6ea !important;
            border-color: #e2e6ea !important;
            color: #1d2433 !important;
        }

        /* ===== Panduan ===== */
        #poGuide, .po-spot-l {
            --g-ink: #1d2433; --g-muted: #6b7385; --g-line: #ebe7de; --g-soft: #f7f5f0;
            --g-pri: #4057e3; --g-ok: #1a9d63; --g-wip: #e39a12; --g-err: #e0483e; --g-empty: #e4e1d8; --g-acc: #ff971f;
        }
        .po-g { padding: 0 20px 20px; color: var(--g-ink); font-size: 13.5px; }
        .po-g-nav { position: sticky; top: 0; z-index: 2; display: flex; flex-wrap: wrap; gap: 6px; padding: 12px 0; background: #fff; border-bottom: 1px solid var(--g-line); margin-bottom: 4px; }
        .po-g-nav a { font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px; background: var(--g-soft); color: var(--g-pri); text-decoration: none; border: 1px solid var(--g-line); }
        .po-g-nav a:hover { background: var(--g-pri); color: #fff; }
        .po-g section { padding-top: 16px; scroll-margin-top: 56px; }
        .po-g h6 { display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap; font-weight: 700; color: var(--g-pri); border-left: 4px solid var(--g-acc); padding-left: 8px; margin-bottom: 10px; }
        .po-g ul { padding-left: 18px; margin-bottom: 0; }
        .po-g li { margin-bottom: 6px; line-height: 1.5; }
        .po-g-t { margin-bottom: 0; }
        .po-g-t td { vertical-align: top; padding: 7px 8px; line-height: 1.5; }
        .po-g-t td:first-child { width: 210px; white-space: nowrap; color: var(--g-pri); }
        .po-g-btn { display: inline-block; font-size: 12px; padding: 2px 8px; border: 1px solid #c5ccd6; border-radius: .3rem; background: #fff; }
        .po-g-flow { display: flex; align-items: stretch; gap: 8px; margin-bottom: 12px; flex-wrap: wrap; }
        .po-g-flow > div { flex: 1; min-width: 130px; background: var(--g-soft); border: 1px solid var(--g-line); border-radius: .5rem; padding: 8px 12px; }
        .po-g-flow > div.ok { background: #e7f7ee; border-color: #b9e8cf; }
        .po-g-flow b { display: block; color: var(--g-pri); } .po-g-flow span { font-size: 12px; color: var(--g-muted); }
        .po-g-flow > i { align-self: center; font-style: normal; font-weight: 700; color: var(--g-acc); }
        .po-g-faq dt { color: var(--g-pri); margin-top: 8px; } .po-g-faq dd { margin: 2px 0 0; }
        .po-g-show { border: 1px solid var(--g-pri); background: #fff; color: var(--g-pri); font-size: 11.5px; font-weight: 600; border-radius: 20px; padding: 2px 10px; }
        .po-g-show:hover, .po-g-show.pri { background: var(--g-pri); color: #fff; }
        .po-g-show.pri:hover { filter: brightness(.92); }
        .po-g-note { font-size: 12.5px; color: var(--g-muted); margin-bottom: 10px; }

        /* Panduan: peta halaman */
        .po-map { background: var(--g-soft); border: 1px solid var(--g-line); border-radius: .6rem; padding: 14px; max-width: 760px; }
        .po-map-z { position: relative; cursor: pointer; transition: box-shadow .15s; }
        .po-map-z:hover { box-shadow: 0 0 0 2px var(--g-acc); z-index: 6; }
        .po-map-n { position: absolute; top: -9px; left: -9px; z-index: 2; width: 22px; height: 22px; border-radius: 50%; background: var(--g-acc); color: #fff !important; font-size: 12px; font-weight: 700; display: grid; place-items: center; text-decoration: none; box-shadow: 0 0 0 2px #fff; }
        .po-map-n.sm { width: 18px; height: 18px; font-size: 10.5px; top: -7px; left: -7px; }
        .po-map-n:hover, .po-map-n:focus-visible { background: var(--g-pri); z-index: 5; }
        .po-map-tip { position: absolute; top: calc(100% + 8px); left: -4px; width: 230px; padding: 8px 10px; border-radius: .45rem; background: #1d2433; color: #fff; font-size: 12px; font-weight: 400; line-height: 1.45; text-align: left; box-shadow: 0 6px 18px rgba(0,0,0,.25); opacity: 0; visibility: hidden; transform: translateY(-4px); transition: opacity .15s, transform .15s, visibility .15s; pointer-events: none; }
        .po-map-tip:before { content: ""; position: absolute; top: -5px; left: 10px; width: 10px; height: 10px; background: #1d2433; transform: rotate(45deg); }
        .po-map-n.tip-l .po-map-tip { left: auto; right: -4px; }
        .po-map-n.tip-l .po-map-tip:before { left: auto; right: 10px; }
        .po-map-tip b { display: block; font-size: 12.5px; margin-bottom: 2px; color: #ffc98a; }
        .po-map-tip small { display: block; margin-top: 5px; font-size: 10.5px; color: rgba(255,255,255,.65); }
        .po-map-n:hover .po-map-tip, .po-map-n:focus-visible .po-map-tip { opacity: 1; visibility: visible; transform: none; }
        .po-map-head { display: flex; justify-content: space-between; align-items: center; gap: 10px; padding: 6px 8px 6px 10px; border-radius: .4rem .4rem 0 0; background: var(--sb-color, #082149); }
        .po-map-head b { font-size: 12px; color: #fff; }
        .po-map-head .r { display: flex; gap: 4px; padding: 3px; border-radius: .35rem; }
        .po-map-head .r i { width: 46px; height: 14px; border-radius: 3px; background: #fff; }
        .po-map-body { background: #fff; border: 1px solid var(--g-line); border-top: 0; border-radius: 0 0 .45rem .45rem; padding: 12px 10px; display: flex; flex-direction: column; gap: 12px; }
        .po-map-fl { display: flex; gap: 6px; width: max-content; border-radius: .3rem; }
        .po-map-fl i { width: 56px; height: 14px; border-radius: .25rem; border: 1px solid #c5ccd6; }
        .po-map-fl i.lk { background: #e9ecef; }
        .po-map-row { display: flex; gap: 8px; align-items: center; }
        .po-map-bt { display: flex; gap: 6px; border-radius: .3rem; }
        .po-map-bt i { width: 22px; height: 16px; border-radius: .25rem; background: #0d6efd; }
        .po-map-bt i.xl { width: 58px; background: #fff; border: 1.5px solid #198754; }
        .po-map-ch { border: 1px solid var(--g-line); border-radius: .4rem; padding: 6px 8px; }
        .po-map-ch .chip { display: flex; gap: 4px; justify-content: flex-end; margin-bottom: 6px; }
        .po-map-ch .chip i { width: 40px; height: 10px; border-radius: 10px; background: #cfe2ff; }
        .po-map-ch .chip i.r { background: #f8d7da; } .po-map-ch .chip i.w { background: #fff; border: 1px solid #dee2e6; }
        .po-map-ch .bar { height: 10px; border-radius: 0 5px 5px 0; margin-top: 4px; }
        .po-map-tb { display: grid; grid-template-columns: repeat(3, 26px) repeat(10, 1fr) repeat(3, 1fr); gap: 3px; border-radius: .3rem; }
        .po-map-tb i { height: 12px; border-radius: .15rem; background: var(--g-soft); border: 1px solid var(--g-line); }
        .po-map-tb i.fz { background: #b8daff; border-color: #9ec5fe; }
        .po-map-tb i.a { background: #d1e7dd; border-color: #a3cfbb; } .po-map-tb i.b { background: #fff3cd; border-color: #ffe69c; }
        .po-map-tb i.o { background: #e7f1ff; border-color: #9ec5fe; }
        .po-map-tb i.s { background: #198754; border: 0; height: 12px; width: 60%; justify-self: center; border-radius: 6px; }

        /* Panduan: sorot elemen & tur */
        .po-spot { position: fixed; z-index: 2000; border-radius: .6rem; box-shadow: 0 0 0 9999px rgba(17,24,39,.6); outline: 3px solid #ff971f; outline-offset: 3px; pointer-events: none; animation: poSpot 1.2s ease-in-out infinite; }
        @keyframes poSpot { 50% { outline-offset: 8px; outline-color: rgba(255,151,31,.6); } }
        .po-spot-cv { position: fixed; inset: 0; z-index: 1999; cursor: pointer; }
        .po-spot-l { position: fixed; z-index: 2001; background: #fff; color: var(--g-ink); font-size: 13px; padding: 10px 12px; border-radius: .5rem; box-shadow: 0 6px 18px rgba(0,0,0,.25); width: 340px; max-width: calc(100vw - 16px); }
        .po-spot-l .st { display: flex; justify-content: space-between; align-items: center; font-size: 10.5px; font-weight: 700; letter-spacing: .6px; text-transform: uppercase; color: var(--g-pri); margin-bottom: 4px; }
        .po-spot-l .dots { display: flex; gap: 4px; }
        .po-spot-l .dots i { width: 7px; height: 7px; border-radius: 50%; background: #e2e6ec; }
        .po-spot-l .dots i.done { background: #b7c2f5; }
        .po-spot-l .dots i.on { background: var(--g-acc); width: 16px; border-radius: 4px; }
        .po-spot-l > b { display: block; font-weight: 700; }
        .po-spot-l > span.d { display: block; color: #3d4450; font-size: 12.5px; line-height: 1.5; margin-top: 3px; }
        .po-spot-l > span.d b { color: var(--g-ink); }
        .po-spot-l .nav { display: flex; justify-content: space-between; gap: 8px; margin-top: 10px; }
        .po-spot-l .nav button { border: 1px solid #c5ccd6; background: #fff; color: var(--g-ink); font-size: 12.5px; font-weight: 600; border-radius: .35rem; padding: 4px 12px; }
        .po-spot-l .nav button:disabled { opacity: .4; }
        .po-spot-l .nav button.pri { background: var(--g-pri); border-color: var(--g-pri); color: #fff; }
        .po-spot-l .nav button:not(:disabled):hover { filter: brightness(.95); }
        .po-spot-l small { display: block; color: var(--g-muted); font-size: 11.5px; margin-top: 6px; padding-top: 5px; border-top: 1px solid #e2e6ec; }
        .po-spot-l kbd { font-size: 10px; padding: 0 4px; }

        @media (max-width: 576px) {
            .po-g-t td:first-child { width: auto; white-space: normal; }
            .po-g-flow > i { display: none; }
        }
    </style>
@endsection

@section('content')
    <div class="card">
        <div class="card-header bg-sb d-flex align-items-center">
            <h5 class="card-title fw-bold mb-0">
                <i class="fas fa-list"></i> Proses Output
            </h5>
            <div class="po-head-r ms-auto" style="margin-block: -.5rem;">
                <button type="button" class="btn btn-light btn-sm" id="guideBtn">
                    <i class="fas fa-book-open"></i> Panduan
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-end gap-3 mb-3" id="poFilter">
                <div>
                    <label class="form-label"><small>Buyer</small></label>
                    <select id="buyer_filter" class="form-control form-control-sm select2bs4" style="width: 220px;">
                        <option value="">Pilih Buyer</option>
                        @foreach ($buyers as $buyer)
                            <option value="{{ $buyer }}">{{ $buyer }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label"><small>WS</small></label>
                    <select id="ws_filter" class="form-control form-control-sm select2bs4" style="width: 170px;">
                        <option value="">Semua WS</option>
                    </select>
                </div>

                <div>
                    <label class="form-label"><small>Style</small></label>
                    <select id="style_filter" class="form-control form-control-sm select2bs4" style="width: 170px;">
                        <option value="">Semua Style</option>
                    </select>
                </div>

                <div>
                    <label class="form-label"><small>Color</small></label>
                    <select id="color_filter" class="form-control form-control-sm select2bs4" style="width: 170px;">
                        <option value="">Semua Color</option>
                    </select>
                </div>

                <div>
                    <label class="form-label"><small>Size</small></label>
                    <select id="size_filter" class="form-control form-control-sm select2bs4" style="width: 170px;">
                        <option value="">Semua Size</option>
                    </select>
                </div>

                <div>
                    <label class="form-label"><small>Status Order</small></label>
                    <select id="status_order_filter" class="form-control form-control-sm select2bs4" style="width: 170px;" disabled>
                        <option value="">Semua Status</option>
                        <option value="Open Order">Open Order</option>
                        <option value="Close Order">Close Order</option>
                    </select>
                </div>

                <div>
                    <button class="btn btn-primary btn-sm" id="btnSearch" onclick="listTableReload()">
                        <i class="fa fa-search"></i>
                    </button>
                </div>
                <div>
                    <a onclick="export_excel()" class="btn btn-outline-success position-relative btn-sm" id="but_export"
                        name="but_export">
                        <i class="fas fa-file-excel fa-sm"></i>
                        Export Excel
                    </a>
                </div>
            </div>
            {{-- Grafik output per panel (ikut hasil search tabel) --}}
            {{-- Tanpa overflow-hidden supaya tooltip grafik tidak terpotong --}}
            <div id="panel-chart-card" class="mb-3 border rounded shadow-sm d-none">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 px-3 py-2 border-bottom bg-light rounded-top">
                    <div class="fw-bold"><i class="fas fa-chart-bar text-primary me-1"></i> Output Per Panel</div>
                    <div id="panel-chart-summary" class="d-flex flex-wrap gap-2"></div>
                </div>
                <div id="panel-chart" class="px-2 pt-2"></div>
            </div>
            <div id="poTable">
                <table class="table table-bordered table-striped w-100 text-nowrap" id="list-table">
                    {{-- Header & body dibangun lewat JS sesuai panel yang ada di data (buildListTable) --}}
                </table>
            </div>
        </div>
    </div>

    {{-- Panduan halaman --}}
    <div class="modal fade" id="poGuide" tabindex="-1" aria-labelledby="poGuideTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <div>
                        <small class="text-uppercase fw-bold" style="letter-spacing: .6px; opacity: .75;">Panduan</small>
                        <h5 class="modal-title fw-bold" id="poGuideTitle">Proses Output</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body po-g">
                    <nav class="po-g-nav">
                        <a href="#pog-peta">Peta halaman</a><a href="#pog-alur">Alur singkat</a><a href="#pog-filter">Filter</a><a href="#pog-cari">Cari &amp; Export</a>
                        <a href="#pog-grafik">Grafik per panel</a><a href="#pog-tabel">Tabel &amp; rumus</a><a href="#pog-tips">Tips &amp; FAQ</a>
                    </nav>

                    <section id="pog-peta">
                        <h6><span>Peta halaman</span> <button type="button" class="po-g-show pri" data-tour="0"><i class="fa fa-play"></i> Mulai tur</button></h6>
                        <p class="po-g-note">Klik <b>nomor</b> untuk membaca penjelasannya, atau klik <b>kotaknya</b> untuk melihat langsung di halaman.</p>
                        <div class="po-map">
                            <div class="po-map-head">
                                <b>Proses Output</b>
                                <div class="po-map-z r" data-spot=".po-head-r"><a href="#pog-peta" class="po-map-n tip-l" aria-label="Tombol Panduan">1<span class="po-map-tip" role="tooltip"><b>Panduan</b>Membuka halaman bantuan ini kapan saja.<small>Klik kotak untuk melihat di halaman</small></span></a><i></i></div>
                            </div>
                            <div class="po-map-body">
                                <div class="po-map-row">
                                    <div class="po-map-z po-map-fl" data-spot="#poFilter"><a href="#pog-filter" class="po-map-n sm" aria-label="Filter">2<span class="po-map-tip" role="tooltip"><b>Filter</b>Buyer / WS wajib salah satu, lalu persempit dengan Style, Color, Size.<small>Klik nomor untuk penjelasan lengkap</small></span></a><i></i><i></i><i></i><i></i><i></i><i class="lk"></i></div>
                                    <div class="po-map-z po-map-bt" data-spot="#btnSearch"><a href="#pog-cari" class="po-map-n sm" aria-label="Cari dan Export">3<span class="po-map-tip" role="tooltip"><b>Cari &amp; Export Excel</b>Tampilkan data sesuai filter, atau unduh sebagai Excel.<small>Klik nomor untuk penjelasan lengkap</small></span></a><i></i><i class="xl"></i></div>
                                </div>
                                <div class="po-map-z po-map-ch" data-spot="#panel-chart-card">
                                    <a href="#pog-grafik" class="po-map-n sm" aria-label="Grafik">4<span class="po-map-tip" role="tooltip"><b>Output Per Panel</b>Total output tiap panel dibanding Output Set &amp; Qty Order.<small>Klik nomor untuk penjelasan lengkap</small></span></a>
                                    <div class="chip"><i></i><i class="r"></i><i class="w"></i><i class="w"></i></div>
                                    <div class="bar" style="width: 80%; background: #d1e7dd;"></div>
                                    <div class="bar" style="width: 62%; background: #fff3cd;"></div>
                                    <div class="bar" style="width: 72%; background: #f8d7da;"></div>
                                </div>
                                <div class="po-map-z po-map-tb" data-spot="#poTable">
                                    <a href="#pog-tabel" class="po-map-n sm" aria-label="Tabel">5<span class="po-map-tip" role="tooltip"><b>Tabel output</b>Kolom per panel, lalu Output Set, Qty Order &amp; Balance di ujung kanan.<small>Klik nomor untuk penjelasan lengkap</small></span></a>
                                    <i class="fz"></i><i class="fz"></i><i class="fz"></i><i class="a"></i><i class="a"></i><i class="a"></i><i class="a"></i><i class="a"></i><i class="b"></i><i class="b"></i><i class="b"></i><i class="b"></i><i class="b"></i><i class="o"></i><i class="o"></i><i class="o"></i>
                                    <i class="s"></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i>
                                    <i class="s"></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i><i></i>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section id="pog-alur">
                        <h6>Alur singkat</h6>
                        <div class="po-g-flow">
                            <div><b>Pilih Buyer / WS</b><span>wajib salah satu</span></div><i>→</i>
                            <div><b>Persempit</b><span>Style, Color, Size (opsional)</span></div><i>→</i>
                            <div><b>Klik cari</b><span>grafik &amp; tabel tampil</span></div><i>→</i>
                            <div class="ok"><b>Cek Balance</b><span>atau Export Excel</span></div>
                        </div>
                    </section>

                    <section id="pog-filter">
                        <h6><span>Filter</span> <button type="button" class="po-g-show" data-spot="#poFilter"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                        <table class="table table-sm po-g-t">
                            <tr><td><b>Buyer</b></td><td>Memilih buyer akan mengisi daftar WS milik buyer tersebut. Style, Color &amp; Size dikosongkan lagi.</td></tr>
                            <tr><td><b>WS</b></td><td>Memilih WS akan mengisi daftar Style, Color &amp; Size sesuai WS itu.</td></tr>
                            <tr><td><b>Style / Color / Size</b></td><td>Opsional, untuk mempersempit data. Kosongkan untuk melihat semua.</td></tr>
                            <tr><td><b>Status Order</b></td><td>Terkunci; terisi otomatis sesuai WS terpilih
                                (<span class="badge bg-success">Open Order</span> / <span class="badge bg-danger">Close Order</span>).</td></tr>
                        </table>
                        <ul class="mt-2">
                            <li>Minimal <b>Buyer atau WS</b> harus dipilih sebelum cari / export.</li>
                        </ul>
                    </section>

                    <section id="pog-cari">
                        <h6><span>Cari &amp; Export Excel</span> <button type="button" class="po-g-show" data-spot="#btnSearch"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                        <table class="table table-sm po-g-t">
                            <tr><td><span class="btn btn-primary btn-sm py-0 px-2"><i class="fa fa-search"></i></span> Cari</td><td>Menampilkan grafik dan tabel sesuai filter. Filter yang diganti baru berlaku setelah klik cari lagi.</td></tr>
                            <tr><td><span class="po-g-btn text-success"><i class="fas fa-file-excel"></i> Export Excel</span></td><td>Mengunduh data sesuai filter sebagai file .xlsx.</td></tr>
                        </table>
                    </section>

                    <section id="pog-grafik">
                        <h6><span>Grafik Output Per Panel</span> <button type="button" class="po-g-show" data-spot="#panel-chart-card"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                        <table class="table table-sm po-g-t">
                            <tr><td><b>Batang</b></td><td>Total output tiap panel; warnanya sama dengan warna header panel di tabel. Arahkan kursor ke batang untuk rincian Form, Scrap, Switching &amp; Adjustment.</td></tr>
                            <tr><td><b>Garis biru</b></td><td>Output Set (jumlah set yang sudah lengkap).</td></tr>
                            <tr><td><b>Garis merah</b></td><td>Qty Order.</td></tr>
                            <tr><td><b>Ringkasan atas</b></td><td>Output Set, Qty Order, Total Output, dan panel dengan output <b>terendah</b>.</td></tr>
                        </table>
                        <ul class="mt-2">
                            <li>Grafik baru muncul setelah klik cari, dan ikut menyesuaikan bila tabel disaring lewat kotak <b>Search</b>.</li>
                        </ul>
                    </section>

                    <section id="pog-tabel">
                        <h6><span>Tabel &amp; rumus</span> <button type="button" class="po-g-show" data-spot="#poTable"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                        <table class="table table-sm po-g-t">
                            <tr><td><b>Status Order – Size</b></td><td>Kolom kiri yang tetap terlihat (freeze) saat tabel digeser ke kanan. Satu baris = satu WS / Color / Size.</td></tr>
                            <tr><td><b>Kolom per panel</b></td><td>Form, Scrap, Switching Out, Switching In, Adjustment, lalu Output.</td></tr>
                            <tr><td><b>Output</b></td><td>Form + Scrap − Switching Out + Switching In + Adjustment.</td></tr>
                            <tr><td><b>Output Set</b></td><td>Output panel <b>terkecil</b> di baris itu. Output panel yang sama dengan Output Set ditulis <span class="text-primary fw-bold">biru</span>.</td></tr>
                            <tr><td><b>Balance</b></td><td>Output Set − Qty Order. <span class="text-success fw-bold">Hijau</span> = lebih, <span class="text-danger fw-bold">merah</span> = masih kurang.</td></tr>
                            <tr><td><b>Total (baris bawah)</b></td><td>Jumlah tiap kolom, mengikuti hasil Search.</td></tr>
                        </table>
                    </section>

                    <section id="pog-tips">
                        <h6>Tips &amp; FAQ</h6>
                        <dl class="po-g-faq mb-0">
                            <dt>Muncul "Pilih buyer atau WS terlebih dahulu"?</dt>
                            <dd>Pilih minimal Buyer atau WS, lalu klik cari lagi.</dd>
                            <dt>Panel mana yang menahan Output Set?</dt>
                            <dd>Lihat angka Output yang berwarna biru di tiap baris, atau chip <b>Terendah</b> di atas grafik.</dd>
                            <dt>Daftar WS / Style / Color / Size kosong?</dt>
                            <dd>Daftar WS ikut Buyer, sedangkan Style, Color &amp; Size ikut WS. Pilih Buyer / WS dulu.</dd>
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
    <script src="{{ asset('plugins/datatables-fixedcolumns/js/dataTables.fixedColumns.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-fixedcolumns/js/fixedColumns.bootstrap4.min.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <!-- ApexCharts -->
    <script src="{{ asset('plugins/apexcharts/apexcharts.min.js') }}"></script>
    <script>
        $('.select2bs4').not('#status_order_filter').select2({
            theme: 'bootstrap4',
            width: 'resolve'
        })

        // Status order tampil sebagai badge, warnanya sama dengan kolom Status Order di tabel
        $('#status_order_filter').select2({
            theme: 'bootstrap4',
            width: 'resolve',
            templateSelection: function (state) {
                if (!state.id) return state.text;

                return $('<span>').addClass(`badge text-nowrap ${state.id === 'Close Order' ? 'bg-danger' : 'bg-success'}`).css('font-size', '12px').text(state.text);
            }
        })

        $('.select2-container--bootstrap4 .select2-selection--single').css({
            'height': '30px',
            'font-size': '12px',
            'line-height': '30px'
        });

        const filterLabels = {
            ws: 'Semua WS',
            style: 'Semua Style',
            color: 'Semua Color',
            size: 'Semua Size',
        };

        // Isi ulang pilihan filter dari master_sb_ws
        function loadFilter(type, params) {
            const $select = $(`#${type}_filter`);

            $select.html(`<option value="">${filterLabels[type]}</option>`).trigger('change.select2');

            if (!params.buyer && !params.ws) return;

            $.get('{{ route('get-filter-proses-output') }}', { type, ...params }, function (res) {
                $select.append(res.map(value => $('<option>').val(value).text(value)));
            });
        }

        // Status order selalu terkunci: terisi otomatis saat WS dipilih, kosong saat WS belum dipilih
        function loadStatusOrder(ws) {
            const $select = $('#status_order_filter');

            $select.val('').trigger('change.select2');

            if (!ws) return;

            $.get('{{ route('get-filter-proses-output') }}', { type: 'status_order', ws }, function (res) {
                if ($('#ws_filter').val() !== ws) return;

                $select.val(res[ws] ?? 'Open Order').trigger('change.select2');
            });
        }

        // Buyer berubah: daftar WS ikut buyer, style / color / size dikosongkan
        $('#buyer_filter').on('change', function () {
            loadFilter('ws', { buyer: this.value });
            ['style', 'color', 'size'].forEach(type => loadFilter(type, {}));
            loadStatusOrder('');
        });

        // WS berubah: style / color / size & status order ikut WS
        $('#ws_filter').on('change', function () {
            ['style', 'color', 'size'].forEach(type => loadFilter(type, { ws: this.value }));
            loadStatusOrder(this.value);
        });

        // Kolom teks (1 baris per buyer / ws / style / color / size)
        const textColumns = [
            ['Status Order', 'status_order'], ['Buyer', 'buyer'], ['WS', 'ws'], ['Style', 'style'], ['Color', 'color'], ['Size', 'size'],
        ];

        // Sub kolom tiap panel: [judul, field dari server]
        const qtyColumns = [
            ['Form', 'qty_form'],
            ['Scrap', 'qty_scrap'],
            ['Switching Out', 'qty_switching_out'],
            ['Switching In', 'qty_switching_in'],
            ['Adjustment', 'qty_adjustment'],
        ];

        // Kolom ujung setelah semua panel: [judul, field]
        const endColumns = [
            ['Output Set', 'output_set'],
            ['Qty Order', 'qty_order'],
            ['Balance', 'balance'],
        ];

        // Output per panel = form + scrap - switching out + switching in + adjustment
        function hitungOutput(qty) {
            return qty.qty_form + qty.qty_scrap - qty.qty_switching_out + qty.qty_switching_in + qty.qty_adjustment;
        }

        // Data server (1 baris per panel) jadi 1 baris per buyer / ws / color / size, panel jadi kolom
        function pivotData(data, qtyOrder = {}, statusOrder = {}) {
            const panels = [...new Set(data.map(item => item.panel ?? '-'))].sort();
            const rows = new Map();

            data.forEach(item => {
                const key = [item.ws, item.color, item.size].join('|');

                if (!rows.has(key)) {
                    rows.set(key, {
                        status_order: statusOrder[item.ws] ?? 'Open Order',
                        buyer: item.buyer, ws: item.ws, style: item.style, color: item.color, size: item.size,
                        size_order: Number(item.size_order) || 0, panels: {},
                    });
                }

                const row = rows.get(key);
                const panel = row.panels[item.panel ?? '-'] ??= {};

                qtyColumns.forEach(([, field]) => panel[field] = (panel[field] ?? 0) + Number(item[field] ?? 0));
                row.size_order = Math.min(row.size_order, Number(item.size_order) || 0);
            });

            rows.forEach(row => {
                panels.forEach(name => {
                    const panel = row.panels[name] ??= {};

                    qtyColumns.forEach(([, field]) => panel[field] ??= 0);
                    panel.output = hitungOutput(panel);
                });

                // Output set = output panel terkecil
                row.output_set = panels.length ? Math.min(...panels.map(name => row.panels[name].output)) : 0;

                // Balance = output set - qty order
                row.qty_order = Number(qtyOrder[[row.ws, row.color, row.size].join('|')] ?? 0);
                row.balance = row.output_set - row.qty_order;
            });

            const sorted = [...rows.values()].sort((a, b) =>
                String(a.buyer).localeCompare(String(b.buyer)) || String(a.ws).localeCompare(String(b.ws)) || String(a.style).localeCompare(String(b.style)) || String(a.color).localeCompare(String(b.color)) || a.size_order - b.size_order
            );

            return { panels, rows: sorted };
        }

        const formatQty = value => Number(value ?? 0).toLocaleString('id-ID');

        // Warna header per panel: [latar, garis lebih gelap dari latar] (berulang jika panel lebih banyak dari daftar warna)
        const panelColors = [
            ['#d1e7dd', '#a3cfbb'], // hijau
            ['#fff3cd', '#ffe69c'], // kuning
            ['#f8d7da', '#f1aeb5'], // merah
            ['#e2d9f3', '#c5b3e6'], // ungu
            ['#ffe5d0', '#fecba1'], // oranye
            ['#d2f4ea', '#a6e9d5'], // tosca
            ['#f7d6e6', '#efadce'], // pink
            ['#e2e3e5', '#c4c8cb'], // abu-abu
        ];

        const panelStyle = p => {
            const [background, border] = panelColors[p % panelColors.length];

            return `background-color: ${background}; border-color: ${border} !important;`;
        };

        let listTable;
        let panelChart;

        // Grafik batang: total output per panel, garis putus-putus = output set
        function renderPanelChart(panels, rows) {
            $('#panel-chart-card').toggleClass('d-none', !panels.length);

            if (!panels.length) return;

            // Total qty per panel (untuk batang & rincian tooltip)
            const totals = panels.map(panel => {
                const total = { output: 0 };

                rows.forEach(row => {
                    total.output += row.panels[panel].output;
                    qtyColumns.forEach(([, field]) => total[field] = (total[field] ?? 0) + row.panels[panel][field]);
                });

                return total;
            });
            const outputSet = rows.reduce((sum, row) => sum + row.output_set, 0);
            const qtyOrder = rows.reduce((sum, row) => sum + row.qty_order, 0);

            // Ringkasan di header grafik: output set, qty order, total output & panel dengan output terendah
            const lowest = totals.reduce((min, total, p) => total.output < totals[min].output ? p : min, 0);
            const chip = (icon, title, value, style) =>
                `<span class="badge rounded-pill border fw-normal px-3 py-2" style="font-size: 12px; ${style}">
                    <i class="fas ${icon} me-1"></i>${title}: <b>${value}</b>
                </span>`;

            $('#panel-chart-summary').html(
                chip('fa-layer-group', 'Output Set', formatQty(outputSet), 'background-color: #cfe2ff; border-color: #9ec5fe !important; color: #084298;')
                + chip('fa-clipboard-list', 'Qty Order', formatQty(qtyOrder), 'background-color: #f8d7da; border-color: #f1aeb5 !important; color: #842029;')
                + chip('fa-boxes', 'Total Output', formatQty(totals.reduce((sum, total) => sum + total.output, 0)), 'background-color: #fff; color: #495057;')
                + chip('fa-arrow-down', 'Terendah', `${panels[lowest]} (${formatQty(totals[lowest].output)})`, `background-color: ${panelColors[lowest % panelColors.length][0]}; border-color: ${panelColors[lowest % panelColors.length][1]} !important; color: #212529;`)
            );

            const options = {
                series: [{ name: 'Output', data: totals.map(total => total.output) }],
                // Horizontal: categories = panel di kiri, qty di bawah
                xaxis: {
                    categories: panels,
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: { formatter: value => formatQty(Math.round(value)), style: { colors: '#6c757d' } },
                },
                // Sumbu qty minimal sampai qty order supaya garis batasnya selalu terlihat
                yaxis: {
                    max: max => Math.max(max, qtyOrder * 1.05),
                    labels: { style: { fontSize: '12px', fontWeight: 700, colors: '#343a40' } },
                },
                // Tinggi grafik menyesuaikan jumlah panel (±48px per panel, jadi tebal batang selalu sama)
                chart: { height: panels.length * 48 + 90 },
                // Warna batang = warna header panel di tabel, garis tepi = garis header
                colors: panels.map((panel, p) => panelColors[p % panelColors.length][0]),
                stroke: { show: true, width: 2, colors: panels.map((panel, p) => panelColors[p % panelColors.length][1]) },
                annotations: {
                    xaxis: [{
                        x: outputSet,
                        borderColor: '#0d6efd',
                        strokeDashArray: 5,
                        label: {
                            text: 'Output Set: ' + formatQty(outputSet),
                            orientation: 'horizontal',
                            position: 'top',
                            offsetY: -8,
                            borderColor: '#0d6efd',
                            style: { background: '#0d6efd', color: '#fff', fontSize: '11px', fontWeight: 600 },
                        },
                    }, {
                        // Batas qty order (label di bawah supaya tidak tabrakan dengan label output set)
                        x: qtyOrder,
                        borderColor: '#dc3545',
                        strokeDashArray: 5,
                        label: {
                            text: 'Qty Order: ' + formatQty(qtyOrder),
                            orientation: 'horizontal',
                            position: 'bottom',
                            offsetY: 8,
                            borderColor: '#dc3545',
                            style: { background: '#dc3545', color: '#fff', fontSize: '11px', fontWeight: 600 },
                        },
                    }],
                },
                tooltip: {
                    custom: ({ dataPointIndex }) => {
                        const total = totals[dataPointIndex];
                        const [background, border] = panelColors[dataPointIndex % panelColors.length];
                        const line = (title, value, bold) =>
                            `<div class="d-flex justify-content-between gap-4${bold ? ' fw-bold border-top pt-1 mt-1' : ''}"><span>${title}</span><span>${formatQty(value)}</span></div>`;

                        return `<div class="px-3 py-2" style="font-size: 12px; min-width: 190px;">
                            <div class="fw-bold mb-1 px-2 rounded" style="background-color: ${background}; border: 1px solid ${border};">${panels[dataPointIndex]}</div>
                            ${qtyColumns.map(([title, field]) => line(title, total[field])).join('')}
                            ${line('Output', total.output, true)}
                        </div>`;
                    },
                },
            };

            if (panelChart) {
                panelChart.updateOptions(options, true, false);
                return;
            }

            panelChart = new ApexCharts(document.querySelector('#panel-chart'), {
                ...options,
                chart: {
                    ...options.chart,
                    type: 'bar',
                    toolbar: { show: false },
                    fontFamily: 'inherit',
                    animations: { enabled: true, speed: 500 },
                    // Bayangan halus supaya batang tidak flat
                    dropShadow: { enabled: true, top: 3, left: 2, blur: 4, color: '#000', opacity: 0.12 },
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        distributed: true,
                        barHeight: '60%',
                        borderRadius: 8,
                        borderRadiusApplication: 'end',
                        dataLabels: { position: 'top' },
                    },
                },
                fill: { type: 'solid', opacity: 1 },
                dataLabels: {
                    enabled: true,
                    offsetX: 34,
                    formatter: formatQty,
                    background: {
                        enabled: true, foreColor: '#212529', borderRadius: 10, padding: 6,
                        borderWidth: 1, borderColor: '#dee2e6', opacity: 1,
                        dropShadow: { enabled: true, top: 1, left: 1, blur: 2, color: '#000', opacity: 0.15 },
                    },
                    style: { fontSize: '11px', fontWeight: 700, colors: ['#fff'] },
                },
                legend: { show: false },
                grid: {
                    borderColor: '#e9ecef',
                    strokeDashArray: 4,
                    xaxis: { lines: { show: true } },
                    yaxis: { lines: { show: false } },
                    // Latar baris selang-seling per panel
                    row: { colors: ['#f8f9fa', 'transparent'], opacity: 1 },
                    padding: { top: 10, right: 60 },
                },
                states: {
                    hover: { filter: { type: 'darken', value: 0.92 } },
                    active: { filter: { type: 'none' } },
                },
            });

            panelChart.render();
        }

        function buildListTable(panels, rows) {
            const panelColumns = [...qtyColumns, ['Output', 'output']];

            const columns = [
                ...textColumns.map(([title, data]) => ({
                    title,
                    data,
                    className: data === 'status_order' ? 'text-center' : '',
                    render: function (value, type) {
                        if (type !== 'display' || data !== 'status_order') return value;

                        return `<span class="badge text-nowrap ${value === 'Close Order' ? 'bg-danger' : 'bg-success'}">${value}</span>`;
                    }
                })),
                ...panels.flatMap((panel, p) => panelColumns.map(([title, field]) => ({
                    title,
                    data: null,
                    className: 'text-end' + (field === 'output' ? ' col-output' : ''),
                    render: function (data, type, row) {
                        const value = row.panels[panel][field];

                        if (type !== 'display') return value;

                        // Output panel yang sama dengan output set ditulis biru
                        return field === 'output' && value === row.output_set
                            ? `<span class="text-primary fw-bold">${formatQty(value)}</span>`
                            : formatQty(value);
                    }
                }))),
                // Kolom paling ujung, hanya jika ada panel
                ...(panels.length ? endColumns.map(([title, data]) => ({
                    title,
                    data,
                    className: 'text-end col-output',
                    render: function (value, type) {
                        if (type !== 'display') return value;

                        // Balance: plus hijau, minus merah
                        if (data === 'balance' && value !== 0) {
                            return `<span class="${value > 0 ? 'text-success' : 'text-danger'} fw-bold">${formatQty(value)}</span>`;
                        }

                        return formatQty(value);
                    }
                })) : []),
            ];

            if (listTable) {
                listTable.destroy();
            }

            // Header 2 baris: panel (colspan) & sub kolom qty
            const panelRow = textColumns.map(([title]) => `<th rowspan="${panels.length ? 2 : 1}" class="text-center align-middle">${title}</th>`).join('')
                + panels.map((panel, p) => `<th colspan="${panelColumns.length}" class="text-center align-middle" style="${panelStyle(p)}">${panel}</th>`).join('')
                + (panels.length ? endColumns.map(([title]) => `<th rowspan="2" class="text-center align-middle col-output">${title}</th>`).join('') : '');
            const subRow = panels.map((panel, p) => panelColumns.map(([title, field]) =>
                `<th class="text-center align-middle${field === 'output' ? ' col-output' : ''}" style="${panelStyle(p)}">${title}</th>`
            ).join('')).join('');
            // Tanpa colspan supaya footer kolom freeze tetap sejajar
            const footRow = textColumns.map((col, i) => `<th class="text-end">${i === textColumns.length - 1 ? 'Total' : ''}</th>`).join('')
                + columns.slice(textColumns.length).map(col => `<th class="${col.className}">0</th>`).join('');

            $('#list-table').html(`
                <thead class="table-primary">
                    <tr>${panelRow}</tr>
                    ${subRow ? `<tr>${subRow}</tr>` : ''}
                </thead>
                <tbody></tbody>
                <tfoot><tr>${footRow}</tr></tfoot>
            `);

            listTable = $('#list-table').DataTable({
                // Kolom teks pakai sel header teratas (rowspan)
                orderCellsTop: true,
                ordering: false,
                processing: true,
                paging: true,
                pageLength: 50,
                lengthMenu: [
                    [25, 50, 100, 500, -1],
                    [25, 50, 100, 500, 'All']
                ],
                searching: true,
                scrollY: '300px',
                scrollX: true,
                scrollCollapse: false,
                fixedColumns: {
                    left: textColumns.length
                },
                data: rows,
                columns: columns,
                columnDefs: [
                    {
                        targets: "_all",
                        defaultContent: "-"
                    },
                ],
                drawCallback: function () {
                    // Grafik dulu (di atas tabel) supaya tinggi tabel dihitung dari posisi akhirnya
                    renderPanelChart(panels, this.api().rows({ search: 'applied' }).data().toArray());

                    fitTableHeight();
                    this.api().columns.adjust();

                    // Total per kolom qty mengikuti hasil search
                    const api = this.api();

                    api.columns().every(function (i) {
                        if (i < textColumns.length) return;

                        const total = api.cells(null, i, { search: 'applied' }).render('sort')
                            .reduce((sum, value) => sum + Number(value ?? 0), 0);

                        $(api.column(i).footer()).text(formatQty(total));
                    });
                },
            });

            // Jalan setelah FixedColumns (handler-nya terdaftar lebih dulu saat init)
            listTable.on('draw.dt column-sizing.dt', fixFrozenLeft);
            fixFrozenLeft();
        }

        // FixedColumns menghitung posisi "left" dari outerWidth() yang dibulatkan, sehingga ada celah 1px
        // antar kolom freeze. Hitung ulang pakai lebar pecahan dari getBoundingClientRect.
        // Header, body & footer dihitung dari lebar selnya masing-masing supaya tidak ada sela.
        function fixFrozenLeft() {
            const columns = [...Array(textColumns.length).keys()].map(i => listTable.column(i));
            const groups = [
                columns.map(col => col.nodes().toArray()),
                columns.map(col => [col.header()]),
                columns.map(col => [col.footer()]),
                // Header bayangan di body scroll: baris pertama, sel Status Order - Size (rowspan)
                $('.dataTables_scrollBody thead tr:first-child th').slice(0, columns.length).addClass('frozen-clone')
                    .toArray().map(th => [th]),
            ];

            groups.forEach(cells => {
                let left = 0;

                cells.forEach(nodes => {
                    if (!nodes.length || !nodes[0]) return;

                    $(nodes).css('left', left + 'px');
                    left += nodes[0].getBoundingClientRect().width;
                });
            });
        }

        buildListTable([], []);

        // Tinggi area scroll tabel mengikuti sisa layar sampai bawah (minimal 300px)
        function fitTableHeight() {
            const $body = $('#list-table').closest('.dataTables_scrollBody');

            if (!$body.length) return;

            const reserved = 170; // total + info "Showing ..." + pagination + padding card + footer
            const height = Math.max(300, window.innerHeight - $body.offset().top - reserved);

            $body.css({ 'height': height + 'px', 'max-height': height + 'px' });
        }

        $(window).on('resize', function () {
            fitTableHeight();
            listTable?.columns.adjust();
        });

        // Filter yang dipilih; null jika buyer & ws belum dipilih
        function filterParams() {
            const params = {
                buyer: $('#buyer_filter').val(),
                ws: $('#ws_filter').val(),
                style: $('#style_filter').val(),
                color: $('#color_filter').val(),
                size: $('#size_filter').val(),
                status_order: $('#status_order_filter').val(),
            };

            if (!params.buyer && !params.ws) {
                iziToast.warning({
                    title: 'Perhatian',
                    message: 'Pilih buyer atau WS terlebih dahulu.',
                    position: 'topCenter'
                });
                return null;
            }

            return params;
        }

        async function export_excel() {
            const params = filterParams();

            if (!params) return;

            Swal.fire({
                title: "Exporting",
                html: "Please Wait...",
                timerProgressBar: true,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            try {
                const res = await fetch('{{ route('export-excel-proses-output') }}?' + new URLSearchParams(params));

                Swal.close();

                if (!res.ok) {
                    const json = await res.json().catch(() => ({}));

                    iziToast.warning({
                        title: 'Perhatian',
                        message: json.message ?? 'Export gagal',
                        position: 'topCenter'
                    });
                    return;
                }

                iziToast.success({
                    title: 'Success',
                    message: 'Success',
                    position: 'topCenter'
                });

                const blob = await res.blob();
                const link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = "Proses Output " + (params.ws || params.buyer).replace(/\//g, '-') + ".xlsx";
                link.click();

            } catch (err) {
                Swal.close();
                console.error(err);

                iziToast.error({
                    title: 'Error',
                    message: 'Export gagal',
                    position: 'topCenter'
                });
            }
        }

        function listTableReload() {
            const params = filterParams();

            if (!params) return;

            $.ajax({
                url: '{{ route('get-data-proses-output') }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: params,
                beforeSend: function () {
                    showLoading();
                    $('#btnSearch').prop('disabled', true);
                },
                success: function (res) {
                    const { panels, rows } = pivotData(res.data, res.qty_order, res.status_order);

                    buildListTable(panels, rows);
                },
                error: function (xhr) {
                    iziToast.error({
                        title: 'Gagal',
                        message: xhr.responseJSON?.message ?? 'Gagal mengambil data',
                        position: 'topCenter'
                    });
                },
                complete: function () {
                    hideLoading();
                    $('#btnSearch').prop('disabled', false);
                }
            });
        }

        // ===== Panduan: sorot elemen asli + tur langkah demi langkah =====
        const gEl = document.getElementById('poGuide'), gBs = bootstrap.Modal.getOrCreateInstance(gEl);
        const TOUR_KEY = 'proses_output_tour_seen_{{ Auth::id() }}';
        const STEPS = [
            { sel: '.po-head-r', sec: 'pog-peta', lbl: 'Panduan',
              d: 'Tombol <b>Panduan</b> membuka halaman bantuan ini kapan saja.' },
            { sel: '#poFilter', sec: 'pog-filter', lbl: 'Filter',
              d: 'Pilih <b>Buyer</b> atau <b>WS</b> (wajib salah satu). Style, Color &amp; Size bisa dipersempit sesuai WS. <b>Status Order</b> terisi otomatis dari WS.' },
            { sel: '#btnSearch', sec: 'pog-cari', lbl: 'Cari',
              d: 'Klik tombol <b>cari</b> untuk menampilkan data sesuai filter.' },
            { sel: '#but_export', sec: 'pog-cari', lbl: 'Export Excel',
              d: 'Unduh data sesuai filter yang dipilih sebagai file Excel.' },
            { sel: '#panel-chart-card', fb: '#poTable', sec: 'pog-grafik', lbl: 'Output Per Panel',
              d: 'Total output tiap panel. Garis biru = <b>Output Set</b>, garis merah = <b>Qty Order</b>. Arahkan kursor ke batang untuk rinciannya.' },
            { sel: '#poTable', sec: 'pog-tabel', lbl: 'Tabel output',
              d: 'Satu baris per WS / Color / Size, tiap panel punya kolom Form, Scrap, Switching, Adjustment &amp; Output. Di ujung: <b>Output Set</b>, <b>Qty Order</b>, <b>Balance</b>.' },
        ];
        let spotTok = 0, spotIdx = -1, tourFirst = false;

        function gGo(id, smooth) {
            const t = gEl.querySelector(id);
            if (t) t.scrollIntoView({ behavior: smooth ? 'smooth' : 'auto', block: 'start' });
        }

        function spotClear() {
            spotTok++;
            document.querySelectorAll('.po-spot, .po-spot-l, .po-spot-cv').forEach(x => x.remove());
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
            gEl.addEventListener('shown.bs.modal', () => gGo(first || !s ? '#pog-peta' : '#' + s.sec), { once: true });
            gBs.show();
        }

        function visibleEl(sel) {
            return sel ? [...document.querySelectorAll(sel)].find(x => x.offsetParent !== null) : null;
        }

        function spotGo(i) {
            if (i < 0 || i >= STEPS.length) return;
            spotClear();
            spotIdx = i;

            const s = STEPS[i], n = STEPS.length;
            // grafik belum tampil (belum cari): sorot tabelnya saja
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
                cv.className = 'po-spot-cv';
                cv.onclick = spotOff;

                const box = document.createElement('div');
                box.className = 'po-spot';
                Object.assign(box.style, { left: r.left - pad + 'px', top: r.top - pad + 'px', width: r.width + pad * 2 + 'px', height: r.height + pad * 2 + 'px' });

                const l = document.createElement('div');
                l.className = 'po-spot-l';
                l.innerHTML = `<div class="st"><span>Langkah ${i + 1} dari ${n}</span><span class="dots">${STEPS.map((_, k) => `<i class="${k === i ? 'on' : k < i ? 'done' : ''}"></i>`).join('')}</span></div>
                    <b>${$('<div>').text(s.lbl).html()}</b><span class="d">${s.d}</span>
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
                    if (tok !== spotTok || !document.querySelector('.po-spot')) return;
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
            const a = e.target.closest('.po-map-n, .po-g-nav a');
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
            if (!document.querySelector('.po-spot')) return;
            if (e.key === 'ArrowRight') { e.preventDefault(); spotIdx === STEPS.length - 1 ? spotOff() : spotGo(spotIdx + 1); }
            else if (e.key === 'ArrowLeft') { e.preventDefault(); spotGo(spotIdx - 1); }
        }, true);

        // Tur otomatis saat halaman pertama kali dibuka oleh user ini (per user per browser)
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
        autoTour();
    </script>
@endsection
