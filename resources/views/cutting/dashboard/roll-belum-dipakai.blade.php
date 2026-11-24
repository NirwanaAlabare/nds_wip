<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roll Belum Dipakai</title>

    <script>
        // Pasang tema tersimpan secepatnya supaya halaman tidak "berkedip" ganti warna saat dibuka
        try {
            const temaTersimpan = localStorage.getItem("dashboard-roll-belum-dipakai-tema");
            if (temaTersimpan === "terang") document.documentElement.setAttribute("data-theme", "light");
            if (temaTersimpan === "gelap") document.documentElement.setAttribute("data-theme", "dark");
        } catch (error) {}
    </script>

    <style>
        /* ==========================================================
           Font lokal (tidak butuh internet, aman untuk TV)
           ========================================================== */
        @font-face { font-family: "Inter"; src: url("{{ asset('assets/fonts/Inter/Inter-Regular.woff2') }}") format("woff2"); font-weight: 400; font-display: swap; }
        @font-face { font-family: "Inter"; src: url("{{ asset('assets/fonts/Inter/Inter-Medium.woff2') }}") format("woff2"); font-weight: 500; font-display: swap; }
        @font-face { font-family: "Inter"; src: url("{{ asset('assets/fonts/Inter/Inter-SemiBold.woff2') }}") format("woff2"); font-weight: 600; font-display: swap; }
        @font-face { font-family: "Inter"; src: url("{{ asset('assets/fonts/Inter/Inter-Bold.woff2') }}") format("woff2"); font-weight: 700 900; font-display: swap; }

        /* ==========================================================
           Warna (mode terang & gelap)
           ========================================================== */
        :root {
            --bg: #f4f5fa;
            --card: #fff;
            --ink: #14141f;
            --ink2: #4a4a5a;
            --muted: #7c7c8c;
            --line: #e7e7ef;
            --soft: #f7f7fb;
            --grid: #ececf2;

            --red: #e5484d;     --red-lt: #ffeced;     --red-bd: #f8c9cb;
            --amber: #e08a00;   --amber-lt: #fff4dd;
            --green: #0fa676;   --green-lt: #e3f7ef;   --green-bd: #b5e6d2;
            --indigo: #5b4ee6;  --indigo-lt: #efedff;
            --blue: #2a78d6;    --blue-lt: #e8f1fc;

            --shadow: 0 1px 2px rgba(20, 20, 40, .04), 0 8px 24px -14px rgba(20, 20, 40, .16);
            --marquee-bg: linear-gradient(90deg, #241f5c, #3b2f9e 60%, #5b4ee6);
        }

        @media (prefers-color-scheme: dark) {
            :root:not([data-theme="light"]) {
                --bg: #0f0f14;
                --card: #1a1a22;
                --ink: #f0f0f5;
                --ink2: #c4c4d0;
                --muted: #8b8b99;
                --line: #2a2a35;
                --soft: #22222c;
                --grid: #262630;

                --red: #ff6b70;     --red-lt: #3a1c1f;     --red-bd: #5c2a2e;
                --amber: #f2b23a;   --amber-lt: #352a12;
                --green: #2cc794;   --green-lt: #13302a;   --green-bd: #1f4d3e;
                --indigo: #8b80ff;  --indigo-lt: #26234a;
                --blue: #4a95ea;    --blue-lt: #16253a;

                --shadow: 0 1px 2px rgba(0, 0, 0, .3);
                --marquee-bg: linear-gradient(90deg, #17143a, #28216b 60%, #3b2f9e);
            }
        }

        /* Tema gelap yang dipilih manual (isi sama dengan blok di atas) */
        :root[data-theme="dark"] {
            --bg: #0f0f14;
            --card: #1a1a22;
            --ink: #f0f0f5;
            --ink2: #c4c4d0;
            --muted: #8b8b99;
            --line: #2a2a35;
            --soft: #22222c;
            --grid: #262630;

            --red: #ff6b70;     --red-lt: #3a1c1f;     --red-bd: #5c2a2e;
            --amber: #f2b23a;   --amber-lt: #352a12;
            --green: #2cc794;   --green-lt: #13302a;   --green-bd: #1f4d3e;
            --indigo: #8b80ff;  --indigo-lt: #26234a;
            --blue: #4a95ea;    --blue-lt: #16253a;

            --shadow: 0 1px 2px rgba(0, 0, 0, .3);
            --marquee-bg: linear-gradient(90deg, #17143a, #28216b 60%, #3b2f9e);
        }

        /* ==========================================================
           Dasar
           ========================================================== */
        * { box-sizing: border-box; }

        html, body { margin: 0; }

        body {
            background: var(--bg);
            color: var(--ink);
            font-family: "Inter", system-ui, Arial, sans-serif;
            font-size: 14px;
            -webkit-font-smoothing: antialiased;
        }

        button, input { font-family: inherit; font-size: 14px; color: inherit; }

        .mono { font-family: ui-monospace, "Cascadia Mono", Consolas, monospace; font-weight: 600; }

        .wrap { max-width: 1680px; margin: 0 auto; padding: 20px 20px 60px; }

        .empty { padding: 30px; text-align: center; color: var(--muted); }

        /* ==========================================================
           Pita berjalan: roll yang sedang dipakai
           ========================================================== */
        .marquee {
            position: sticky;
            top: 0;
            z-index: 20;
            display: flex;
            min-height: 44px;
            background: var(--marquee-bg);
            color: #fff;
            box-shadow: 0 6px 18px -10px rgba(40, 30, 120, .6);
        }

        .marquee-label {
            flex: none;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0 16px;
            background: rgba(0, 0, 0, .22);
            font-size: 12.5px;
            font-weight: 800;
            letter-spacing: .4px;
            white-space: nowrap;
        }

        .marquee-count { background: #fff; color: #3b2f9e; border-radius: 999px; padding: 1px 8px; }

        .live-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: #3ee39b;
            animation: pulse 1.6s infinite;
        }

        .marquee-view {
            flex: 1;
            overflow: hidden;
            mask-image: linear-gradient(90deg, transparent, #000 3%, #000 97%, transparent);
        }

        .marquee-track {
            display: inline-flex;
            gap: 12px;
            padding: 10px 0;
            white-space: nowrap;
            animation: scroll var(--duration, 60s) linear infinite;
        }

        .marquee-track.diam { animation: none; padding-left: 16px; }
        .marquee:hover .marquee-track { animation-play-state: paused; }

        .marquee-item {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 5px 12px 5px 6px;
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .18);
            border-radius: 999px;
            font-size: 13px;
        }

        .marquee-item .info { opacity: .85; font-size: 12.5px; }

        .tag { border-radius: 999px; padding: 3px 9px; font-size: 11.5px; font-weight: 800; color: #1b1640; }

        @keyframes pulse {
            0%   { box-shadow: 0 0 0 0 rgba(62, 227, 155, .7); }
            70%  { box-shadow: 0 0 0 8px rgba(62, 227, 155, 0); }
            100% { box-shadow: 0 0 0 0 rgba(62, 227, 155, 0); }
        }

        @keyframes scroll {
            from { transform: translateX(0); }
            to   { transform: translateX(-50%); }
        }

        /* ==========================================================
           Header
           ========================================================== */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 14px;
            margin: 4px 0 12px;
        }

        h1 { margin: 0; font-size: 24px; font-weight: 800; letter-spacing: -.4px; }

        .subjudul { margin: 3px 0 0; color: var(--muted); font-size: 13.5px; }

        .chips { display: flex; gap: 8px; flex-wrap: wrap; }

        .chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 13px;
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 999px;
            color: var(--ink2);
            font-size: 12.5px;
            font-weight: 600;
        }

        .chip.error { color: var(--red); border-color: var(--red); }

        /* Pilihan tema: Otomatis / Terang / Gelap */
        .pilih-tema {
            display: inline-flex;
            gap: 2px;
            padding: 3px;
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 999px;
        }

        .pilih-tema button {
            padding: 4px 11px;
            border: 0;
            border-radius: 999px;
            background: transparent;
            color: var(--ink2);
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
        }

        .pilih-tema button.aktif { background: var(--ink); color: var(--card); }

        /* ==========================================================
           Notifikasi (warna lembut; merah muda kalau ada roll lewat batas)
           ========================================================== */
        .alert {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            padding: 14px 16px;
            border-radius: 16px;
            border: 1px solid var(--red-bd);
            background: var(--red-lt);
            color: var(--ink2);
        }

        .alert.aman {
            border-color: var(--green-bd);
            background: var(--green-lt);
        }

        .alert h2 { margin: 0; font-size: 17px; font-weight: 800; color: var(--red); }
        .alert p  { margin: 3px 0 0; font-size: 13.5px; }

        .alert.aman h2 { color: var(--green); }

        .lonceng {
            flex: none;
            display: grid;
            place-items: center;
            width: 42px;
            height: 42px;
            border-radius: 14px;
            background: var(--card);
            color: var(--red);
        }

        .alert.aman .lonceng { color: var(--green); }

        .alert-tombol {
            margin-left: auto;
            padding: 11px 18px;
            border: 0;
            border-radius: 11px;
            background: var(--red);
            color: #fff;
            font-size: 13.5px;
            font-weight: 800;
            cursor: pointer;
        }

        /* Lonceng bergoyang pelan sesekali, tanpa kedip */
        .alert.ada-lewat .lonceng svg { animation: goyangLonceng 3s ease-in-out infinite; }

        @keyframes goyangLonceng {
            0%, 80%, 100% { transform: rotate(0); }
            85%           { transform: rotate(-14deg); }
            90%           { transform: rotate(12deg); }
            95%           { transform: rotate(-6deg); }
        }

        @media (prefers-reduced-motion: reduce) {
            .marquee-track { animation-duration: 240s; }
            .alert.ada-lewat .lonceng svg { animation: none; }
        }

        /* ==========================================================
           Kartu angka (KPI)
           ========================================================== */
        .kpi-baris {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 12px;
            margin-bottom: 12px;
        }

        .kpi {
            position: relative;
            overflow: hidden;
            display: grid;
            grid-template-columns: auto 1fr;
            align-items: end;
            column-gap: 14px;
            padding: 12px 14px 12px 18px;
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 16px;
            box-shadow: var(--shadow);
            text-align: left;
            cursor: pointer;
            transition: transform .15s;
        }

        .kpi:hover { transform: translateY(-2px); }

        .kpi-judul { grid-column: 1 / -1; margin-bottom: 2px; font-size: 13px; font-weight: 700; color: var(--ink2); }

        .kpi-ikon {
            display: inline-grid;
            place-items: center;
            width: 28px;
            height: 28px;
            margin-right: 6px;
            border-radius: 9px;
            font-size: 15px;
            vertical-align: middle;
        }

        .kpi-angka { margin-top: 2px; font-size: 30px; font-weight: 800; letter-spacing: -.8px; line-height: 1.1; }
        .kpi-angka small { margin-left: 4px; font-size: 13px; font-weight: 600; color: var(--muted); letter-spacing: 0; }

        .kpi-detail { margin: 0 0 3px; font-size: 11.5px; line-height: 1.45; color: var(--muted); text-align: right; }

        .kpi-garis { position: absolute; left: 0; top: 0; bottom: 0; width: 5px; }

        .kpi.semua .kpi-ikon  { background: var(--indigo-lt); }
        .kpi.semua .kpi-garis { background: var(--indigo); }

        .kpi.lewat .kpi-ikon  { background: var(--red-lt); }
        .kpi.lewat .kpi-garis { background: var(--red); }
        .kpi.lewat .kpi-angka { color: var(--red); }

        /* ==========================================================
           Kartu & grafik
           ========================================================== */
        .grid-dua { display: grid; grid-template-columns: 1.25fr 1fr; gap: 12px; margin-bottom: 12px; }

        .card {
            padding: 14px 16px;
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 16px;
            box-shadow: var(--shadow);
        }

        .card h3 { margin: 0; font-size: 16px; font-weight: 800; }
        .card .petunjuk { margin: 2px 0 8px; font-size: 12.5px; color: var(--muted); }

        .label-klik {
            display: inline-flex;
            margin-left: 6px;
            padding: 3px 9px;
            border-radius: 999px;
            background: var(--indigo-lt);
            color: var(--indigo);
            font-size: 11.5px;
            font-weight: 700;
            vertical-align: 2px;
        }

        .kotak-warna { display: inline-block; width: 12px; height: 12px; border-radius: 4px; vertical-align: -1px; }

        .legenda { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 12px; font-size: 12px; color: var(--ink2); }
        .legenda span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 10px;
        }

        svg text { font-family: "Inter", system-ui, sans-serif; }

        .batang-umur { cursor: pointer; transition: opacity .15s; }
        .batang-umur:hover { opacity: .8; }

        .baris-style {
            display: grid;
            grid-template-columns: minmax(110px, 190px) 1fr 70px;
            align-items: center;
            gap: 10px;
            margin: 5px -4px;
            padding: 2px 4px;
            border-radius: 8px;
            font-size: 12.5px;
            cursor: pointer;
        }

        .baris-style:hover { background: var(--soft); }
        .baris-style .nama { overflow: hidden; white-space: nowrap; text-overflow: ellipsis; color: var(--ink2); font-weight: 600; }
        .baris-style .jalur { display: flex; gap: 2px; height: 18px; overflow: hidden; border-radius: 6px; background: var(--soft); }
        .baris-style .jalur i { display: block; height: 100%; }
        .baris-style .jumlah { text-align: right; font-weight: 700; font-variant-numeric: tabular-nums; }

        .tooltip {
            position: fixed;
            z-index: 70;
            padding: 7px 10px;
            border-radius: 8px;
            background: #14141f;
            color: #fff;
            font-size: 12.5px;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0;
            transition: opacity .1s;
        }

        /* ==========================================================
           Tabel
           ========================================================== */
        .tabel-header { display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 12px; margin-bottom: 12px; }

        .filter-grup { display: flex; flex-wrap: wrap; gap: 6px; }

        .tombol-filter {
            padding: 7px 13px;
            background: var(--soft);
            border: 1px solid var(--line);
            border-radius: 999px;
            color: var(--ink2);
            font-size: 12.5px;
            font-weight: 700;
            cursor: pointer;
        }

        .tombol-filter.aktif { background: var(--ink); border-color: var(--ink); color: var(--card); }
        .tombol-filter .jumlah { margin-left: 4px; opacity: .7; }

        .pencarian {
            min-width: 260px;
            padding: 9px 12px;
            background: var(--soft);
            border: 1px solid var(--line);
            border-radius: 11px;
            outline: none;
        }

        .pencarian:focus { border-color: var(--indigo); }

        .tabel-scroll { max-height: 620px; overflow: auto; border: 1px solid var(--line); border-radius: 12px; }

        table { width: 100%; border-collapse: collapse; font-size: 13px; }

        th {
            position: sticky;
            top: 0;
            z-index: 1;
            padding: 10px;
            background: var(--soft);
            border-bottom: 1px solid var(--line);
            color: var(--muted);
            font-size: 11.5px;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: .3px;
            white-space: nowrap;
            cursor: pointer;
            user-select: none;
        }

        td { padding: 9px 10px; border-bottom: 1px solid var(--line); white-space: nowrap; vertical-align: middle; }
        tr:hover td { background: var(--soft); }

        td.item { max-width: 280px; overflow: hidden; text-overflow: ellipsis; color: var(--ink2); font-size: 12.5px; }
        td.angka { text-align: right; font-weight: 700; font-variant-numeric: tabular-nums; }

        .satuan { color: var(--muted); font-weight: 600; }

        .titik-warna { display: inline-block; width: 10px; height: 10px; margin-right: 6px; border-radius: 50%; border: 1px solid rgba(0, 0, 0, .15); vertical-align: -1px; }

        .badge { display: inline-flex; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 800; }
        .badge.lewat  { background: var(--red-lt); color: var(--red); }
        .badge.normal { background: var(--amber-lt); color: var(--amber); }
        .badge.baru   { background: var(--green-lt); color: var(--green); }

        .tabel-footer { display: flex; justify-content: space-between; flex-wrap: wrap; gap: 8px; margin-top: 10px; font-size: 12.5px; color: var(--muted); }

        /* ==========================================================
           Modal detail
           ========================================================== */
        .modal-latar {
            position: fixed;
            inset: 0;
            z-index: 60;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(15, 15, 30, .55);
            backdrop-filter: blur(3px);
        }

        .modal-latar.terbuka { display: flex; }

        .modal {
            display: flex;
            flex-direction: column;
            width: min(1100px, 100%);
            max-height: 88vh;
            background: var(--card);
            border-radius: 18px;
            box-shadow: 0 30px 80px -20px rgba(0, 0, 0, .5);
        }

        .modal-header { display: flex; align-items: flex-start; gap: 14px; padding: 18px 20px 12px; border-bottom: 1px solid var(--line); }
        .modal-header h3 { margin: 0; font-size: 18px; font-weight: 800; }
        .modal-header p  { margin: 3px 0 0; color: var(--muted); font-size: 13px; }

        .modal-ikon { flex: none; display: grid; place-items: center; width: 44px; height: 44px; border-radius: 13px; font-size: 21px; }

        .modal-tutup { flex: none; width: 36px; height: 36px; margin-left: auto; border: 0; border-radius: 10px; background: var(--soft); font-size: 18px; cursor: pointer; }

        .modal-ringkasan { display: flex; flex-wrap: wrap; gap: 10px; padding: 12px 20px; }
        .modal-ringkasan div { padding: 8px 12px; background: var(--soft); border-radius: 12px; font-size: 12.5px; color: var(--muted); }
        .modal-ringkasan b { display: block; font-size: 18px; color: var(--ink); }

        .modal-filter { display: flex; flex-wrap: wrap; gap: 6px; padding: 0 20px 10px; }

        .modal-tabel { margin: 0 20px 18px; overflow: auto; border: 1px solid var(--line); border-radius: 12px; }

        /* ==========================================================
           Responsif
           ========================================================== */
        @media (max-width: 1400px) {
            .kpi-baris { grid-template-columns: 1fr 1fr; }
            .alert { grid-column: 1 / -1; }
        }

        @media (max-width: 900px) {
            .grid-dua { grid-template-columns: 1fr; }
        }

        @media (max-width: 640px) {
            .kpi { grid-template-columns: 1fr; }
            .kpi-detail { margin-top: 4px; text-align: left; }
        }

        @media (max-width: 520px) {
            .wrap { padding: 16px 16px 40px; }
            h1 { font-size: 21px; }
            .kpi-angka { font-size: 26px; }
            .pencarian { min-width: 0; width: 100%; }
            .alert-tombol { margin-left: 0; width: 100%; }
            .marquee-label .teks { display: none; }
            .baris-style { grid-template-columns: 110px 1fr 54px; }
            .modal-latar { padding: 0; align-items: flex-end; }
            .modal { max-height: 92vh; border-radius: 18px 18px 0 0; }
            .modal-tabel { margin: 0 12px 12px; }
            .modal-ringkasan, .modal-filter { padding-left: 12px; padding-right: 12px; }
        }
    </style>
</head>
<body>

    {{-- Pita berjalan: roll yang sedang di meja cutting --}}
    <div class="marquee">
        <div class="marquee-label">
            <span class="live-dot"></span>
            <span class="teks">SEDANG DIPAKAI CUTTING</span>
            <span class="marquee-count" id="jumlah-sedang-dipakai">…</span>
        </div>
        <div class="marquee-view">
            <div class="marquee-track diam" id="pita-sedang-dipakai">
                <span class="marquee-item">Memuat…</span>
            </div>
        </div>
    </div>

    <div class="wrap">

        {{-- Judul + jam --}}
        <div class="header">
            <div>
                <h1>Roll Kain Belum Dipakai</h1>
                <p class="subjudul">
                    Roll yang sudah diterima cutting tapi belum dipakai sama sekali
                    (termasuk roll yang baru diterima hari ini).
                    Batas waktu: <b>{{ $batasHari }} hari</b>.
                </p>
            </div>
            <div class="chips">
                <div class="pilih-tema" id="pilih-tema" role="group" aria-label="Pilih tema">
                    <button type="button" data-tema="otomatis" title="Ikuti pengaturan perangkat">🖥️ Otomatis</button>
                    <button type="button" data-tema="terang">☀️ Terang</button>
                    <button type="button" data-tema="gelap">🌙 Gelap</button>
                </div>
                <span class="chip" id="status-update">⏳ Memuat data…</span>
                <span class="chip">
                    📅 <b id="tanggal-sekarang"></b>
                    <span style="color: var(--line)">|</span>
                    🕒 <b class="mono" id="jam-sekarang" style="font-size: 14px; color: var(--indigo)"></b>
                </span>
            </div>
        </div>

        {{-- Notifikasi + angka ringkasan --}}
        <div class="kpi-baris">
            <div class="alert" id="notifikasi">
                <div class="lonceng">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 8a6 6 0 1 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/>
                        <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
                    </svg>
                </div>
                <div class="alert-isi">
                    <h2 id="notifikasi-judul">Memuat…</h2>
                    <p id="notifikasi-teks"></p>
                </div>
                <button class="alert-tombol" id="notifikasi-tombol" type="button">Lihat daftar →</button>
            </div>

            <button class="kpi semua" data-filter="semua" type="button">
                <div class="kpi-garis"></div>
                <div class="kpi-judul"><span class="kpi-ikon">🧵</span>Semua roll belum dipakai</div>
                <div class="kpi-angka" id="kpi-semua-angka">–</div>
                <div class="kpi-detail" id="kpi-semua-detail"></div>
            </button>

            <button class="kpi lewat" data-filter="lewat" type="button">
                <div class="kpi-garis"></div>
                <div class="kpi-judul"><span class="kpi-ikon">🚨</span>{{ $batasHari }} hari atau lebih</div>
                <div class="kpi-angka" id="kpi-lewat-angka">–</div>
                <div class="kpi-detail" id="kpi-lewat-detail"></div>
            </button>
        </div>

        {{-- Grafik --}}
        <div class="grid-dua">
            <div class="card">
                <h3>Berapa lama roll menunggu? <span class="label-klik">👆 Klik batang</span></h3>
                <p class="petunjuk">
                    Jumlah roll per umur sejak diterima.
                    <i class="kotak-warna" style="background: var(--red)"></i> <b>Merah</b> = ≥ {{ $batasHari }} hari, segera pakai ·
                    <i class="kotak-warna" style="background: var(--amber)"></i> <b>Kuning</b> = 1–{{ $batasHari - 1 }} hari ·
                    <i class="kotak-warna" style="background: var(--green)"></i> <b>Hijau</b> = 0 hari (hari ini)
                </p>
                <div id="grafik-umur"></div>
            </div>

            <div class="card">
                <h3>Style dengan roll menunggu paling banyak <span class="label-klik">👆 Klik baris</span></h3>
                <p class="petunjuk">8 style teratas. Bagian merah = roll yang sudah ≥ {{ $batasHari }} hari.</p>
                <div id="grafik-style"></div>
            </div>
        </div>

        {{-- Tabel lengkap --}}
        <div class="card" id="kartu-tabel">
            <div class="tabel-header">
                <div>
                    <h3>Daftar lengkap roll belum dipakai</h3>
                    <p class="petunjuk">Diurutkan dari yang paling lama menunggu. Klik judul kolom untuk mengurutkan.</p>
                    <div class="filter-grup" id="tabel-filter"></div>
                </div>
                <input class="pencarian" id="pencarian" type="search" placeholder="🔍 Cari ID roll, style, warna, no. req…">
            </div>

            <div class="tabel-scroll">
                <table>
                    <thead><tr id="tabel-kepala"></tr></thead>
                    <tbody id="tabel-isi"></tbody>
                </table>
            </div>

            <div class="tabel-footer">
                <span id="tabel-total"></span>
                <span>
                    Umur dihitung dari <b>Tanggal Terima</b> cutting.
                    Qty meter = qty konversi penerimaan (YRD × 0,9144; KGM tidak dikonversi).
                </span>
            </div>
        </div>
    </div>

    <div class="tooltip" id="tooltip"></div>

    {{-- Modal detail (dibuka dari grafik) --}}
    <div class="modal-latar" id="modal" role="dialog" aria-modal="true" aria-labelledby="modal-judul">
        <div class="modal">
            <div class="modal-header">
                <div class="modal-ikon" id="modal-ikon"></div>
                <div>
                    <h3 id="modal-judul"></h3>
                    <p id="modal-subjudul"></p>
                </div>
                <button class="modal-tutup" id="modal-tutup" type="button" aria-label="Tutup">✕</button>
            </div>
            <div class="modal-ringkasan" id="modal-ringkasan"></div>
            <div class="modal-filter" id="modal-filter"></div>
            <div class="modal-tabel">
                <table>
                    <thead><tr id="modal-tabel-kepala"></tr></thead>
                    <tbody id="modal-tabel-isi"></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // ==========================================================
        // Pengaturan
        // ==========================================================
        const URL_DATA = @json(route('dashboard-roll-belum-dipakai-data'));
        const BATAS_HARI = {{ (int) $batasHari }};
        const INTERVAL_REFRESH = 5 * 60 * 1000;   // ambil data ulang tiap 5 menit
        const UMUR_MAKS_GRAFIK = 7;               // batang terakhir grafik = "≥ 7 hari"
        const SATU_HARI = 24 * 60 * 60 * 1000;

        const WARNA_KEGIATAN = {
            "Form Gelar": "#9ef0c4",
            "Form Piece": "#b9dcff",
        };

        const WARNA_KAIN = {
            "DARK GREEN": "#1f5132",
            BLACK: "#222",
            WHITE: "#fff",
            GREEN: "#2e8b57",
            NAVY: "#1f2a5a",
            RED: "#d33",
            BLUE: "#2a6fd6",
            GREY: "#999",
            GRAY: "#999",
            KHAKI: "#c3b091",
            YELLOW: "#f2c94c",
            PINK: "#f4a6c1",
            CHARCOAL: "#444",
        };

        // Kolom tabel: [judul, nama field]
        const KOLOM_TABEL = [
            ["Status", "umur"],
            ["ID Roll", "id"],
            ["Tgl Diterima", "tgl"],
            ["Qty", "qty"],
            ["Qty (Meter)", "m"],
            ["Style", "style"],
            ["Warna", "color"],
            ["Item", "item"],
            ["No. Req", "req"],
            ["No. BPPB", "bppb"],
            ["WS", "ws"],
            ["WS Aktual", "wsAct"],
            ["Lot", "lot"],
            ["Roll", "no"],
        ];

        // ==========================================================
        // Data & keadaan halaman
        // ==========================================================
        let tanggalHariIni = new Date().toISOString().slice(0, 10);
        let semuaRoll = [];        // semua roll belum dipakai
        let rollLewatBatas = [];   // umur >= BATAS_HARI
        let rollNormal = [];       // umur < BATAS_HARI
        let sudahPernahDimuat = false;

        const tabel = {
            filter: "semua",       // semua | lewat | normal
            urutBerdasarkan: "umur",
            urutTurun: true,
            kataKunci: "",
        };

        let modalAktif = null;     // { daftarRoll, pakaiFilter, filter }

        // ==========================================================
        // Fungsi bantu
        // ==========================================================
        function el(id) {
            return document.getElementById(id);
        }

        function formatAngka(angka) {
            return Number(angka || 0).toLocaleString("id-ID", { maximumFractionDigits: 1 });
        }

        function formatTanggal(tanggal) {
            if (!tanggal) return "-";
            return new Date(tanggal + "T00:00:00").toLocaleDateString("id-ID", { day: "numeric", month: "short", year: "numeric" });
        }

        // Cegah teks dari database merusak HTML
        function aman(teks) {
            const ganti = { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;" };
            return String(teks ?? "-").replace(/[&<>"]/g, karakter => ganti[karakter]);
        }

        function ambilWarnaCss(nama) {
            return getComputedStyle(document.documentElement).getPropertyValue(nama).trim();
        }

        function rollSudahLewatBatas(roll) {
            return roll.umur >= BATAS_HARI;
        }

        function totalMeter(daftarRoll) {
            return daftarRoll.reduce((total, roll) => total + (roll.m || 0), 0);
        }

        // Contoh hasil: "1.250 YRD · 320 METER"
        function totalPerSatuan(daftarRoll) {
            const perSatuan = {};

            daftarRoll.forEach(roll => {
                perSatuan[roll.unit] = (perSatuan[roll.unit] || 0) + roll.qty;
            });

            return Object.entries(perSatuan)
                .sort((a, b) => b[1] - a[1])
                .map(([satuan, jumlah]) => formatAngka(jumlah) + " " + satuan)
                .join(" · ");
        }

        function urutkanDariPalingLama(daftarRoll) {
            return [...daftarRoll].sort((a, b) => b.umur - a.umur || String(a.req).localeCompare(String(b.req)));
        }

        // ==========================================================
        // Jam di pojok kanan atas
        // ==========================================================
        function perbaruiJam() {
            const sekarang = new Date();

            el("tanggal-sekarang").textContent = sekarang.toLocaleDateString("id-ID", {
                weekday: "long", day: "numeric", month: "long", year: "numeric",
            });

            el("jam-sekarang").textContent = sekarang
                .toLocaleTimeString("id-ID", { hour: "2-digit", minute: "2-digit", second: "2-digit", hour12: false })
                .replace(/\./g, ":");
        }

        // ==========================================================
        // Tooltip grafik
        // ==========================================================
        function tampilkanTooltip(event, html) {
            const tooltip = el("tooltip");
            tooltip.innerHTML = html;
            tooltip.style.opacity = 1;
            tooltip.style.left = Math.min(event.clientX + 12, innerWidth - tooltip.offsetWidth - 8) + "px";
            tooltip.style.top = (event.clientY + 14) + "px";
        }

        function sembunyikanTooltip() {
            el("tooltip").style.opacity = 0;
        }

        // ==========================================================
        // Pita berjalan: roll yang sedang dipakai
        // ==========================================================
        function tampilkanRollSedangDipakai(daftar) {
            const pita = el("pita-sedang-dipakai");
            el("jumlah-sedang-dipakai").textContent = daftar.length + " roll";

            if (daftar.length === 0) {
                pita.className = "marquee-track diam";
                pita.innerHTML = `<span class="marquee-item">Tidak ada roll yang sedang digelar saat ini</span>`;
                return;
            }

            const isi = daftar.map(roll => {
                const warnaTag = WARNA_KEGIATAN[roll.keg] || "#fff09a";
                const meja = roll.meja ? aman(roll.meja) + " · " : "";

                return `
                    <span class="marquee-item">
                        <span class="tag" style="background: ${warnaTag}">${aman(roll.keg)}</span>
                        <b class="mono">${aman(roll.id)}</b>
                        <span>${aman(roll.color)}</span>
                        <span class="info">${meja}${aman(roll.form)} · ${formatAngka(roll.qty)} ${aman(roll.unit)}</span>
                    </span>`;
            }).join("");

            // Isi digandakan supaya animasi berjalan tanpa putus
            pita.className = "marquee-track";
            pita.innerHTML = isi + isi;
            pita.style.setProperty("--duration", Math.max(20, daftar.length * 7) + "s");
        }

        // ==========================================================
        // Notifikasi & kartu angka
        // ==========================================================
        function tampilkanNotifikasi() {
            const notifikasi = el("notifikasi");

            if (rollLewatBatas.length > 0) {
                const umurTerlama = rollLewatBatas[0].umur;

                notifikasi.className = "alert ada-lewat";
                el("notifikasi-judul").textContent = `Ada ${rollLewatBatas.length} roll yang belum dipakai ${BATAS_HARI} hari atau lebih`;
                el("notifikasi-teks").textContent = `Paling lama ${umurTerlama} hari. Mohon dicek dan didahulukan pemakaiannya ya.`;
                el("notifikasi-tombol").style.display = "";
            } else {
                notifikasi.className = "alert aman";
                el("notifikasi-judul").textContent = `Aman, tidak ada roll yang menunggu ${BATAS_HARI} hari atau lebih`;
                el("notifikasi-teks").textContent = "Semua roll masih dalam batas waktu.";
                el("notifikasi-tombol").style.display = "none";
            }
        }

        function tampilkanKartuAngka(idAngka, idDetail, daftarRoll) {
            el(idAngka).innerHTML = `${daftarRoll.length}<small>roll</small>`;
            el(idDetail).innerHTML = `
                <b style="color: var(--ink); font-size: 13.5px">≈ ${formatAngka(totalMeter(daftarRoll))} meter</b><br>
                ${totalPerSatuan(daftarRoll) || "–"}`;
        }

        // ==========================================================
        // Grafik umur roll (batang)
        // ==========================================================
        function kelompokkanPerUmur() {
            const kelompok = [];
            const hariIni = new Date(tanggalHariIni + "T00:00:00");

            // Urutan kiri ke kanan: paling lama -> paling baru
            for (let umur = UMUR_MAKS_GRAFIK; umur >= 0; umur--) {
                const batangTerakhir = umur === UMUR_MAKS_GRAFIK;
                const daftarRoll = semuaRoll.filter(roll => batangTerakhir ? roll.umur >= umur : roll.umur === umur);

                kelompok.push({
                    umur: umur,
                    batangTerakhir: batangTerakhir,
                    tanggal: new Date(hariIni - umur * SATU_HARI),
                    daftarRoll: daftarRoll,
                });
            }

            return kelompok;
        }

        // Skala sumbu Y yang rapi (kelipatan 2 / 10 / 40 / 100)
        function nilaiAtasSumbuY(nilaiTerbesar) {
            let kelipatan = 100;
            if (nilaiTerbesar <= 8) kelipatan = 2;
            else if (nilaiTerbesar <= 40) kelipatan = 10;
            else if (nilaiTerbesar <= 200) kelipatan = 40;

            return Math.ceil(Math.max(nilaiTerbesar, 1) / kelipatan) * kelipatan;
        }

        function gambarGrafikUmur() {
            const wadah = el("grafik-umur");
            const kelompok = kelompokkanPerUmur();

            const lebar = Math.max(320, wadah.clientWidth || 560);
            const tinggi = 225;
            const jarak = { kiri: 34, kanan: 8, atas: 22, bawah: 46 };
            const tinggiArea = tinggi - jarak.atas - jarak.bawah;
            const lebarSlot = (lebar - jarak.kiri - jarak.kanan) / kelompok.length;
            const nilaiAtas = nilaiAtasSumbuY(Math.max(...kelompok.map(k => k.daftarRoll.length)));

            const warna = {
                garis: ambilWarnaCss("--grid"),
                teksPudar: ambilWarnaCss("--muted"),
                teks: ambilWarnaCss("--ink"),
                teks2: ambilWarnaCss("--ink2"),
                merah: ambilWarnaCss("--red"),
                kuning: ambilWarnaCss("--amber"),
                hijau: ambilWarnaCss("--green"),
            };

            let svg = `<svg viewBox="0 0 ${lebar} ${tinggi}" width="100%" role="img" aria-label="Grafik umur roll">`;

            // Garis bantu horizontal + angka sumbu Y
            for (let i = 0; i <= 4; i++) {
                const nilai = nilaiAtas / 4 * i;
                const y = jarak.atas + tinggiArea * (1 - nilai / nilaiAtas);

                svg += `<line x1="${jarak.kiri}" x2="${lebar - jarak.kanan}" y1="${y}" y2="${y}" stroke="${warna.garis}"/>`;
                svg += `<text x="${jarak.kiri - 6}" y="${y + 4}" text-anchor="end" font-size="10.5" fill="${warna.teksPudar}">${Math.round(nilai)}</text>`;
            }

            // Garis putus-putus pemisah "lewat batas"
            const indexBatas = kelompok.findIndex(k => k.umur < BATAS_HARI);
            if (indexBatas > 0) {
                const x = jarak.kiri + lebarSlot * indexBatas;

                svg += `<line x1="${x}" x2="${x}" y1="${jarak.atas - 14}" y2="${tinggi - jarak.bawah}" stroke="${warna.merah}" stroke-dasharray="4 4" stroke-width="1.5"/>`;
                svg += `<text x="${x - 6}" y="${jarak.atas - 6}" text-anchor="end" font-size="11" font-weight="700" fill="${warna.merah}">◀ lewat batas ${BATAS_HARI} hari</text>`;
            }

            // Batang per umur
            kelompok.forEach((k, index) => {
                const jumlah = k.daftarRoll.length;
                const tinggiBatang = tinggiArea * jumlah / nilaiAtas;
                const xBatang = jarak.kiri + lebarSlot * index + lebarSlot * .18;
                const yBatang = tinggi - jarak.bawah - tinggiBatang;
                const xTengah = jarak.kiri + lebarSlot * index + lebarSlot / 2;
                const warnaBatang = k.umur >= BATAS_HARI ? warna.merah : (k.umur === 0 ? warna.hijau : warna.kuning);
                const awalan = k.batangTerakhir ? "≥ " : "";
                const labelTanggal = k.tanggal.toLocaleDateString("id-ID", { day: "numeric", month: "short" });

                svg += `<rect class="batang-umur" data-index="${index}" x="${xBatang}" y="${yBatang}" width="${lebarSlot * .64}" height="${tinggiBatang}" rx="5" fill="${warnaBatang}"/>`;

                if (jumlah > 0) {
                    svg += `<text x="${xBatang + lebarSlot * .32}" y="${yBatang - 5}" text-anchor="middle" font-size="12" font-weight="800" fill="${warna.teks}">${jumlah}</text>`;
                }

                svg += `<text x="${xTengah}" y="${tinggi - jarak.bawah + 16}" text-anchor="middle" font-size="11.5" font-weight="700" fill="${warna.teks2}">${awalan}${k.umur} hari</text>`;
                svg += `<text x="${xTengah}" y="${tinggi - jarak.bawah + 31}" text-anchor="middle" font-size="10.5" fill="${warna.teksPudar}">${k.batangTerakhir ? "≤ " : ""}${labelTanggal}</text>`;
            });

            svg += "</svg>";
            wadah.innerHTML = svg;

            // Interaksi: tooltip & klik untuk buka detail
            wadah.querySelectorAll(".batang-umur").forEach(batang => {
                const k = kelompok[batang.dataset.index];
                const awalan = k.batangTerakhir ? "≥ " : "";

                batang.addEventListener("mousemove", event => {
                    const teks = k.umur === 0 ? "diterima hari ini" : `sudah ${awalan}${k.umur} hari menunggu`;
                    tampilkanTooltip(event, `<b>${k.daftarRoll.length} roll</b> ${teks}`);
                });

                batang.addEventListener("mouseleave", sembunyikanTooltip);

                batang.addEventListener("click", () => {
                    sembunyikanTooltip();
                    if (k.daftarRoll.length === 0) return;

                    const lewat = k.umur >= BATAS_HARI;
                    const hariIni = k.umur === 0;
                    let judul = `Roll menunggu ${k.umur} hari`;
                    if (k.batangTerakhir) judul = `Roll menunggu ${UMUR_MAKS_GRAFIK} hari atau lebih`;
                    if (hariIni) judul = "Roll diterima hari ini";

                    bukaModal({
                        ikon: lewat ? "🚨" : (hariIni ? "🆕" : "⏳"),
                        warnaIkon: lewat ? "var(--red-lt)" : (hariIni ? "var(--green-lt)" : "var(--amber-lt)"),
                        judul: judul,
                        subjudul: "Sudah diterima cutting, belum dipakai",
                        daftarRoll: k.daftarRoll,
                    });
                });
            });
        }

        // ==========================================================
        // Grafik style teratas
        // ==========================================================
        function gambarGrafikStyle() {
            const wadah = el("grafik-style");
            const perStyle = {};

            semuaRoll.forEach(roll => {
                if (!perStyle[roll.style]) {
                    perStyle[roll.style] = { style: roll.style, lewat: 0, normal: 0 };
                }

                if (rollSudahLewatBatas(roll)) perStyle[roll.style].lewat++;
                else perStyle[roll.style].normal++;
            });

            const delapanTeratas = Object.values(perStyle)
                .map(s => ({ ...s, total: s.lewat + s.normal }))
                .sort((a, b) => b.lewat - a.lewat || b.total - a.total)
                .slice(0, 8);

            if (delapanTeratas.length === 0) {
                wadah.innerHTML = `<div class="empty">Tidak ada roll yang menunggu.</div>`;
                return;
            }

            const totalTerbesar = Math.max(...delapanTeratas.map(s => s.total));

            const baris = delapanTeratas.map((s, index) => {
                const persenLewat = s.lewat / totalTerbesar * 100;
                const persenNormal = s.normal / totalTerbesar * 100;
                const angkaLewat = s.lewat > 0 ? `<span style="color: var(--red)">${s.lewat}</span> / ` : "";

                return `
                    <div class="baris-style" data-index="${index}" title="Klik untuk lihat roll ${aman(s.style)}">
                        <span class="nama">${aman(s.style)}</span>
                        <span class="jalur">
                            <i style="width: ${persenLewat}%; background: var(--red)"></i>
                            <i style="width: ${persenNormal}%; background: var(--blue); opacity: .45"></i>
                        </span>
                        <span class="jumlah">${angkaLewat}${s.total}</span>
                    </div>`;
            }).join("");

            const legenda = `
                <div class="legenda">
                    <span><i class="kotak-warna" style="background: var(--red)"></i>≥ ${BATAS_HARI} hari</span>
                    <span><i class="kotak-warna" style="background: var(--blue); opacity: .45"></i>&lt; ${BATAS_HARI} hari</span>
                    <span>Angka: merah / total roll</span>
                </div>`;

            wadah.innerHTML = baris + legenda;

            wadah.querySelectorAll(".baris-style").forEach(elemen => {
                elemen.addEventListener("click", () => {
                    const style = delapanTeratas[elemen.dataset.index].style;

                    bukaModal({
                        ikon: "👕",
                        warnaIkon: "var(--indigo-lt)",
                        judul: style,
                        subjudul: "Semua roll style ini yang belum dipakai",
                        daftarRoll: semuaRoll.filter(roll => roll.style === style),
                        pakaiFilter: true,
                    });
                });
            });
        }

        // ==========================================================
        // Tabel
        // ==========================================================
        function titikWarnaKain(namaWarna) {
            const warna = String(namaWarna || "").toUpperCase();
            const kunci = Object.keys(WARNA_KAIN).find(k => warna.startsWith(k))
                || Object.keys(WARNA_KAIN).find(k => warna.includes(k));

            return kunci ? `<i class="titik-warna" style="background: ${WARNA_KAIN[kunci]}"></i>` : "";
        }

        function barisTabel(roll) {
            const kelasBadge = rollSudahLewatBatas(roll) ? "lewat" : (roll.umur === 0 ? "baru" : "normal");
            const meter = roll.m == null
                ? `<span class="satuan">–</span>`
                : `${formatAngka(roll.m)} <span class="satuan">M</span>`;

            return `
                <tr>
                    <td><span class="badge ${kelasBadge}">${roll.umur} hari</span></td>
                    <td class="mono">${aman(roll.id)}</td>
                    <td>${formatTanggal(roll.tgl)}</td>
                    <td class="angka">${formatAngka(roll.qty)} <span class="satuan">${aman(roll.unit)}</span></td>
                    <td class="angka" style="color: var(--blue)">${meter}</td>
                    <td>${aman(roll.style)}</td>
                    <td>${titikWarnaKain(roll.color)}${aman(roll.color)}</td>
                    <td class="item" title="${aman(roll.item)}">${aman(roll.item)}</td>
                    <td class="mono">${aman(roll.req)}</td>
                    <td class="mono">${aman(roll.bppb)}</td>
                    <td class="mono">${aman(roll.ws)}</td>
                    <td class="mono">${aman(roll.wsAct)}</td>
                    <td>${aman(roll.lot)}</td>
                    <td class="angka">${aman(roll.no)}</td>
                </tr>`;
        }

        function daftarRollSesuaiFilter() {
            if (tabel.filter === "lewat") return rollLewatBatas;
            if (tabel.filter === "normal") return rollNormal;
            return semuaRoll;
        }

        function cocokDenganPencarian(roll) {
            if (!tabel.kataKunci) return true;

            const teksDicari = [roll.id, roll.style, roll.color, roll.req, roll.bppb, roll.ws, roll.wsAct, roll.item, roll.lot]
                .join(" ")
                .toLowerCase();

            return teksDicari.includes(tabel.kataKunci.toLowerCase());
        }

        function bandingkanBaris(a, b) {
            const nilaiA = a[tabel.urutBerdasarkan];
            const nilaiB = b[tabel.urutBerdasarkan];
            const arah = tabel.urutTurun ? -1 : 1;

            let hasil;
            if (typeof nilaiA === "number" || typeof nilaiB === "number") {
                hasil = (nilaiA ?? -1) - (nilaiB ?? -1);
            } else {
                hasil = String(nilaiA ?? "").localeCompare(String(nilaiB ?? ""));
            }

            // Kalau nilainya sama, yang lebih lama menunggu tampil duluan
            return hasil * arah || b.umur - a.umur;
        }

        function gambarTombolFilterTabel() {
            const pilihan = [
                ["semua", "Semua", semuaRoll.length],
                ["lewat", `🚨 ≥ ${BATAS_HARI} hari`, rollLewatBatas.length],
                ["normal", `⏳ < ${BATAS_HARI} hari`, rollNormal.length],
            ];

            el("tabel-filter").innerHTML = pilihan.map(([kunci, label, jumlah]) => `
                <button type="button" class="tombol-filter ${tabel.filter === kunci ? "aktif" : ""}" data-filter="${kunci}">
                    ${label}<span class="jumlah">${jumlah}</span>
                </button>`).join("");

            el("tabel-filter").querySelectorAll(".tombol-filter").forEach(tombol => {
                tombol.addEventListener("click", () => {
                    tabel.filter = tombol.dataset.filter;
                    gambarTabel();
                });
            });
        }

        function gambarKepalaTabel() {
            el("tabel-kepala").innerHTML = KOLOM_TABEL.map(([judul, field]) => {
                const panah = tabel.urutBerdasarkan === field ? (tabel.urutTurun ? " ↓" : " ↑") : "";
                return `<th data-field="${field}">${judul}${panah}</th>`;
            }).join("");

            el("tabel-kepala").querySelectorAll("th").forEach(th => {
                th.addEventListener("click", () => {
                    const field = th.dataset.field;

                    if (tabel.urutBerdasarkan === field) {
                        tabel.urutTurun = !tabel.urutTurun;
                    } else {
                        // Kolom angka default besar -> kecil, kolom teks A -> Z
                        tabel.urutBerdasarkan = field;
                        tabel.urutTurun = ["umur", "qty", "m"].includes(field);
                    }

                    gambarTabel();
                });
            });
        }

        function gambarTabel() {
            gambarTombolFilterTabel();
            gambarKepalaTabel();

            const daftar = daftarRollSesuaiFilter()
                .filter(cocokDenganPencarian)
                .sort(bandingkanBaris);

            if (daftar.length > 0) {
                el("tabel-isi").innerHTML = daftar.map(barisTabel).join("");
            } else {
                const pesan = sudahPernahDimuat ? "Tidak ada roll yang cocok." : "Memuat…";
                el("tabel-isi").innerHTML = `<tr><td colspan="${KOLOM_TABEL.length}" class="empty">${pesan}</td></tr>`;
            }

            el("tabel-total").textContent =
                `Menampilkan ${daftar.length} roll · total ≈ ${formatAngka(totalMeter(daftar))} meter (${totalPerSatuan(daftar) || "0"})`;
        }

        function lompatKeTabel(filter) {
            tabel.filter = filter;
            gambarTabel();
            el("kartu-tabel").scrollIntoView({ behavior: "smooth", block: "start" });
        }

        // ==========================================================
        // Modal detail
        // ==========================================================
        function bukaModal(pengaturan) {
            modalAktif = { ...pengaturan, filter: "semua" };

            el("modal-ikon").textContent = pengaturan.ikon;
            el("modal-ikon").style.background = pengaturan.warnaIkon;
            el("modal-judul").textContent = pengaturan.judul;
            el("modal-subjudul").textContent = pengaturan.subjudul;
            el("modal-tabel-kepala").innerHTML = KOLOM_TABEL.map(([judul]) => `<th style="cursor: default">${judul}</th>`).join("");

            gambarIsiModal();

            el("modal").classList.add("terbuka");
            document.body.style.overflow = "hidden";
            el("modal-tutup").focus();
        }

        function gambarIsiModal() {
            const semua = urutkanDariPalingLama(modalAktif.daftarRoll);
            const lewat = semua.filter(rollSudahLewatBatas);
            const ditampilkan = modalAktif.filter === "lewat" ? lewat : semua;

            el("modal-ringkasan").innerHTML = `
                <div><b>${semua.length}</b>roll belum dipakai</div>
                <div><b style="color: var(--red)">${lewat.length}</b>roll ≥ ${BATAS_HARI} hari</div>
                <div><b style="color: var(--blue)">≈ ${formatAngka(totalMeter(semua))} m</b>${totalPerSatuan(semua)}</div>`;

            // Tombol filter hanya muncul kalau isinya campuran (ada yang lewat batas & ada yang belum)
            const perluFilter = modalAktif.pakaiFilter && lewat.length > 0 && lewat.length < semua.length;
            const wadahFilter = el("modal-filter");

            wadahFilter.innerHTML = !perluFilter ? "" : [
                ["semua", "Semua", semua.length],
                ["lewat", `🚨 ≥ ${BATAS_HARI} hari`, lewat.length],
            ].map(([kunci, label, jumlah]) => `
                <button type="button" class="tombol-filter ${modalAktif.filter === kunci ? "aktif" : ""}" data-filter="${kunci}">
                    ${label}<span class="jumlah">${jumlah}</span>
                </button>`).join("");

            wadahFilter.querySelectorAll(".tombol-filter").forEach(tombol => {
                tombol.addEventListener("click", () => {
                    modalAktif.filter = tombol.dataset.filter;
                    gambarIsiModal();
                });
            });

            el("modal-tabel-isi").innerHTML = ditampilkan.map(barisTabel).join("");
        }

        function tutupModal() {
            el("modal").classList.remove("terbuka");
            document.body.style.overflow = "";
            modalAktif = null;
        }

        // ==========================================================
        // Ambil data dari server
        // ==========================================================
        async function muatData() {
            const statusUpdate = el("status-update");

            try {
                const respon = await fetch(URL_DATA, {
                    headers: { "Accept": "application/json", "X-Requested-With": "XMLHttpRequest" },
                    credentials: "same-origin",
                });

                if (!respon.ok) {
                    throw new Error("HTTP " + respon.status);
                }

                const data = await respon.json();

                tanggalHariIni = data.today;
                semuaRoll = urutkanDariPalingLama((data.unused || []).map(roll => ({ ...roll, umur: roll.age })));
                rollLewatBatas = semuaRoll.filter(rollSudahLewatBatas);
                rollNormal = semuaRoll.filter(roll => !rollSudahLewatBatas(roll));
                sudahPernahDimuat = true;

                // Data baru -> modal lama ditutup supaya tidak menampilkan isi usang
                if (modalAktif) tutupModal();

                tampilkanRollSedangDipakai(data.in_use || []);
                tampilkanNotifikasi();
                tampilkanKartuAngka("kpi-semua-angka", "kpi-semua-detail", semuaRoll);
                tampilkanKartuAngka("kpi-lewat-angka", "kpi-lewat-detail", rollLewatBatas);
                gambarGrafikUmur();
                gambarGrafikStyle();
                gambarTabel();

                statusUpdate.classList.remove("error");
                statusUpdate.textContent = "🔄 Diperbarui " + data.updated_at.slice(11, 16);
            } catch (error) {
                console.error(error);
                statusUpdate.classList.add("error");
                statusUpdate.textContent = "⚠️ Gagal memuat data, mencoba lagi…";
            }
        }

        // ==========================================================
        // Tema: otomatis (ikut perangkat) / terang / gelap
        // ==========================================================
        const KUNCI_SIMPAN_TEMA = "dashboard-roll-belum-dipakai-tema";

        function ambilTemaTersimpan() {
            try {
                return localStorage.getItem(KUNCI_SIMPAN_TEMA) || "otomatis";
            } catch (error) {
                return "otomatis";   // mis. mode private / penyimpanan diblokir
            }
        }

        function simpanTema(tema) {
            try {
                localStorage.setItem(KUNCI_SIMPAN_TEMA, tema);
            } catch (error) {
                // tidak apa-apa, tema hanya tidak diingat
            }
        }

        function terapkanTema(tema) {
            const atributTema = { terang: "light", gelap: "dark" };

            if (atributTema[tema]) {
                document.documentElement.setAttribute("data-theme", atributTema[tema]);
            } else {
                document.documentElement.removeAttribute("data-theme");
            }

            el("pilih-tema").querySelectorAll("button").forEach(tombol => {
                tombol.classList.toggle("aktif", tombol.dataset.tema === tema);
            });

            // Warna grafik SVG diambil dari CSS saat digambar, jadi perlu digambar ulang
            if (sudahPernahDimuat) gambarGrafikUmur();
        }

        el("pilih-tema").querySelectorAll("button").forEach(tombol => {
            tombol.addEventListener("click", () => {
                simpanTema(tombol.dataset.tema);
                terapkanTema(tombol.dataset.tema);
            });
        });

        // Kalau mode "otomatis" dan pengaturan perangkat berubah, gambar ulang grafik
        window.matchMedia("(prefers-color-scheme: dark)").addEventListener("change", () => {
            if (ambilTemaTersimpan() === "otomatis" && sudahPernahDimuat) gambarGrafikUmur();
        });

        terapkanTema(ambilTemaTersimpan());

        // ==========================================================
        // Pasang event & jalankan
        // ==========================================================
        el("modal-tutup").addEventListener("click", tutupModal);

        el("modal").addEventListener("click", event => {
            // Klik di area gelap di luar kotak modal
            if (event.target === el("modal")) tutupModal();
        });

        document.addEventListener("keydown", event => {
            if (event.key === "Escape" && modalAktif) tutupModal();
        });

        el("pencarian").addEventListener("input", event => {
            tabel.kataKunci = event.target.value;
            gambarTabel();
        });

        el("notifikasi-tombol").addEventListener("click", () => lompatKeTabel("lewat"));

        document.querySelectorAll(".kpi").forEach(kartu => {
            kartu.addEventListener("click", () => lompatKeTabel(kartu.dataset.filter));
        });

        let jedaResize;
        window.addEventListener("resize", () => {
            clearTimeout(jedaResize);
            jedaResize = setTimeout(() => {
                if (sudahPernahDimuat) gambarGrafikUmur();
            }, 150);
        });

        perbaruiJam();
        setInterval(perbaruiJam, 1000);

        gambarTabel();
        muatData();
        setInterval(muatData, INTERVAL_REFRESH);
    </script>
</body>
</html>
