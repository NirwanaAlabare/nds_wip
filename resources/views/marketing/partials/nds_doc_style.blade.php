{{-- Gaya halaman Detail SO / BOM DB baru: ukuran teks, font, dan desain disamakan dengan Detail Costing
     (resources/views/marketing/costing/detail.blade.php) dari header sampai tabel. --}}
<link rel="stylesheet" href="{{ asset('dist/css/marketing-fonts.css') }}?v={{ @filemtime(public_path('assets/dist/css/marketing-fonts.css')) }}">
<link rel="stylesheet" href="{{ asset('dist/css/marketing-buttons.css') }}?v={{ @filemtime(public_path('assets/dist/css/marketing-buttons.css')) }}">
<style>
    /* Halaman & kartu = .costing-detail-page / .costing-detail-card */
    .ndoc { min-height: calc(100vh - 56px); padding: 10px 14px 24px; background: #f5f5f5; color: #263746; font-family: 'Poppins', sans-serif; }
    .ndoc-card { margin: 0 auto; padding: 20px 24px 28px; border-radius: 10px; background: #fff; box-shadow: 0 5px 12px -3px rgba(30, 41, 59, .18), 0 2px 4px rgba(30, 41, 59, .07); }
    .ndoc .mkt-btn, .ndoc .mkt-icon-btn { font-family: 'Poppins', sans-serif; font-weight: 600; }

    /* Breadcrumb = .costing-crumb (di dalam kartu, sama dengan Costing) */
    .ndoc-crumb { margin-bottom: 14px; padding: 8px 10px; background: #284a63; color: #b4b4b4; font-size: 16px; font-weight: 500; }
    .ndoc-crumb a { color: #b4b4b4; text-decoration: none; }
    .ndoc-crumb a:hover { color: #fff; text-decoration: underline; }
    .ndoc-crumb strong { color: #fff; font-weight: 500; }

    /* Judul = .costing-detail-heading / -title / -subtitle */
    .ndoc-head { display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 14px; }
    .ndoc-head h1 { margin: 0; color: #284a63; font-family: 'Poppins', sans-serif; font-size: 32px; font-weight: 700; }
    .ndoc-head h1 i { color: #64748b; font-size: 12px; vertical-align: super; }
    .ndoc-head p { margin: 4px 0 0; color: #284a63; font-size: 12px; font-weight: 600; }
    .ndoc-head p i { margin-right: 4px; color: #a3b2c7; }
    .ndoc-actions { display: flex; flex-wrap: wrap; gap: 10px; }
    .ndoc-actions .mkt-btn { text-decoration: none; }
    .ndoc-ws { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin: -4px 0 16px; font-size: 14px; font-weight: 600; }
    .ndoc-ws .mkt-btn { text-decoration: none; }

    /* Kartu ringkasan = .costing-detail-stat: kartu pertama terang, sisanya gelap */
    .ndoc-cards { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-bottom: 16px; }
    .ndoc-cards > div { min-width: 0; min-height: 76px; padding: 10px 14px; border-radius: 10px; background: #6c8798; color: #fff; box-shadow: 0 3px 8px rgba(30, 41, 59, .10); }
    .ndoc-cards span { display: block; margin-bottom: 4px; color: #e7eef2; font-family: 'Instrument Sans', sans-serif; font-size: 20px; font-weight: 500; letter-spacing: 0.2px; line-height: 1.25; }
    .ndoc-cards strong { display: block; overflow: hidden; color: #fff; font-family: 'Poppins', sans-serif; font-size: 24px; font-weight: 700; letter-spacing: 0.05em; line-height: 1.25; text-overflow: ellipsis; white-space: nowrap; }
    .ndoc-cards > div:first-child { border: 1px solid #e1e7ef; background: #fff; }
    .ndoc-cards > div:first-child span { color: #9e9e9e; }
    .ndoc-cards > div:first-child strong { color: #284a63; }

    /* Kotak informasi = .costing-detail-panel / -fields / -field */
    .ndoc-grid { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 16px; margin-bottom: 18px; }
    .ndoc-box { min-width: 0; overflow: hidden; border: 1px solid #e1e7ef; border-radius: 10px; background: #fff; box-shadow: 0 3px 8px rgba(30, 41, 59, .08); }
    .ndoc-box h2 { margin: 0 12px; padding: 9px 0 7px; border-bottom: 1px solid #dce4ec; color: #616161; font-family: 'Poppins', sans-serif; font-size: 16px; font-weight: 700; letter-spacing: 0.05em; }
    .ndoc-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0 18px; padding: 4px 12px 10px; }
    .ndoc-fields > div { display: flex; min-width: 0; flex-direction: column; justify-content: center; gap: 2px; padding: 6px 0; border-bottom: 1px solid #e7e7e7; }
    .ndoc-fields span { color: #9e9e9e; font-family: 'Instrument Sans', sans-serif; font-size: 18px; font-weight: 500; letter-spacing: 0.2px; }
    .ndoc-fields strong { min-width: 0; overflow-wrap: anywhere; color: #284a63; font-family: 'Poppins', sans-serif; font-size: 18px; font-weight: 700; letter-spacing: 0.05em; }

    /* Judul bagian = .costing-detail-category-toggle (tanpa tombol buka/tutup) */
    .ndoc-title { margin: 18px 0 8px; padding: 6px 12px; border: 1px solid #94a3b8; border-radius: 6px; background: #fff; color: #284a63; font-family: 'Poppins', sans-serif; font-size: 25px; font-weight: 700; }

    /* Tabel = .costing-detail-table: header biru muda kapital dengan titik pemisah, tanpa garis vertikal, total merah muda */
    .ndoc-box .ndoc-scroll { padding: 8px 12px 12px; }
    .ndoc-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .ndoc-scroll::-webkit-scrollbar { height: 8px; }
    .ndoc-scroll::-webkit-scrollbar-track { background: #e2e8f0; border-radius: 4px; }
    .ndoc-scroll::-webkit-scrollbar-thumb { background: #284a63; border-radius: 4px; }
    .ndoc-table { width: 100%; margin: 0; border-collapse: collapse; font-family: 'Inter', sans-serif; font-size: 14px; font-variant-numeric: tabular-nums; }
    .ndoc-table th { padding: 10px 8px; border: 0; background: #cfe2ff; color: #1e293b; font-family: 'Inter', sans-serif; font-size: 12px; font-weight: 500; letter-spacing: 0.03em; text-align: center; text-transform: uppercase; vertical-align: middle; white-space: nowrap; }
    .ndoc-table thead th + th::before { content: ''; display: inline-block; width: 5px; height: 5px; margin: 0 5px 1px 0; border-radius: 50%; background: #94a3b8; vertical-align: middle; }
    .ndoc-table td { padding: 9px 8px; border: 0; border-bottom: 1px solid #edf1f5; color: #1e293b; font-family: 'Inter', sans-serif; font-size: 14px; font-weight: 400; vertical-align: middle; }
    .ndoc-table td.n { text-align: right; white-space: nowrap; }
    /* Tabel BOM lebar: kolom teks (content, shell, item, supplier) boleh turun baris dengan lebar minimum, kolom lain satu baris. */
    .ndoc-table td { white-space: nowrap; }
    .ndoc-table td.w { min-width: 170px; white-space: normal; }
    .ndoc-table tr.subrow td { background: #fbfcfe; }
    .ndoc-table tr.tot td { height: 36px; background: #ffd6dc; color: #1e293b; font-weight: 700; }
    .ndoc-table td.t { font-weight: 700; }
    /* Breakdown Order: kolom size & total ramping rata kanan, sisa lebar untuk Color / Destination. */
    .ndoc-bd th:first-child { width: 50px; }
    .ndoc-bd th.n { width: 90px; text-align: right; }
    .ndoc-bd th.n:last-child { width: 110px; }
    /* Tabel detail: teks rata tengah, qty / price rata kanan. */
    .ndoc-center td:not(.n), .ndoc-center th:not(.n) { text-align: center; }
    .ndoc-center th.n { text-align: right; }
    .ndoc-center tr.tot td:first-child, .ndoc-center td.l { text-align: left; }
    .ndoc-table .dim { color: #9aa5ad; font-style: italic; }

    .ndoc-alert { margin: 0 0 16px; padding: 12px 16px; border: 1px solid #f5a3ab; border-left: 4px solid #dc2626; border-radius: 10px; background: #fff1f2; color: #9f1239; }
    .ndoc-alert ul { margin: 6px 0 0; padding-left: 22px; }
    .ndoc-grand { margin-top: 14px; color: #284a63; text-align: right; font-family: 'Poppins', sans-serif; font-size: 18px; font-weight: 700; }

    /* ===== Responsif (skala sama dengan Detail Costing) ===== */
    @media (max-width: 1599.98px) {
        .ndoc-cards span { font-size: 16px; }
        .ndoc-cards strong { font-size: 18px; }
    }
    @media (max-width: 1199.98px) {
        .ndoc-cards { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .ndoc-fields span, .ndoc-fields strong { font-size: 16px; }
    }
    @media (max-width: 991.98px) {
        .ndoc-head h1 { font-size: 28px; }
        .ndoc-cards { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .ndoc-cards > div { min-height: 0; }
        .ndoc-cards span { font-size: 14px; }
        .ndoc-cards strong { font-size: 18px; white-space: normal; overflow-wrap: anywhere; }
        .ndoc-grid { grid-template-columns: minmax(0, 1fr); }
        .ndoc-fields span { font-size: 14px; }
        .ndoc-fields strong { font-size: 15px; }
    }
    @media (max-width: 767.98px) {
        .ndoc { padding: 6px 6px 18px; }
        .ndoc-card { padding: 14px 12px 20px; }
        .ndoc-head h1 { font-size: 26px; }
        .ndoc-table td { font-size: 13px; }
    }
    @media (max-width: 575.98px) {
        .ndoc-head h1 { font-size: 24px; }
        .ndoc-crumb { font-size: 13px; }
        .ndoc-cards { gap: 8px; }
        .ndoc-cards > div { padding: 8px 10px; }
        .ndoc-cards span { font-size: 13px; }
        .ndoc-cards strong { font-size: 16px; }
        .ndoc-fields span { font-size: 13px; }
        .ndoc-fields strong { font-size: 14px; }
        .ndoc-title { font-size: 20px; }
    }
    @media (max-width: 359.98px) {
        .ndoc-cards, .ndoc-fields { grid-template-columns: minmax(0, 1fr); }
    }
</style>
