{{-- Gaya halaman Detail SO / BOM DB baru: sama dengan header, kartu ringkasan, dan tabel halaman BOM lama. --}}
<link rel="stylesheet" href="{{ asset('dist/css/marketing-buttons.css') }}?v={{ @filemtime(public_path('assets/dist/css/marketing-buttons.css')) }}">
<style>
    .ndoc { color: #284a63; font-family: 'Poppins', sans-serif; }
    .ndoc-crumb { margin-bottom: 16px; padding: 8px 10px; border-radius: 4px; background: #284a63; color: #b4b4b4; font-size: 16px; font-weight: 500; }
    .ndoc-crumb a { color: #b4b4b4; text-decoration: none; }
    .ndoc-crumb a:hover { color: #fff; text-decoration: underline; }
    .ndoc-crumb strong { color: #fff; font-weight: 500; }
    .ndoc-card { padding: 16px 32px 24px; border-radius: 10px; background: #fff; box-shadow: 0 10px 40px rgba(30, 41, 59, .08); }
    .ndoc-head { display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 14px; }
    .ndoc-head h1 { margin: 0; color: #284a63; font-size: 32px; font-weight: 700; }
    .ndoc-head p { margin: 2px 0 0; font-size: 12px; font-weight: 600; }
    .ndoc-actions { display: flex; flex-wrap: wrap; gap: 8px; }
    .ndoc-actions .mkt-btn { text-decoration: none; }
    .ndoc-ws { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin: -4px 0 16px; font-size: 14px; font-weight: 600; }
    .ndoc-ws .mkt-btn { text-decoration: none; }
    .ndoc-cards { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 12px; margin-bottom: 16px; }
    .ndoc-cards > div, .ndoc-box { min-width: 0; border: 1px solid #e1e7ef; border-radius: 10px; background: #fff; box-shadow: 0 3px 8px rgba(30, 41, 59, .10); }
    .ndoc-cards > div { padding: 10px 14px; }
    .ndoc-cards span { display: block; color: #9e9e9e; font-size: 16px; }
    .ndoc-cards strong { display: block; overflow: hidden; color: #284a63; font-size: 20px; font-weight: 700; text-overflow: ellipsis; white-space: nowrap; }
    .ndoc-grid { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 14px; margin-bottom: 18px; }
    .ndoc-box { overflow: hidden; }
    .ndoc-box h2 { margin: 0 12px; padding: 8px 0 6px; border-bottom: 1px solid #dce4ec; color: #284a63; font-size: 16px; font-weight: 700; }
    .ndoc-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); column-gap: 20px; padding: 6px 12px 8px; }
    .ndoc-fields > div { padding: 4px 0; border-bottom: 1px solid #e7e7e7; }
    .ndoc-fields span { display: block; color: #9e9e9e; font-size: 14px; }
    .ndoc-fields strong { display: block; color: #284a63; font-size: 15px; font-weight: 700; overflow-wrap: anywhere; }
    .ndoc-box .ndoc-scroll { padding: 8px 12px 12px; }
    .ndoc-scroll { overflow-x: auto; }
    .ndoc-title { margin: 22px 0 10px; color: #284a63; font-size: 22px; font-weight: 600; }
    .ndoc-table { width: 100%; border-collapse: collapse; font-family: Inter, sans-serif; font-size: 13px; font-variant-numeric: tabular-nums; }
    .ndoc-table th { padding: 6px 8px; border: 1px solid #284a63; background: #284a63; color: #fff; font-size: 12px; font-weight: 500; white-space: nowrap; }
    .ndoc-table td { padding: 5px 8px; border: 1px solid #dfe4ec; color: #2b2f36; vertical-align: top; }
    .ndoc-table td.n, .ndoc-table th.n { text-align: right; white-space: nowrap; }
    /* Tabel BOM lebar: kolom teks (content, shell, item, supplier) boleh turun baris dengan lebar minimum, kolom lain satu baris. */
    .ndoc-table td { white-space: nowrap; }
    .ndoc-table td.w { min-width: 170px; white-space: normal; }
    .ndoc-table tr.subrow td { background: #fbfcfe; }
    .ndoc-table tr.tot td { background: #e9f0fb; font-weight: 700; }
    .ndoc-table td.t { background: #a8c9ff; font-weight: 700; }
    .ndoc-table .dim { color: #9aa5ad; font-style: italic; }
    .ndoc-alert { margin: 0 0 16px; padding: 12px 16px; border: 1px solid #f0c36d; border-radius: 10px; background: #fff8e6; color: #7a5300; font-size: 14px; }
    .ndoc-alert ul { margin: 6px 0 0; padding-left: 22px; }
    .ndoc-grand { margin-top: 14px; text-align: right; font-size: 18px; font-weight: 700; }
    @media (max-width: 991.98px) {
        .ndoc-cards { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .ndoc-grid { grid-template-columns: minmax(0, 1fr); }
    }
    @media (max-width: 575.98px) {
        .ndoc-card { padding: 12px; }
        .ndoc-cards, .ndoc-fields { grid-template-columns: minmax(0, 1fr); }
        .ndoc-head h1 { font-size: 24px; }
    }
</style>
