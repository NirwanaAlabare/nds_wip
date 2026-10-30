<!DOCTYPE html>
<html lang="id" @if (!empty($lite)) class="lite" @endif>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NDS - Smart Line</title>
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('dist/img/tabicon.png') }}">
    {{-- dashboard TV versi smart factory: tanpa jQuery/Bootstrap/Swiper/JSCharting supaya ringan --}}
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free-6.4.2/css/all.min.css') }}">

    <style>
        :root {
            /* putih lembut: latar abu-biru & kartu putih redup supaya tidak silau di TV */
            --bg: #e6ecf4;
            --panel: #f7f9fc;
            --row: #edf2f8;
            --edge: rgba(15, 31, 61, .08);
            /* warna aksen kartu (garis atas + ikon judul), diganti per kartu di bagian AKSEN KARTU */
            --accent: #2563eb;
            --accent-soft: rgba(37, 99, 235, .12);
            /* teks: hitam pekat untuk angka, biru tua untuk label supaya kontras di layar TV */
            --ink: #0a1128;
            --muted: #1e3a6e;
            --dim: #8492a9;
            --cyan: #06b6d4;
            --brand: #2563eb;
            --good: #16a34a;
            --warn: #eab308;
            --bad: #dc2626;
            --violet: #a855f7;
            --radius: 16px;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            height: 100%;
            overflow: hidden;
            color: var(--ink);
            font-family: "Segoe UI", Roboto, system-ui, -apple-system, Arial, sans-serif;
            font-variant-numeric: tabular-nums;
            /* latar statis (tanpa animasi) supaya ringan di TV */
            background:
                radial-gradient(1200px 600px at 15% 0%, rgba(6, 182, 212, .07), transparent 60%),
                radial-gradient(900px 500px at 100% 100%, rgba(124, 58, 237, .06), transparent 60%),
                var(--bg);
        }

        /* kanvas minimal 1600x900 di-scale mengikuti layar; sisa lebar/tinggi diisi (stretch), jadi tidak ada area kosong */
        #stage {
            position: absolute;
            top: 0;
            left: 0;
            width: 1600px;
            height: 900px;
            transform-origin: 0 0;
            display: flex;
            flex-direction: column;
            gap: 16px;
            padding: 20px 24px;
        }

        .card {
            position: relative;
            background: var(--panel);
            border: 1px solid var(--edge);
            border-top: 4px solid var(--accent);
            border-radius: var(--radius);
            box-shadow: 0 1px 2px rgba(15, 31, 61, .06), 0 8px 22px rgba(15, 31, 61, .07);
        }

        /* ---------- AKSEN KARTU: warna garis atas & ikon judul per kartu ---------- */
        .brand {
            --accent: #0891b2;
            --accent-soft: rgba(8, 145, 178, .12);
        }

        .clock {
            --accent: #7c3aed;
            --accent-soft: rgba(124, 58, 237, .12);
        }

        .s2 .row3:first-child .big-card:nth-child(1),
        .defect-img-card {
            --accent: #0891b2;
            --accent-soft: rgba(8, 145, 178, .12);
        }

        .s2 .row3:first-child .big-card:nth-child(3),
        .proj-card:nth-child(3) {
            --accent: #d97706;
            --accent-soft: rgba(217, 119, 6, .12);
        }

        .s2 .gauge-card:nth-child(1),
        .proj-card:nth-child(2) {
            --accent: #16a34a;
            --accent-soft: rgba(22, 163, 74, .12);
        }

        .s2 .gauge-card:nth-child(3),
        .defect-list-card {
            --accent: #dc2626;
            --accent-soft: rgba(220, 38, 38, .12);
        }

        .proj-card:nth-child(1) {
            --accent: #7c3aed;
            --accent-soft: rgba(124, 58, 237, .12);
        }

        /* ikon judul kartu di dalam tile berwarna lembut */
        .section-title>i:first-child {
            flex: 0 0 auto;
            width: 36px;
            height: 36px;
            display: grid;
            place-items: center;
            border-radius: 10px;
            font-size: 17px;
            color: var(--accent);
            background: var(--accent-soft);
            filter: none;
        }

        /* siku dekorasi ala HUD */
        .hud::before,
        .hud::after {
            content: "";
            position: absolute;
            width: 22px;
            height: 22px;
            border: 2px solid var(--accent);
            opacity: .7;
            pointer-events: none;
        }

        .hud::before {
            top: -4px;
            left: -4px;
            border-right: 0;
            border-bottom: 0;
            border-top-left-radius: 8px;
        }

        .hud::after {
            top: -4px;
            right: -4px;
            border-left: 0;
            border-bottom: 0;
            border-top-right-radius: 8px;
        }

        /* ---------- HEADER ---------- */
        .header {
            display: grid;
            grid-template-columns: 360px 1fr 360px 300px;
            gap: 16px;
            height: 96px;
            flex: 0 0 auto;
        }

        .header .card {
            display: flex;
            align-items: center;
            padding: 0 22px;
            overflow: hidden;
        }

        .brand {
            gap: 16px;
        }

        /* logo berwarna gelap, jadi diberi tile terang supaya terbaca */
        .brand img {
            width: 58px;
            height: 58px;
            padding: 6px;
            border-radius: 14px;
            background: #fff;
            border: 1px solid var(--edge);
            box-shadow: 0 0 14px rgba(6, 182, 212, .25);
        }

        .eyebrow {
            font-size: 15px;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: var(--muted);
        }

        .brand .line-name {
            font-size: 34px;
            font-weight: 800;
            line-height: 1.1;
        }

        .order {
            flex-direction: column;
            align-items: flex-start !important;
            justify-content: center;
            min-width: 0;
        }

        .order .circuit {
            position: absolute;
            right: 0;
            top: 0;
            height: 100%;
            width: 62%;
            opacity: .35;
            pointer-events: none;
        }

        .order .buyer,
        .order .ws {
            position: relative;
            max-width: 100%;
            white-space: nowrap;
            overflow: hidden;
        }

        .order .buyer {
            font-size: 30px;
            font-weight: 800;
            line-height: 1.2;
        }

        .order .ws {
            font-size: 20px;
            color: var(--muted);
            font-weight: 700;
        }

        .scroll-text {
            display: inline-block;
            white-space: nowrap;
        }

        .scroll-text.marquee {
            padding-left: 100%;
            animation: marquee 30s linear infinite;
        }

        @keyframes marquee {
            from {
                transform: translateX(0);
            }

            to {
                transform: translateX(-100%);
            }
        }

        .clock {
            flex-direction: column;
            align-items: flex-start !important;
            justify-content: center;
        }

        .clock .time {
            display: flex;
            align-items: center;
            gap: 14px;
            font-size: 42px;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -.02em;
        }

        .clock .date {
            font-size: 18px;
            color: var(--muted);
            font-weight: 700;
            margin-top: 6px;
            white-space: nowrap;
        }

        /* kartu putih seperti header lain, aksen hijau di garis atas & bar progres shift */
        .worktime {
            --accent: #16a34a;
            --accent-soft: rgba(22, 163, 74, .15);
            flex-direction: column;
            align-items: flex-start !important;
            justify-content: center;
        }

        .worktime .eyebrow {
            position: relative;
        }

        .worktime .value {
            position: relative;
            font-size: 36px;
            font-weight: 800;
            line-height: 1.15;
        }

        .worktime .value small {
            font-size: 18px;
            font-weight: 700;
            color: var(--muted);
            margin: 0 6px 0 2px;
        }

        /* progres shift: menit kerja berjalan / total menit kerja plan */
        .shift-bar {
            position: relative;
            width: 100%;
            height: 5px;
            margin-top: 8px;
            border-radius: 99px;
            background: var(--accent-soft);
        }

        .shift-bar i {
            display: block;
            height: 100%;
            width: 0;
            border-radius: 99px;
            background: var(--accent);
            box-shadow: 0 0 6px var(--accent-soft);
            transition: width .8s ease;
        }

        /* ---------- SLIDES ---------- */
        .slides {
            position: relative;
            flex: 1 1 auto;
            min-height: 0;
            touch-action: pan-y;
            /* geser horizontal ditangani JS, scroll vertikal tetap */
            user-select: none;
            cursor: grab;
        }

        .slides img {
            -webkit-user-drag: none;
            pointer-events: none;
        }

        .slide {
            position: absolute;
            inset: 0;
            opacity: 0;
            visibility: hidden;
            transform: translateY(12px);
            transition: opacity .6s ease, transform .6s ease, visibility 0s linear .6s;
        }

        .slide.active {
            opacity: 1;
            visibility: visible;
            transform: none;
            transition: opacity .6s ease, transform .6s ease;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 22px;
            color: var(--muted);
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .section-title i {
            color: var(--brand);
            filter: drop-shadow(0 0 5px rgba(37, 99, 235, .35));
        }

        .section-title .meta {
            margin-left: auto;
            font-size: 17px;
            font-weight: 700;
            color: var(--muted);
            text-transform: none;
            letter-spacing: 0;
        }

        /* ---------- SLIDE 1: per jam ---------- */
        .s1 {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 16px;
        }

        .hourly-card {
            padding: 18px 22px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            min-height: 0;
        }

        .hourly {
            width: 100%;
            flex: 1 1 auto;
            border-collapse: separate;
            border-spacing: 0 6px;
            margin-top: -6px;
        }

        .hourly th {
            font-size: 17px;
            font-weight: 800;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .06em;
            padding: 4px 12px;
            text-align: center;
        }

        .hourly tr {
            background: var(--row);
            outline: 1px solid rgba(15, 31, 61, .07);
            outline-offset: -1px;
            border-radius: 10px;
        }

        .hourly thead tr {
            background: none;
            outline: 0;
        }

        .hourly td {
            font-size: 27px;
            font-weight: 800;
            text-align: center;
            padding: 0 12px;
        }

        .hourly td:first-child {
            text-align: left;
            padding-left: 18px;
            white-space: nowrap;
        }

        .hourly .dot {
            display: inline-block;
            width: 9px;
            height: 9px;
            border-radius: 50%;
            margin-right: 12px;
            vertical-align: middle;
            background: var(--dim);
        }

        .hourly .dot.good {
            background: var(--good);
            box-shadow: 0 0 8px var(--good);
        }

        .hourly .dot.bad {
            background: var(--bad);
            box-shadow: 0 0 8px var(--bad);
        }

        .hourly tr.future td {
            color: var(--dim);
        }

        .hourly tr.break {
            background: linear-gradient(90deg, #fee2e2, #fef2f2);
            outline-color: rgba(220, 38, 38, .35);
        }

        .hourly tr.break td {
            color: var(--bad);
        }

        /* latar & bingkai di level baris, bukan per sel, supaya tidak ada garis di antar kolom saat di-scale */
        .hourly tr.current {
            background: linear-gradient(90deg, #dff7fc, #ecfbfe);
            outline: 2px solid var(--cyan);
            outline-offset: -2px;
            /* pendar statis: animasi box-shadow menggambar ulang baris tiap frame, berat di TV spek rendah */
            box-shadow: 0 0 14px rgba(6, 182, 212, .3);
        }

        .hourly tr.current td {
            color: #0e7490;
        }

        .now-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-left: 12px;
            vertical-align: middle;
        }

        .now-tag b {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .08em;
            color: #fff;
            background: #1d4ed8;
            border-radius: 6px;
            padding: 3px 7px;
        }

        /* equalizer kecil = penanda live (dekorasi, bukan data) */
        .eq {
            display: inline-flex;
            align-items: flex-end;
            gap: 2px;
            height: 14px;
        }

        /* animasi pakai transform (scaleY), bukan height, supaya tidak menghitung ulang layout tiap frame */
        .eq i {
            width: 2px;
            height: 100%;
            background: var(--cyan);
            transform-origin: bottom;
            animation: eq 1s ease-in-out infinite;
        }

        .eq i:nth-child(2) {
            animation-delay: -.2s;
        }

        .eq i:nth-child(3) {
            animation-delay: -.4s;
        }

        .eq i:nth-child(4) {
            animation-delay: -.6s;
        }

        .eq i:nth-child(5) {
            animation-delay: -.8s;
        }

        @keyframes eq {

            0%,
            100% {
                transform: scaleY(.3);
            }

            50% {
                transform: scaleY(1);
            }
        }

        .var {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .var.up {
            color: var(--good);
        }

        .var.down {
            color: var(--bad);
        }

        .var i {
            font-size: 16px;
        }

        .effcell {
            display: flex;
            align-items: center;
            gap: 12px;
            justify-content: center;
        }

        .effcell span {
            min-width: 124px;
            text-align: right;
        }

        .bar {
            width: 90px;
            height: 7px;
            border-radius: 99px;
            background: rgba(15, 31, 61, .08);
        }

        .bar i {
            display: block;
            height: 100%;
            border-radius: 99px;
            transition: width .8s ease;
        }

        .tone-good {
            background: var(--good);
            box-shadow: 0 0 8px rgba(22, 163, 74, .45);
        }

        .tone-warn {
            background: var(--warn);
            box-shadow: 0 0 8px rgba(234, 179, 8, .45);
        }

        .tone-bad {
            background: var(--bad);
            box-shadow: 0 0 8px rgba(220, 38, 38, .45);
        }

        /* KPI kanan: kartu berwarna + visual data di kanan */
        .kpi-stack {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .kpi {
            flex: 1 1 0;
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 0 18px;
            overflow: hidden;
        }

        .kpi .text {
            flex: 1 1 auto;
            min-width: 0;
            position: relative;
            z-index: 1;
        }

        /* kartu KPI berwarna solid, teks putih */
        .kpi {
            color: #fff;
            border-color: transparent !important;
            border-top-width: 1px;
        }

        .kpi .label {
            font-size: 16px;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, .85);
        }

        .kpi .value {
            font-size: 44px;
            font-weight: 800;
            line-height: 1.1;
        }

        .kpi .value small {
            font-size: 20px;
            font-weight: 700;
            opacity: .8;
        }

        .kpi .sub {
            font-size: 14px;
            font-weight: 700;
            letter-spacing: .04em;
            color: rgba(255, 255, 255, .85);
            white-space: nowrap;
        }

        .kpi .viz {
            flex: 0 0 120px;
            height: 70px;
            display: grid;
            place-items: center;
        }

        .kpi .viz svg {
            width: 100%;
            height: 100%;
            overflow: visible;
        }

        .kpi .viz>i {
            font-size: 58px;
        }

        .ico {
            display: grid;
            place-items: center;
            flex: 0 0 auto;
        }

        .kpi .ico {
            width: 54px;
            height: 54px;
            border-radius: 14px;
            font-size: 24px;
            background: rgba(255, 255, 255, .18);
            border: 1px solid rgba(255, 255, 255, .3);
            color: #fff;
        }

        .kpi.target {
            background: linear-gradient(135deg, #0891b2, #22d3ee);
            box-shadow: 0 10px 24px rgba(8, 145, 178, .3);
        }

        .kpi.output {
            background: linear-gradient(135deg, #0f766e, #14b8a6);
            box-shadow: 0 10px 24px rgba(15, 118, 110, .3);
        }

        .kpi.variation {
            background: linear-gradient(135deg, #6d28d9, #a855f7);
            box-shadow: 0 10px 24px rgba(109, 40, 217, .3);
        }

        .kpi.eff {
            background: linear-gradient(135deg, #15803d, #22c55e);
            box-shadow: 0 10px 24px rgba(21, 128, 61, .3);
        }

        .kpi.defect {
            background: linear-gradient(135deg, #b91c1c, #ef4444);
            box-shadow: 0 10px 24px rgba(185, 28, 28, .3);
        }

        .kpi .var.up,
        .kpi .var.down {
            color: #fff;
        }

        .kpi.defect .viz>i {
            color: rgba(255, 255, 255, .9);
            filter: drop-shadow(0 0 12px rgba(255, 255, 255, .6));
        }

        .proj b {
            color: #fef08a;
        }

        .proj .neg {
            color: #ffedd5;
        }

        .proj .pos {
            color: #dcfce7;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 99px;
            font-size: 15px;
            font-weight: 800;
            letter-spacing: .02em;
            border: 1px solid currentColor;
        }

        .pill.good {
            background: #f0fdf4;
            color: var(--good);
        }

        .pill.bad {
            background: #fef2f2;
            color: var(--bad);
        }

        /* ---------- SLIDE 2: KPI + gauge ---------- */
        .s2 {
            display: grid;
            grid-template-rows: 1fr 1fr;
            gap: 16px;
        }

        .row3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            min-height: 0;
        }

        /* isi kartu dibagi rata sampai bawah, tidak menyisakan ruang kosong */
        .big-card {
            padding: 22px 26px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 14px;
        }

        .big-card .head {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .hero {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .hero .ico {
            width: 72px;
            height: 72px;
            border-radius: 18px;
            font-size: 30px;
            border: 1px solid currentColor;
            background: #fff;
            box-shadow: 0 0 16px -6px currentColor;
        }

        .hero .num {
            font-size: 72px;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -.02em;
        }

        .hero .cap {
            font-size: 18px;
            font-weight: 700;
            color: var(--muted);
            letter-spacing: .08em;
            text-transform: uppercase;
            margin-top: 4px;
        }

        .c-cyan {
            color: #0891b2;
        }

        .c-blue {
            color: var(--brand);
        }

        .c-amber {
            color: #d97706;
        }

        .c-green {
            color: var(--good);
        }

        .progress {
            position: relative;
            height: 12px;
            border-radius: 99px;
            background: rgba(15, 31, 61, .08);
        }

        .progress>i {
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            border-radius: 99px;
            transition: width .8s ease;
        }

        .progress .marker {
            position: absolute;
            top: -6px;
            bottom: -6px;
            width: 3px;
            border-radius: 2px;
            background: var(--ink);
            transition: left .8s ease;
        }

        /* legend warna bar progress */
        .legend {
            display: flex;
            flex-wrap: wrap;
            gap: 4px 16px;
            margin-top: -4px;
            font-size: 13px;
            font-weight: 700;
            color: var(--muted);
        }

        .legend span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .legend i {
            display: inline-block;
            width: 14px;
            height: 8px;
            border-radius: 99px;
        }

        .legend i.mk {
            width: 3px;
            height: 14px;
            border-radius: 2px;
            background: var(--ink);
        }

        .split {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .split>div {
            background: var(--row);
            border: 1px solid rgba(15, 31, 61, .07);
            border-radius: 12px;
            padding: 12px 16px;
        }

        .split .k {
            font-size: 15px;
            font-weight: 700;
            color: var(--muted);
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .split .v {
            font-size: 40px;
            font-weight: 800;
            line-height: 1.15;
        }

        .split .k .sw {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 3px;
            margin-right: 6px;
            vertical-align: 0;
        }

        .gauge-card {
            padding: 18px 22px 14px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .gauge-card .section-title {
            align-self: stretch;
        }

        .gauge {
            position: relative;
            width: 300px;
            flex: 1 1 auto;
            min-height: 0;
        }

        .gauge svg {
            width: 100%;
            height: 100%;
            overflow: visible;
        }

        .gauge .g-value {
            transition: stroke-dasharray .9s ease, stroke .4s ease;
        }

        .gauge .center {
            position: absolute;
            left: 0;
            right: 0;
            top: 46%;
            transform: translateY(-50%);
            text-align: center;
        }

        .gauge .center .n {
            font-size: 58px;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -.02em;
        }

        .gauge .center .n small {
            font-size: 24px;
            color: var(--muted);
        }

        .gauge-legend {
            display: flex;
            gap: 18px;
            font-size: 16px;
            font-weight: 700;
            color: var(--muted);
        }

        .gauge-legend span::before {
            content: "";
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 3px;
            margin-right: 6px;
            background: var(--c);
        }

        /* ---------- SLIDE 3: defect ---------- */
        .s3 {
            display: grid;
            grid-template-columns: 1fr 520px;
            gap: 16px;
        }

        .defect-img-card,
        .defect-list-card {
            padding: 18px 22px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            min-height: 0;
        }

        /* gambar garment berupa sketsa garis di latar putih, jadi panelnya tetap terang */
        .defect-stage {
            position: relative;
            flex: 1 1 auto;
            min-height: 0;
            border-radius: 12px;
            border: 1px solid rgba(103, 232, 249, .35);
            box-shadow: inset 0 0 30px rgba(34, 211, 238, .15);
            background: repeating-linear-gradient(0deg, transparent 0 39px, #e8eef7 39px 40px),
                repeating-linear-gradient(90deg, transparent 0 39px, #e8eef7 39px 40px), #f8fbff;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .defect-frame {
            position: absolute;
            inset: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity .6s ease;
        }

        .defect-frame.active {
            opacity: 1;
        }

        .defect-frame .holder {
            position: relative;
            height: 100%;
        }

        .defect-frame img {
            height: 100%;
            width: auto;
            display: block;
            opacity: .85;
        }

        .defect-point {
            position: absolute;
            width: 3%;
            aspect-ratio: 1;
            border-radius: 50%;
            transform: translate(-50%, -50%);
            opacity: .9;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, .6), 0 0 10px rgba(0, 0, 0, .25);
        }

        .img-dots {
            display: flex;
            gap: 6px;
        }

        .img-dots i {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--dim);
        }

        .img-dots i.on {
            background: var(--cyan);
            box-shadow: 0 0 6px var(--cyan);
        }

        .empty {
            margin: auto;
            text-align: center;
            color: var(--muted);
            font-size: 20px;
            font-weight: 700;
        }

        .empty i {
            display: block;
            font-size: 44px;
            margin-bottom: 12px;
            color: var(--dim);
        }

        .defect-stage .empty,
        .defect-stage .empty i {
            color: #94a3b8;
        }

        /* selalu 5 baris dibagi rata setinggi kartu */
        .defect-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            flex: 1 1 auto;
            min-height: 0;
        }

        .defect-item {
            flex: 1 1 0;
            min-height: 0;
            display: grid;
            grid-template-columns: 44px 1fr auto;
            align-items: center;
            gap: 6px 14px;
            padding: 14px 16px;
            background: var(--row);
            border: 1px solid rgba(15, 31, 61, .07);
            border-radius: 12px;
        }

        .defect-item .rank {
            grid-row: span 2;
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            font-size: 20px;
            font-weight: 800;
            color: #0f1f3d;
        }

        .defect-item.blank {
            opacity: .45;
        }

        .defect-item .name {
            font-size: 24px;
            font-weight: 700;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .defect-item .count {
            grid-row: span 2;
            font-size: 36px;
            font-weight: 800;
        }

        .defect-item .bar {
            width: 100%;
        }

        /* ---------- SLIDE 4: projection ---------- */
        .s4 {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 16px;
        }

        .proj-chart-card {
            padding: 18px 22px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            min-height: 0;
        }

        .proj-chart {
            flex: 1 1 auto;
            min-height: 0;
        }

        .proj-chart svg {
            display: block;
            width: 100%;
            height: 100%;
        }

        .legend i.line-dash {
            height: 4px;
            background: repeating-linear-gradient(90deg, currentColor 0 4px, transparent 4px 7px);
        }

        .proj-stack {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .proj-card {
            flex: 1 1 0;
            padding: 18px 22px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 6px;
        }

        .proj-card .k {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 16px;
            font-weight: 700;
            color: var(--muted);
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .proj-card .v {
            font-size: 56px;
            font-weight: 800;
            line-height: 1.05;
        }

        .proj-card .v small {
            font-size: 20px;
            font-weight: 700;
            color: var(--muted);
            margin-left: 4px;
        }

        .proj-card .f {
            font-size: 14px;
            font-weight: 600;
            color: var(--dim);
        }

        .proj-card .note {
            font-size: 15px;
            font-weight: 800;
        }

        .t-good {
            color: var(--good);
        }

        .t-bad {
            color: var(--bad);
        }

        /* ---------- STATUS (di sebelah jam) ---------- */
        .status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            font-weight: 800;
            letter-spacing: .08em;
            color: var(--good);
        }

        .status.offline {
            color: var(--bad);
        }

        /* jam data terakhir di bawah tulisan OFFLINE */
        .status small {
            display: block;
            font-size: 12px;
            letter-spacing: .02em;
            line-height: 1.1;
        }

        /* lampu berdenyut pakai opacity (ringan), bukan box-shadow yang digambar ulang tiap frame */
        .status .led {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--good);
            animation: pulse 2s ease-in-out infinite;
        }

        .status.offline .led {
            background: var(--bad);
            animation: none;
        }

        @keyframes pulse {
            50% {
                opacity: .35;
            }
        }

        /* ---------- MODE LITE (route wip-line-smart-lite): untuk TV spek rendah, semua animasi & efek cahaya/bayangan dimatikan ---------- */
        .lite *,
        .lite *::before,
        .lite *::after {
            animation: none !important;
            transition: none !important;
            box-shadow: none !important;
            filter: none !important;
        }

        .lite .card {
            box-shadow: 0 1px 2px rgba(15, 31, 61, .08) !important;
        }

        /* tanpa animasi, teks panjang tidak berjalan: tampil dari kiri & terpotong di ujung kartu */
        .lite .scroll-text.marquee {
            padding-left: 0;
        }

        /* slide tidak aktif dikeluarkan dari render (display:none), jadi ganti slide = langsung tampil tanpa efek */
        .lite .slide {
            opacity: 1;
            visibility: visible;
            transform: none;
        }

        .lite .slide:not(.active),
        .lite .defect-frame:not(.active) {
            display: none;
        }

        .lite .defect-frame.active,
        .lite .defect-frame img,
        .lite .defect-point {
            opacity: 1;
        }

        /* gradient & dekorasi diganti warna polos: lebih murah digambar ulang di kanvas yang di-scale */
        .lite body {
            background: var(--bg);
        }

        .lite .order .circuit,
        .lite .hud::before,
        .lite .hud::after {
            display: none;
        }

        .lite .kpi.target {
            background: #0891b2;
        }

        .lite .kpi.output {
            background: #0f766e;
        }

        .lite .kpi.variation {
            background: #6d28d9;
        }

        .lite .kpi.eff {
            background: #15803d;
        }

        .lite .kpi.defect {
            background: #b91c1c;
        }

        .lite .hourly tr.break {
            background: #fee2e2;
        }

        .lite .hourly tr.current {
            background: #dff7fc;
        }

        .lite .defect-stage {
            background: #f8fbff;
        }
    </style>
</head>

<body>
    <div id="stage">
        <header class="header">
            <div class="card brand">
                <img src="{{ asset('dist/img/logo-nds4.png') }}" alt="NDS">
                <div>
                    <div class="eyebrow">Sewing · End Line</div>
                    <div class="line-name" id="line-name">-</div>
                </div>
            </div>
            <div class="card order">
                {{-- dekorasi jalur sirkuit --}}
                <svg class="circuit" viewBox="0 0 400 96" preserveAspectRatio="none" fill="none" stroke="#22d3ee"
                    stroke-width="1.5">
                    <path d="M0 30 H120 L140 14 H260 L280 30 H400" />
                    <path d="M40 70 H150 L170 52 H230" />
                    <path d="M250 70 H330 L350 84 H400" />
                    <path d="M300 30 V52 H360" />
                    <circle cx="230" cy="52" r="3" fill="#22d3ee" />
                    <circle cx="360" cy="52" r="3" fill="#22d3ee" />
                    <circle cx="120" cy="30" r="3" fill="#22d3ee" />
                </svg>
                <div class="eyebrow" style="position:relative">Buyer · WS</div>
                <div class="buyer"><span class="scroll-text" id="buyer">-</span></div>
                <div class="ws"><span class="scroll-text" id="ws">-</span></div>
            </div>
            <div class="card clock">
                <div class="time"><span id="clock-time">--:--:--</span>
                    <span class="status" id="status"><span class="led"></span><span
                            id="status-text">…</span></span>
                </div>
                <div class="date" id="clock-date">-</div>
            </div>
            <div class="card worktime">
                <div class="eyebrow">Jam kerja berjalan</div>
                <div class="value" id="worktime">0<small>jam</small>0<small>menit</small></div>
                <div class="shift-bar"><i id="worktime-bar"></i></div>
            </div>
        </header>

        <main class="slides">
            {{-- SLIDE 1: ringkasan per jam --}}
            <section class="slide s1">
                <div class="card hud hourly-card">
                    <div class="section-title"><i class="fa-solid fa-clock"></i> Output per jam
                        <span class="meta" id="hourly-meta"></span>
                    </div>
                    <table class="hourly">
                        <thead>
                            <tr>
                                <th style="text-align:left;padding-left:18px">Hours</th>
                                <th>Target</th>
                                <th>Output</th>
                                <th>Variation</th>
                                <th>Efficiency</th>
                                <th>Defect Rate</th>
                            </tr>
                        </thead>
                        <tbody id="hourly-body"></tbody>
                    </table>
                </div>
                <div class="kpi-stack">
                    <div class="card kpi target">
                        <div class="ico"><i class="fa-solid fa-bullseye"></i></div>
                        <div class="text">
                            <div class="label">Target</div>
                            <div class="value" id="k-target">0</div>
                            <div class="sub proj" id="k-proj"></div>
                        </div>
                    </div>
                    <div class="card kpi output">
                        <div class="ico"><i class="fa-solid fa-shirt"></i></div>
                        <div class="text">
                            <div class="label">Output</div>
                            <div class="value" id="k-output">0</div>
                        </div>
                        <div class="viz"><svg id="k-output-viz" viewBox="0 0 120 70"></svg></div>
                    </div>
                    <div class="card kpi variation">
                        <div class="ico"><i class="fa-solid fa-scale-unbalanced"></i></div>
                        <div class="text">
                            <div class="label">Variation</div>
                            <div class="value" id="k-variation">0</div>
                        </div>
                        <div class="viz"><svg id="k-variation-viz" viewBox="0 0 120 70"></svg></div>
                    </div>
                    <div class="card kpi eff">
                        <div class="ico"><i class="fa-solid fa-gauge-high"></i></div>
                        <div class="text">
                            <div class="label">Efficiency</div>
                            <div class="value" id="k-eff">0<small>%</small></div>
                        </div>
                        <div class="viz"><svg id="k-eff-viz" viewBox="0 0 70 70" style="width:70px"></svg></div>
                    </div>
                    <div class="card kpi defect">
                        <div class="ico"><i class="fa-solid fa-triangle-exclamation"></i></div>
                        <div class="text">
                            <div class="label">Defect Rate</div>
                            <div class="value" id="k-defrate">0<small>%</small></div>
                        </div>
                        <div class="viz"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    </div>
                </div>
            </section>

            {{-- SLIDE 2: KPI hari ini + gauge --}}
            <section class="slide s2">
                <div class="row3">
                    <div class="card big-card">
                        <div class="head">
                            <div class="section-title"><i class="fa-solid fa-industry"></i> Produksi hari ini</div>
                            <span class="pill" id="p-variation"></span>
                        </div>
                        <div class="hero">
                            <div class="ico c-cyan"><i class="fa-solid fa-shirt"></i></div>
                            <div>
                                <div class="num" id="b-actual">0</div>
                                <div class="cap">Actual</div>
                            </div>
                        </div>
                        <div class="progress" title="actual vs day target">
                            <i id="b-actual-bar" style="width:0"></i>
                            <span class="marker" id="b-realtime-marker" style="left:0"></span>
                        </div>
                        <div class="legend">
                            <span><i class="tone-good"></i>&ge; Realtime</span>
                            <span><i class="tone-bad"></i>&lt; Realtime</span>
                            <span><i class="mk"></i>Realtime target</span>
                        </div>
                        <div class="split">
                            <div>
                                <div class="k">Day target</div>
                                <div class="v" id="b-day-target">0</div>
                            </div>
                            <div>
                                <div class="k">Realtime target</div>
                                <div class="v" id="b-realtime-target">0</div>
                            </div>
                        </div>
                    </div>
                    <div class="card big-card">
                        <div class="head">
                            <div class="section-title"><i class="fa-solid fa-stopwatch"></i> Jam ini
                                <span class="meta" id="b-periode" style="margin-left:6px"></span>
                            </div>
                            <span class="pill" id="p-hour"></span>
                        </div>
                        <div class="hero">
                            <div class="ico c-blue"><i class="fa-solid fa-bolt"></i></div>
                            <div>
                                <div class="num" id="b-hour-output">0</div>
                                <div class="cap">Hour output</div>
                            </div>
                        </div>
                        <div class="progress"><i id="b-hour-bar" style="width:0"></i></div>
                        <div class="legend">
                            <span><i class="tone-good"></i>&ge; 85%</span>
                            <span><i class="tone-warn"></i>75 – 84%</span>
                            <span><i class="tone-bad"></i>&lt; 75%</span>
                        </div>
                        <div class="split">
                            <div>
                                <div class="k">Req hour target</div>
                                <div class="v" id="b-req-hour">0</div>
                            </div>
                            <div>
                                <div class="k">Pencapaian</div>
                                <div class="v" id="b-hour-pct">0<small style="font-size:20px">%</small></div>
                            </div>
                        </div>
                    </div>
                    <div class="card big-card">
                        <div class="head">
                            <div class="section-title"><i class="fa-solid fa-shield-halved"></i> Kualitas</div>
                        </div>
                        <div class="hero">
                            <div class="ico c-amber"><i class="fa-solid fa-shirt"></i></div>
                            <div>
                                <div class="num" id="b-defect-qty">0</div>
                                <div class="cap">Defect garment qty</div>
                            </div>
                        </div>
                        <div class="progress" title="defect rate (skala 0 - 5%)"><i id="b-defect-bar"
                                style="width:0"></i></div>
                        <div class="legend">
                            <span><i class="tone-good"></i>&lt; 2%</span>
                            <span><i class="tone-warn"></i>2 – 3%</span>
                            <span><i class="tone-bad"></i>&ge; 3%</span>
                        </div>
                        <div class="split">
                            <div>
                                <div class="k">Rework output</div>
                                <div class="v" id="b-rework">0</div>
                            </div>
                            <div>
                                <div class="k">Defect rate</div>
                                <div class="v" id="b-defrate">0<small style="font-size:20px">%</small></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row3">
                    <div class="card gauge-card">
                        <div class="section-title"><i class="fa-solid fa-gauge-high"></i> Efficiency</div>
                        <div class="gauge" id="g-eff"></div>
                        <div class="gauge-legend">
                            <span style="--c:var(--bad)">&lt;50</span><span style="--c:#f59e0b">50–75</span>
                            <span style="--c:#4ade80">75–85</span><span style="--c:var(--good)">≥85</span>
                        </div>
                    </div>
                    <div class="card gauge-card">
                        <div class="section-title"><i class="fa-solid fa-circle-check"></i> RFT</div>
                        <div class="gauge" id="g-rft"></div>
                        <div class="gauge-legend">
                            <span style="--c:var(--bad)">&lt;97</span><span style="--c:#f59e0b">97–98</span>
                            <span style="--c:var(--good)">≥98</span>
                        </div>
                    </div>
                    <div class="card gauge-card">
                        <div class="section-title"><i class="fa-solid fa-triangle-exclamation"></i> Defect rate</div>
                        <div class="gauge" id="g-defect"></div>
                        <div class="gauge-legend">
                            <span style="--c:var(--good)">&lt;2</span><span style="--c:#f59e0b">2–3</span>
                            <span style="--c:var(--bad)">≥3</span>
                        </div>
                    </div>
                </div>
            </section>

            {{-- SLIDE 3: defect --}}
            <section class="slide s3">
                <div class="card hud defect-img-card">
                    <div class="section-title"><i class="fa-solid fa-location-crosshairs"></i> Posisi defect
                        <span class="meta img-dots" id="img-dots"></span>
                    </div>
                    <div class="defect-stage" id="defect-stage"></div>
                </div>
                <div class="card defect-list-card">
                    <div class="section-title"><i class="fa-solid fa-ranking-star"></i> Top 5 defect</div>
                    <div class="defect-list" id="defect-list"></div>
                </div>
            </section>

            {{-- SLIDE 4: projection akhir shift --}}
            <section class="slide s4">
                <div class="card hud proj-chart-card">
                    <div class="section-title"><i class="fa-solid fa-chart-line"></i> Projection akhir shift
                        <span class="meta legend" style="margin-top:0">
                            <span style="color:var(--brand)"><i style="background:currentColor"></i>Output
                                aktual</span>
                            <span id="proj-legend-color"><i class="line-dash"></i>Projection</span>
                            <span style="color:var(--dim)"><i class="line-dash"></i>Target</span>
                        </span>
                    </div>
                    <div class="proj-chart" id="proj-chart"></div>
                </div>
                <div class="proj-stack">
                    <div class="card proj-card">
                        <div class="k">Projection <span class="pill" id="proj-gap"></span></div>
                        <div class="v" id="proj-value">-</div>
                        <div class="f">Output ÷ menit kerja berjalan × total menit kerja</div>
                    </div>
                    <div class="card proj-card">
                        <div class="k">Kecepatan sekarang</div>
                        <div class="v" id="proj-rate-now">-</div>
                        <div class="f">Output ÷ jam kerja berjalan</div>
                    </div>
                    <div class="card proj-card">
                        <div class="k">Kecepatan dibutuhkan</div>
                        <div class="v" id="proj-rate-need">-</div>
                        <div class="note" id="proj-rate-note"></div>
                        <div class="f" id="proj-rate-formula">(Target − output) ÷ sisa jam kerja</div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script>
        const lineId = @json($id);
        const params = new URLSearchParams(location.search);
        const testJam = params.get('jam'); // TEST JAM: ?jam=HH:MM menggantikan jam server
        // class lite dipasang dari route wip-line-smart-lite
        const LITE = document.documentElement.classList.contains('lite');
        const SLIDE_MS = 15000;
        const REFRESH_MS = 60000;
        const DEFECT_COLORS = ['#ec3032', '#fd8024', '#fffb45', '#2bff6b', '#c084fc'];
        const DEFECT_BORDERS = ['#b50204', '#da5d02', '#d0cb02', '#00c43b', '#9333ea'];
        const IMG_BASE = location.protocol !== 'https:' ?
            'http://10.10.5.62:8080/erp/pages/prod_new/upload_files/' :
            'https://10.10.5.62:8143/erp/pages/prod_new/upload_files/';

        const $ = id => document.getElementById(id);
        const num = v => Number(v || 0);
        const fmt = v => num(v).toLocaleString('id-ID');
        const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        })[c]);
        const effTone = v => v >= 85 ? 'good' : (v >= 75 ? 'warn' : 'bad');
        let shift = null; // jam mulai & total menit kerja dari summary, untuk renderWorktime()
        let lastWorktime = null; // menit terakhir yang sudah ditampilkan
        let lastProj = null; // argumen drawProjChart terakhir, untuk gambar ulang saat resize / slide 4 tampil
        let imgTimer = null; // pergantian gambar defect, hanya jalan saat slide 3 tampil
        let imgFrames = [],
            imgDots = [];
        const worktimeFmt = new Intl.DateTimeFormat('en-US', {
            timeZone: 'Asia/Jakarta',
            weekday: 'short',
            hour: 'numeric',
            minute: 'numeric',
            hourCycle: 'h23'
        });

        // ---------- skala kanvas ----------
        function fitStage() {
            const s = Math.min(innerWidth / 1600, innerHeight / 900);
            const stage = $('stage');
            stage.style.width = innerWidth / s + 'px';
            stage.style.height = innerHeight / s + 'px';
            stage.style.transform = `scale(${s})`;
        }
        addEventListener('resize', fitStage);
        fitStage();

        // ---------- jam ----------
        const timeFmt = new Intl.DateTimeFormat('id-ID', {
            timeZone: 'Asia/Jakarta',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        });
        const dateFmt = new Intl.DateTimeFormat('id-ID', {
            timeZone: 'Asia/Jakarta',
            weekday: 'long',
            day: '2-digit',
            month: 'long',
            year: 'numeric'
        });

        // DOM hanya ditulis saat teksnya berubah; mode lite tanpa detik supaya jam cukup digambar ulang tiap menit
        function tick() {
            const now = new Date();
            const time = timeFmt.format(now).replace(/\./g, ':');
            const clock = LITE ? time.slice(0, 5) : time;
            const date = dateFmt.format(now);
            if ($('clock-time').textContent !== clock) $('clock-time').textContent = clock;
            if ($('clock-date').textContent !== date) $('clock-date').textContent = date;
            renderWorktime();
        }
        tick();
        setInterval(tick, 1000);

        // ---------- slide ----------
        const slides = [...document.querySelectorAll('.slide')];
        // ?slide=1..4 mengunci slide (untuk cek tampilan)
        const lockedSlide = params.has('slide') ? Math.min(Math.max(parseInt(params.get('slide'), 10) - 1 || 0, 0), slides
            .length - 1) : null;
        let slideIndex = lockedSlide ?? 0;

        function showSlide(i) {
            slideIndex = i;
            slides.forEach((s, k) => s.classList.toggle('active', k === i));
            // mode lite: slide tersembunyi display:none (lebar 0), jadi grafik projection digambar saat slide-nya tampil
            if (lastProj && slides[i].classList.contains('s4')) drawProjChart($('proj-chart'), ...lastProj);
            startImgCycle();
        }
        // auto slide; diganti manual (geser/panah) -> hitungan 15 detik mulai ulang
        let slideTimer = null;

        function goSlide(i) {
            showSlide((i + slides.length) % slides.length);
            clearInterval(slideTimer);
            if (lockedSlide === null) slideTimer = setInterval(() => showSlide((slideIndex + 1) % slides.length), SLIDE_MS);
        }
        goSlide(slideIndex);

        addEventListener('keydown', e => {
            if (e.key === 'ArrowRight') goSlide(slideIndex + 1);
            if (e.key === 'ArrowLeft') goSlide(slideIndex - 1);
        });

        // geser (touch / drag mouse): kiri = slide berikutnya, kanan = sebelumnya
        const slidesEl = document.querySelector('.slides');
        let dragX = null,
            dragY = null;
        slidesEl.addEventListener('pointerdown', e => {
            dragX = e.clientX;
            dragY = e.clientY;
        });
        addEventListener('pointerup', e => {
            if (dragX === null) return;
            const dx = e.clientX - dragX,
                dy = e.clientY - dragY;
            dragX = null;
            if (Math.abs(dx) > 60 && Math.abs(dx) > Math.abs(dy)) goSlide(slideIndex + (dx < 0 ? 1 : -1));
        });
        addEventListener('pointercancel', () => dragX = null);

        // ---------- mini chart SVG (data asli dari tabel per jam) ----------
        // posisi "sekarang" dalam satuan slot jam (mis. 3.07 = jam ke-4 baru berjalan 7%),
        // bagian jam berjalan diambil dari realtime target yang dihitung per menit
        function nowSlotX(slots, done, realtime) {
            if (!done) return 0;
            const cur = done - 1,
                before = slots.slice(0, cur).reduce((a, r) => a + num(r.target), 0),
                tgt = num(slots[cur].target);
            return cur + (tgt > 0 ? Math.min(Math.max((realtime - before) / tgt, 0), 1) : 1);
        }

        // bar +/- per jam dengan garis nol putus-putus: ke atas = lebih dari target, ke bawah = kurang
        // values = semua slot jam kerja, doneCount = jumlah jam yang sudah terjadi (sisanya tampil tipis)
        function zeroBars(el, values, doneCount, color) {
            const w = el.viewBox.baseVal.width,
                h = el.viewBox.baseVal.height,
                pad = 4;
            if (!values.length) return el.innerHTML = '';
            const done = values.slice(0, doneCount),
                min = Math.min(0, ...done),
                max = Math.max(0, ...done),
                span = (max - min) || 1;
            const Y = v => h - pad - (v - min) / span * (h - pad * 2);
            const y0 = Y(0),
                gap = 4,
                bw = (w - gap * (values.length - 1)) / values.length;
            el.innerHTML = values.map((v, i) => {
                    const future = i >= doneCount;
                    const top = future ? y0 - 1.5 : Math.min(Y(v), y0 - 1.5),
                        bh = future ? 3 : Math.max(3, Math.abs(Y(v) - y0));
                    const op = future ? .2 : (i === doneCount - 1 ? 1 : .6);
                    return `<rect x="${(i * (bw + gap)).toFixed(1)}" y="${(v < 0 && !future ? y0 : top).toFixed(1)}" width="${bw.toFixed(1)}" height="${bh.toFixed(1)}"
                    rx="2" fill="${color}" opacity="${op}"/>`;
                }).join('') +
                `<line x1="0" x2="${w}" y1="${y0.toFixed(1)}" y2="${y0.toFixed(1)}" stroke="${color}" stroke-width="1.5" stroke-dasharray="4 3" opacity=".8"/>
                <text x="${w + 3}" y="${y0.toFixed(1)}" dy="4" font-size="11" font-weight="700" fill="${color}">0</text>`;
        }

        // doneCount = jumlah jam yang sudah terjadi; bar terakhir yang terjadi paling terang
        function bars(el, values, color, doneCount) {
            const w = el.viewBox.baseVal.width,
                h = el.viewBox.baseVal.height;
            if (!values.length) return el.innerHTML = '';
            const max = Math.max(1, ...values),
                gap = 4,
                bw = (w - gap * (values.length - 1)) / values.length;
            el.innerHTML = values.map((v, i) => {
                const future = i >= doneCount;
                const bh = future ? 3 : Math.max(3, v / max * (h - 4));
                const op = future ? .2 : (i === doneCount - 1 ? 1 : .55);
                return `<rect x="${(i * (bw + gap)).toFixed(1)}" y="${(h - bh).toFixed(1)}" width="${bw.toFixed(1)}" height="${bh.toFixed(1)}"
                    rx="2" fill="${color}" opacity="${op}"/>`;
            }).join('');
        }

        function ring(el, pct, color) {
            const r = 28,
                c = 2 * Math.PI * r,
                p = Math.min(Math.max(pct, 0), 100);
            el.innerHTML = `<circle cx="35" cy="35" r="${r}" fill="none" stroke="rgba(255,255,255,.25)" stroke-width="7"/>
                <circle cx="35" cy="35" r="${r}" fill="none" stroke="${color}" stroke-width="7" stroke-linecap="round"
                    stroke-dasharray="${(p / 100 * c).toFixed(1)} ${c.toFixed(1)}" transform="rotate(-90 35 35)"
                    style="filter:drop-shadow(0 0 5px ${color})"/>`;
        }

        // ---------- gauge SVG (dibuat sekali, nilai di-update) ----------
        // busur 240 derajat, pathLength=100 supaya dasharray langsung dalam persen
        const ARC = (() => {
            const r = 120,
                cx = 150,
                cy = 150,
                a0 = 150,
                a1 = 390;
            const pt = a => [cx + r * Math.cos(a * Math.PI / 180), cy + r * Math.sin(a * Math.PI / 180)];
            const [x0, y0] = pt(a0),
                [x1, y1] = pt(a1);
            return `M ${x0} ${y0} A ${r} ${r} 0 1 1 ${x1} ${y1}`;
        })();

        function createGauge(id, zones, unit) {
            const el = $(id);
            const bands = zones.map(z =>
                `<path d="${ARC}" pathLength="100" fill="none" stroke="${z.color}" stroke-width="6" stroke-linecap="butt"
                    stroke-dasharray="${Math.max(z.to - z.from - .6, 0)} 100" stroke-dashoffset="${-z.from}"
                    style="transform-origin:150px 150px;transform:scale(1.2)"/>`).join(
                ''); // zona warna di luar busur utama
            el.innerHTML = `
                <svg viewBox="0 0 300 260">
                    ${bands}
                    <path d="${ARC}" pathLength="100" fill="none" stroke="rgba(15,31,61,.07)" stroke-width="26" stroke-linecap="round"/>
                    <path class="g-value" d="${ARC}" pathLength="100" fill="none" stroke="#cbd5e1" stroke-width="26"
                        stroke-linecap="round" stroke-dasharray="0 100"/>
                    <text x="34" y="250" font-size="14" font-weight="700" fill="#64748b" text-anchor="middle">0</text>
                    <text x="266" y="250" font-size="14" font-weight="700" fill="#64748b" text-anchor="middle">100</text>
                </svg>
                <div class="center"><div class="n">0<small>${unit}</small></div></div>`;
            return {
                arc: el.querySelector('.g-value'),
                label: el.querySelector('.n'),
                zones,
                unit,
                last: null
            };
        }

        function setGauge(g, value) {
            if (g.last === value) return;
            g.last = value;
            const pct = Math.min(Math.max(value, 0), 100);
            const zone = g.zones.find(z => pct >= z.from && pct < z.to) || g.zones[g.zones.length - 1];
            g.arc.setAttribute('stroke-dasharray', `${pct} 100`);
            g.arc.setAttribute('stroke', zone.color);
            g.arc.style.filter = `drop-shadow(0 0 6px ${zone.color}66)`;
            // angka asli tetap ditampilkan walau > 100 (busur dibatasi 0-100)
            g.label.innerHTML = `${value.toLocaleString('id-ID', { maximumFractionDigits: 2 })}<small>${g.unit}</small>`;
        }

        const gauges = {
            eff: createGauge('g-eff', [{
                from: 0,
                to: 50,
                color: '#ef4444'
            }, {
                from: 50,
                to: 75,
                color: '#f59e0b'
            }, {
                from: 75,
                to: 85,
                color: '#4ade80'
            }, {
                from: 85,
                to: 100.01,
                color: '#22c55e'
            }], '%'),
            rft: createGauge('g-rft', [{
                from: 0,
                to: 97,
                color: '#ef4444'
            }, {
                from: 97,
                to: 98,
                color: '#f59e0b'
            }, {
                from: 98,
                to: 100.01,
                color: '#22c55e'
            }], '%'),
            defect: createGauge('g-defect', [{
                from: 0,
                to: 2,
                color: '#22c55e'
            }, {
                from: 2,
                to: 3,
                color: '#f59e0b'
            }, {
                from: 3,
                to: 100.01,
                color: '#ef4444'
            }], '%'),
        };

        // ---------- render ----------
        function varHtml(diff) {
            // diff > 0 = lebih dari target
            const up = diff >= 0;
            return `<span class="var ${up ? 'up' : 'down'}">${fmt(Math.abs(diff))}<i class="fa-solid ${up ? 'fa-caret-up' : 'fa-caret-down'}"></i></span>`;
        }

        function setPill(el, diff) {
            const up = diff >= 0;
            el.className = `pill ${up ? 'good' : 'bad'}`;
            el.innerHTML = `<i class="fa-solid ${up ? 'fa-arrow-up' : 'fa-arrow-down'}"></i> ${fmt(Math.abs(diff))}`;
        }

        function setScrollText(el, text) {
            el.textContent = text;
            el.classList.remove('marquee');
            if (el.scrollWidth > el.parentElement.clientWidth) el.classList.add('marquee');
        }

        // baris per jam yang sudah terjadi (bukan istirahat, sampai jam berjalan)
        function pastRows(data) {
            const rows = data.hourly || [];
            const currentStart = ((data.summary || {}).periode_jam || '').slice(0, 5);
            let currentIndex = currentStart ? rows.findIndex(r => r.hours.slice(0, 5) === currentStart) : -1;
            if (currentStart && currentIndex < 0) currentIndex = rows.length - 1;
            return rows.filter((r, i) => num(r.menit_kerja) > 0 || num(r.output) > 0).filter(r => currentIndex < 0 || rows
                .indexOf(r) <= currentIndex);
        }

        function renderHeader(data) {
            const line = String(lineId || '').replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
            $('line-name').textContent = line || '-';
            const buyer = data.buyer || '-';
            const ws = data.no_ws || '-';
            if ($('buyer').textContent !== buyer) setScrollText($('buyer'), buyer);
            if ($('ws').textContent !== ws) setScrollText($('ws'), ws);

            const s = data.summary || {};
            const [h, m] = String(s.jam_kerja_awal || '').split(':').map(Number);
            shift = {
                menit: num(s.menit_kerja),
                startM: s.jam_kerja_awal ? h * 60 + m : null,
                totalM: Math.round(num(s.jam_kerja) * 60)
            };
            lastWorktime = null; // data baru (mis. total jam kerja berubah) -> tulis ulang
            renderWorktime();
        }

        // jam kerja berjalan dihitung tiap detik dari jam browser (rumus sama dengan menit_kerja di cari_summary_line),
        // supaya tidak tertinggal sampai 1 menit menunggu refresh data
        function renderWorktime() {
            if (!shift) return;
            let menit = shift.menit;
            if (!testJam && shift.startM !== null) {
                const p = Object.fromEntries(worktimeFmt.formatToParts(new Date()).map(x => [x.type, x.value]));
                const nowM = Number(p.hour) * 60 + Number(p.minute);
                const friday = p.weekday === 'Fri';
                const cut = (s, e) => Math.max(0, Math.min(nowM, e) - Math.max(shift.startM, s));
                menit = Math.min(Math.max(0, Math.max(0, nowM - shift.startM) - cut(friday ? 690 : 720, friday ? 750 :
                    780) - cut(1080, 1110)), shift.totalM);
            }
            // dipanggil tiap detik, tapi DOM hanya ditulis saat menitnya berubah
            if (menit === lastWorktime) return;
            lastWorktime = menit;
            $('worktime').innerHTML = `${Math.floor(menit / 60)}<small>jam</small>${menit % 60}<small>menit</small>`;
            $('worktime-bar').style.width = (shift.totalM ? Math.min(menit / shift.totalM * 100, 100) : 0) + '%';
        }

        function renderHourly(data) {
            const rows = data.hourly || [];
            const lastWorkIndex = rows.map(r => num(r.menit_kerja) > 0).lastIndexOf(true);
            // jam berjalan ikut periode_jam summary; kalau baris setelah shift tidak tampil, tandai baris terakhir
            const currentStart = ((data.summary || {}).periode_jam || '').slice(0, 5);
            let currentIndex = currentStart ? rows.findIndex(r => r.hours.slice(0, 5) === currentStart) : -1;
            if (currentStart && currentIndex < 0) currentIndex = rows.length - 1;

            $('hourly-body').innerHTML = rows.map((row, i) => {
                const isBreak = num(row.menit_kerja) === 0 && i < lastWorkIndex;
                const eff = num(row.efficiency);
                let cls = '';
                if (isBreak) cls = 'break';
                else if (i === currentIndex) cls = 'current';
                else if (currentIndex >= 0 && i > currentIndex) cls = 'future';

                if (isBreak) {
                    return `<tr class="break"><td><span class="dot" style="background:var(--bad);box-shadow:0 0 8px var(--bad)"></span>${row.hours}</td>
                        <td colspan="5" style="letter-spacing:.2em;font-size:18px"><i class="fa-solid fa-circle-pause"></i>&nbsp; ISTIRAHAT</td></tr>`;
                }
                const future = cls === 'future';
                const dot = future ? '' : (num(row.output) >= num(row.target) ? 'good' : 'bad');
                const nowTag = cls === 'current' ?
                    `<span class="now-tag"><b>SEKARANG</b><span class="eq"><i></i><i></i><i></i><i></i><i></i></span></span>` :
                    '';
                return `<tr class="${cls}">
                    <td><span class="dot ${dot}"></span>${row.hours}${nowTag}</td>
                    <td>${fmt(row.target)}</td>
                    <td>${fmt(row.output)}</td>
                    <td>${future ? '-' : varHtml(num(row.variation))}</td>
                    <td><div class="effcell"><span>${eff.toFixed(2)} %</span>
                        <div class="bar"><i class="tone-${effTone(eff)}" style="width:${Math.min(eff, 100)}%"></i></div></div></td>
                    <td>${num(row.defect_rate).toFixed(2)} %</td>
                </tr>`;
            }).join('');
            $('hourly-meta').textContent = rows.length ?
                `${rows[0].hours.slice(0, 5)} – ${rows[rows.length - 1].hours.slice(-5)}` : 'Belum ada plan';
        }

        // proyeksi akhir shift = laju output sejauh ini x total menit kerja plan; null kalau belum ada menit kerja
        function projection(s) {
            const menit = num(s.menit_kerja),
                totalMenit = num(s.jam_kerja) * 60;
            if (!(menit > 0 && totalMenit > 0)) return null;
            const proj = Math.round(num(s.tot_output) / menit * totalMenit);
            return {
                proj,
                gap: proj - num(s.target)
            };
        }

        function renderKpi(data) {
            const s = data.summary || {};
            const target = num(s.target),
                actual = num(s.tot_output),
                realtime = num(s.target_skrg);
            const variation = num(s.variation); // day target - output, positif = kurang
            $('k-target').textContent = fmt(s.target);
            $('k-output').textContent = fmt(s.tot_output);
            $('k-eff').innerHTML = `${num(s.eff).toFixed(2)}<small>%</small>`;
            $('k-defrate').innerHTML = `${num(s.def_rate).toFixed(2)}<small>%</small>`;

            const p = projection(s);
            $('k-proj').innerHTML = p ?
                `PROJECTION: <b>${fmt(p.proj)}</b> <span class="${p.gap < 0 ? 'neg' : 'pos'}">(${p.gap < 0 ? 'SHORTFALL' : 'SURPLUS'} ${p.gap < 0 ? '-' : '+'}${fmt(Math.abs(p.gap))})</span>` :
                '';

            // visual kartu dari data per jam: bar selalu selebar semua jam kerja (jam yang belum terjadi tampil tipis)
            const past = pastRows(data);
            const slots = (data.hourly || []).filter(r => num(r.menit_kerja) > 0 || num(r.output) > 0);
            bars($('k-output-viz'), slots.map(r => num(r.output)), '#ffffff', past.length);
            // angka kartu = day target - output; bar = kolom Variation tabel per jam (jam berjalan juga vs target jam penuh)
            $('k-variation').innerHTML = varHtml(-variation);
            const varDone = past.map(r => num(r.variation));
            zeroBars($('k-variation-viz'), slots.map((r, i) => varDone[i] ?? 0), past.length, '#ffffff');
            ring($('k-eff-viz'), num(s.eff), '#ffffff');

            // slide 2
            $('b-actual').textContent = fmt(actual);
            $('b-day-target').textContent = fmt(target);
            $('b-realtime-target').textContent = fmt(realtime);
            setPill($('p-variation'), -variation);
            const bar = $('b-actual-bar');
            bar.style.width = (target ? Math.min(actual / target * 100, 100) : 0) + '%';
            bar.className = actual >= realtime ? 'tone-good' : 'tone-bad';
            $('b-realtime-marker').style.left = (target ? Math.min(realtime / target * 100, 100) : 0) + '%';

            const req = num(s.req_hour_target),
                hourOut = num(s.output_jam_ini);
            $('b-periode').textContent = s.periode_jam || '';
            $('b-hour-output').textContent = fmt(hourOut);
            $('b-req-hour').textContent = fmt(req);
            setPill($('p-hour'), hourOut - req);
            const hourPct = req ? hourOut / req * 100 : 0;
            $('b-hour-pct').innerHTML = `${req ? hourPct.toFixed(0) : '-'}<small style="font-size:20px">%</small>`;
            const hbar = $('b-hour-bar');
            hbar.style.width = Math.min(hourPct, 100) + '%';
            hbar.className = 'tone-' + effTone(hourPct);

            $('b-defect-qty').textContent = fmt(s.tot_defect);
            $('b-rework').textContent = fmt(s.tot_reworked);
            // bar defect rate skala 0 - 5%, warna sama dengan gauge defect
            const defRate = num(s.def_rate);
            $('b-defrate').innerHTML = `${defRate.toFixed(2)}<small style="font-size:20px">%</small>`;
            const dbar = $('b-defect-bar');
            dbar.style.width = Math.min(defRate / 5 * 100, 100) + '%';
            dbar.className = 'tone-' + (defRate < 2 ? 'good' : (defRate < 3 ? 'warn' : 'bad'));

            setGauge(gauges.eff, num(s.eff));
            setGauge(gauges.rft, num(s.rft_pct));
            setGauge(gauges.defect, num(s.def_rate));
        }

        // slide 4: grafik kumulatif output aktual vs target, disambung garis projection sampai akhir shift
        function renderProjection(data) {
            const s = data.summary || {};
            const target = num(s.target),
                actual = num(s.tot_output);
            const menit = num(s.menit_kerja),
                totalMenit = num(s.jam_kerja) * 60,
                sisa = Math.max(totalMenit - menit, 0);
            const p = projection(s);
            const good = p ? p.gap >= 0 : actual >= target;
            const projColor = good ? 'var(--good)' : 'var(--bad)';

            $('proj-value').innerHTML = p ? `${fmt(p.proj)}<small>pcs</small>` : '-';
            if (p) {
                setPill($('proj-gap'), p.gap);
            } else {
                $('proj-gap').className = 'pill';
                $('proj-gap').innerHTML = '';
            }
            $('proj-value').className = 'v ' + (good ? 't-good' : 't-bad');
            $('proj-legend-color').style.color = projColor;

            const rateNow = menit > 0 ? actual / menit * 60 : 0;
            $('proj-rate-now').innerHTML = menit > 0 ? `${fmt(Math.round(rateNow))}<small>pcs/jam</small>` : '-';

            const sisaTarget = Math.max(target - actual, 0);
            const need = sisa > 0 ? sisaTarget / sisa * 60 : null;
            $('proj-rate-formula').textContent =
                `(Target − output) ÷ sisa jam kerja (${Math.floor(sisa / 60)} jam ${Math.round(sisa % 60)} menit)`;
            const note = $('proj-rate-note');
            if (sisaTarget === 0) {
                $('proj-rate-need').innerHTML = '0<small>pcs/jam</small>';
                note.className = 'note t-good';
                note.textContent = 'Day target sudah tercapai';
            } else if (need === null) {
                $('proj-rate-need').textContent = '-';
                note.className = 'note t-bad';
                note.textContent = 'Jam kerja sudah habis';
            } else {
                $('proj-rate-need').innerHTML = `${fmt(Math.ceil(need))}<small>pcs/jam</small>`;
                const diff = Math.ceil(need) - Math.round(rateNow);
                note.className = 'note ' + (diff > 0 ? 't-bad' : 't-good');
                note.textContent = diff > 0 ? `Perlu +${fmt(diff)} pcs/jam dari kecepatan sekarang` :
                    'Kecepatan sekarang sudah cukup';
            }

            lastProj = [data, p, projColor];
            drawProjChart($('proj-chart'), ...lastProj);
        }

        // ukuran SVG ikut ukuran kartu, jadi gambar ulang saat layar di-resize
        addEventListener('resize', () => lastProj && drawProjChart($('proj-chart'), ...lastProj));

        // sumbu X = slot jam kerja (lebar sama), posisi "sekarang" di dalam jam berjalan dari realtime target
        function drawProjChart(el, data, p, projColor) {
            const s = data.summary || {};
            const slots = (data.hourly || []).filter(r => num(r.menit_kerja) > 0 || num(r.output) > 0);
            const done = pastRows(data).length;
            const W = el.clientWidth,
                H = el.clientHeight;
            if (!slots.length || !W || !H) return el.innerHTML = '';

            const N = slots.length,
                actual = num(s.tot_output),
                realtime = num(s.target_skrg);
            const cumTarget = [0];
            slots.forEach(r => cumTarget.push(cumTarget[cumTarget.length - 1] + num(r.target)));

            const actualPts = [
                [0, 0]
            ];
            let cumOut = 0;
            for (let i = 0; i < done - 1; i++) actualPts.push([i + 1, cumOut += num(slots[i].output)]);
            const nowX = nowSlotX(slots, done, realtime);
            if (done > 0) actualPts.push([nowX, actual]);

            const top = Math.max(cumTarget[N], num(s.target), actual, p ? p.proj : 0, 1) * 1.08;
            const mag = Math.pow(10, Math.floor(Math.log10(top / 5)));
            const step = [1, 2, 5, 10].map(m => m * mag).find(v => top / v <= 6);
            const yMax = Math.ceil(top / step) * step;

            const L = 64,
                R = 130,
                T = 16,
                B = 40;
            const X = x => L + x / N * (W - L - R),
                Y = y => T + (1 - y / yMax) * (H - T - B);
            const path = pts => pts.map((pt, i) => `${i ? 'L' : 'M'}${X(pt[0]).toFixed(1)} ${Y(pt[1]).toFixed(1)}`).join(
                ' ');

            let svg = '';
            // grid + label sumbu Y
            for (let v = 0; v <= yMax; v += step) {
                svg +=
                    `<line x1="${L}" x2="${W - R}" y1="${Y(v)}" y2="${Y(v)}" stroke="rgba(15,31,61,.08)"/>
                    <text x="${L - 10}" y="${Y(v)}" dy="5" text-anchor="end" font-size="15" font-weight="700" fill="var(--muted)">${fmt(v)}</text>`;
            }
            // area jam yang belum terjadi
            svg +=
                `<rect x="${X(nowX)}" y="${T}" width="${X(N) - X(nowX)}" height="${H - T - B}" fill="rgba(15,31,61,.035)"/>`;
            // label jam di sumbu X
            slots.forEach((r, i) => {
                svg +=
                    `<text x="${X(i)}" y="${H - B + 24}" text-anchor="middle" font-size="15" font-weight="700" fill="var(--muted)">${esc(r.hours.slice(0, 5))}</text>`;
            });
            svg +=
                `<text x="${X(N)}" y="${H - B + 24}" text-anchor="middle" font-size="15" font-weight="700" fill="var(--muted)">${esc(slots[N - 1].hours.slice(-5))}</text>`;

            // target kumulatif
            const targetPts = cumTarget.map((v, i) => [i, v]);
            svg +=
                `<path d="${path(targetPts)}" fill="none" stroke="var(--dim)" stroke-width="3" stroke-dasharray="3 6" stroke-linecap="round"/>`;
            // projection
            if (p && done > 0) {
                svg += `<path d="${path([[nowX, actual], [N, p.proj]])}" fill="none" stroke="${projColor}" stroke-width="4" stroke-dasharray="10 8" stroke-linecap="round"/>
                    <circle cx="${X(N)}" cy="${Y(p.proj)}" r="6" fill="${projColor}"/>`;
            }
            // output aktual + area
            svg += `<path d="${path(actualPts)} L${X(nowX)} ${Y(0)} L${X(0)} ${Y(0)} Z" fill="var(--brand)" opacity=".1"/>
                <path d="${path(actualPts)}" fill="none" stroke="var(--brand)" stroke-width="5" stroke-linejoin="round" stroke-linecap="round"
                    style="filter:drop-shadow(0 0 5px rgba(37,99,235,.45))"/>`;
            if (done > 0) {
                svg +=
                    `<line x1="${X(nowX)}" x2="${X(nowX)}" y1="${T}" y2="${H - B}" stroke="var(--brand)" stroke-width="1.5" stroke-dasharray="4 4" opacity=".6"/>
                    <circle cx="${X(nowX)}" cy="${Y(actual)}" r="8" fill="#fff" stroke="var(--brand)" stroke-width="4"/>
                    <text x="${X(nowX)}" y="${Y(actual) - 18}" text-anchor="middle" font-size="22" font-weight="800" fill="var(--ink)">${fmt(actual)}</text>`;
            }

            // label ujung kanan; digeser kalau target & projection terlalu dekat
            let yT = Y(cumTarget[N]),
                yP = p ? Y(p.proj) : null;
            if (p && Math.abs(yT - yP) < 46) {
                const mid = (yT + yP) / 2,
                    dir = yP > yT ? 1 : -1;
                yP = mid + dir * 23;
                yT = mid - dir * 23;
            }
            svg +=
                `<text x="${X(N) + 14}" y="${yT}" font-size="14" font-weight="700" fill="var(--dim)">TARGET
                    <tspan x="${X(N) + 14}" dy="20" font-size="20" font-weight="800" fill="var(--ink)">${fmt(cumTarget[N])}</tspan></text>`;
            if (p) {
                svg += `<text x="${X(N) + 14}" y="${yP}" font-size="14" font-weight="700" fill="${projColor}">PROJECTION
                    <tspan x="${X(N) + 14}" dy="20" font-size="20" font-weight="800">${fmt(p.proj)}</tspan></text>`;
            }

            el.innerHTML = `<svg viewBox="0 0 ${W} ${H}">${svg}</svg>`;
        }

        let lastDefectSignature = null;

        // gambar defect berganti tiap 4,5 detik hanya saat slide 3 tampil, selalu mulai dari gambar pertama
        function startImgCycle() {
            clearInterval(imgTimer);
            imgFrames.forEach((f, i) => f.classList.toggle('active', i === 0));
            imgDots.forEach((d, i) => d.classList.toggle('on', i === 0));
            if (imgFrames.length < 2 || !slides[slideIndex].classList.contains('s3')) return;
            let k = 0;
            imgTimer = setInterval(() => {
                imgFrames[k].classList.remove('active');
                imgDots[k].classList.remove('on');
                k = (k + 1) % imgFrames.length;
                imgFrames[k].classList.add('active');
                imgDots[k].classList.add('on');
            }, 4500);
        }

        function renderDefect(data) {
            const defects = data.list_defect || [];
            const images = data.link_gambar1 || [];
            const points = data.positiondefect || [];

            const signature = JSON.stringify([defects, images, points]);
            if (signature === lastDefectSignature) return;
            lastDefectSignature = signature;

            // warna titik mengikuti peringkat defect di list (sama seperti dashboard lama)
            const rankOf = {};
            defects.forEach((d, i) => rankOf[String(d.defect_type_id)] = i);
            const maxJml = Math.max(1, ...defects.map(d => num(d.jml)));

            // selalu 5 slot supaya daftar penuh setinggi kartu; slot tanpa data tampil "–"
            $('defect-list').innerHTML = defects.length ? [0, 1, 2, 3, 4].map(i => {
                    const d = defects[i];
                    if (!d) return `<div class="defect-item blank"><div class="rank" style="background:#e2e8f0">${i + 1}</div>
                    <div class="name">–</div><div class="count"></div><div class="bar"></div></div>`;
                    return `<div class="defect-item">
                    <div class="rank" style="background:${DEFECT_COLORS[i]};box-shadow:0 0 12px ${DEFECT_COLORS[i]}">${i + 1}</div>
                    <div class="name">${esc(d.defect_type)}</div>
                    <div class="count">${fmt(d.jml)}</div>
                    <div class="bar"><i style="width:${num(d.jml) / maxJml * 100}%;background:${DEFECT_COLORS[i]};box-shadow:0 0 8px ${DEFECT_COLORS[i]}"></i></div>
                </div>`;
                }).join('') :
                `<div class="empty"><i class="fa-regular fa-face-smile"></i>Belum ada defect</div>`;

            const stage = $('defect-stage');
            if (!images.length) {
                stage.innerHTML = `<div class="empty"><i class="fa-regular fa-image"></i>Belum ada gambar</div>`;
                $('img-dots').innerHTML = '';
                imgFrames = [];
                imgDots = [];
                return startImgCycle();
            }
            stage.innerHTML = images.map((g, i) => {
                // hanya titik dari 5 defect teratas, supaya gambar selalu cocok dengan daftar
                const dots = points.filter(p => p.image === g.image && rankOf[String(p.defect_type_id)] < 5).map(
                    p => {
                        const r = rankOf[String(p.defect_type_id)];
                        return `<span class="defect-point" style="left:${parseFloat(p.defect_area_x)}%;top:${parseFloat(p.defect_area_y)}%;background:${DEFECT_COLORS[r]};border:1px solid ${DEFECT_BORDERS[r]}"></span>`;
                    }).join('');
                return `<div class="defect-frame ${i === 0 ? 'active' : ''}"><div class="holder">
                    <img src="${IMG_BASE}${encodeURI(g.image)}" alt="">${dots}</div></div>`;
            }).join('');
            $('img-dots').innerHTML = images.length > 1 ? images.map((_, i) => `<i class="${i === 0 ? 'on' : ''}"></i>`)
                .join('') : '';

            imgFrames = [...stage.querySelectorAll('.defect-frame')];
            imgDots = [...$('img-dots').querySelectorAll('i')];
            startImgCycle();
        }

        // ---------- data ----------
        let isFetching = false;
        let lastUpdate = null; // jam (HH:MM:SS) data terakhir berhasil diambil

        async function getData() {
            if (isFetching) return;
            isFetching = true;

            // tanggal lokal, bukan toISOString() (UTC)
            const d = new Date();
            const tanggal = [d.getFullYear(), String(d.getMonth() + 1).padStart(2, '0'), String(d.getDate()).padStart(2,
                '0')].join('-');
            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 30000);

            try {
                const res = await fetch('/nds_wip/public/index.php/api/trigger-wip-line/dashboard-line/wip-line-sign', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        tanggal,
                        line_id: lineId,
                        jam: testJam
                    }),
                    signal: controller.signal,
                });
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const data = (await res.json()).data.data;

                renderHeader(data);
                renderHourly(data);
                renderKpi(data);
                renderDefect(data);
                renderProjection(data);

                lastUpdate = timeFmt.format(new Date()).replace(/\./g, ':');
                $('status').classList.remove('offline');
                $('status-text').textContent = testJam ? `TEST ${testJam}` : 'LIVE';
                $('status').title = 'Update terakhir ' + lastUpdate;
            } catch (e) {
                console.error('Request failed:', e);
                $('status').classList.add('offline');
                // tampilkan jam data terakhir supaya yang melihat TV tahu datanya sudah lama
                $('status-text').innerHTML = lastUpdate ? `OFFLINE<small>data ${lastUpdate.slice(0, 5)}</small>` :
                    'OFFLINE';
            } finally {
                clearTimeout(timeout);
                isFetching = false;
            }
        }

        getData();
        setInterval(getData, REFRESH_MS);
    </script>
</body>

</html>
