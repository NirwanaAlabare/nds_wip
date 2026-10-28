<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NDS - Smart Line</title>
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('dist/img/tabicon.png') }}">
    {{-- dashboard TV versi smart factory: tanpa jQuery/Bootstrap/Swiper/JSCharting supaya ringan --}}
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free-6.4.2/css/all.min.css') }}">

    <style>
        :root {
            --bg: #f4f7fb;
            --panel: #ffffff;
            --row: #f5f8fc;
            --edge: rgba(14, 165, 233, .22);
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
                radial-gradient(1200px 600px at 15% 0%, rgba(6, 182, 212, .08), transparent 60%),
                radial-gradient(900px 500px at 100% 100%, rgba(37, 99, 235, .07), transparent 60%),
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
            border-radius: var(--radius);
            box-shadow: 0 1px 2px rgba(15, 31, 61, .05), 0 10px 28px rgba(14, 116, 144, .08);
        }

        /* siku dekorasi ala HUD */
        .hud::before,
        .hud::after {
            content: "";
            position: absolute;
            width: 22px;
            height: 22px;
            border: 2px solid var(--cyan);
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

        .worktime {
            background: linear-gradient(135deg, #1d4ed8, #2563eb 45%, #06b6d4) !important;
            border-color: rgba(103, 232, 249, .6) !important;
            box-shadow: 0 0 24px rgba(34, 211, 238, .35), inset 0 1px 0 rgba(255, 255, 255, .2) !important;
            flex-direction: column;
            align-items: flex-start !important;
            justify-content: center;
        }

        .worktime .eyebrow {
            color: rgba(255, 255, 255, .85);
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
            font-weight: 600;
            opacity: .85;
            margin: 0 6px 0 2px;
        }

        /* progres shift: menit kerja berjalan / total menit kerja plan */
        .shift-bar {
            position: relative;
            width: 100%;
            height: 5px;
            margin-top: 8px;
            border-radius: 99px;
            background: rgba(255, 255, 255, .25);
        }

        .shift-bar i {
            display: block;
            height: 100%;
            width: 0;
            border-radius: 99px;
            background: #fff;
            box-shadow: 0 0 8px #fff;
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
            box-shadow: 0 0 18px rgba(6, 182, 212, .35);
            animation: glow 2.4s ease-in-out infinite;
        }

        @keyframes glow {
            50% {
                box-shadow: 0 0 6px rgba(6, 182, 212, .15);
            }
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

        .eq i {
            width: 2px;
            background: var(--cyan);
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
                height: 30%;
            }

            50% {
                height: 100%;
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

        .status .led {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--good);
            box-shadow: 0 0 0 0 rgba(34, 197, 94, .5);
            animation: pulse 2s infinite;
        }

        .status.offline .led {
            background: var(--bad);
            animation: none;
        }

        @keyframes pulse {
            70% {
                box-shadow: 0 0 0 8px rgba(34, 197, 94, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
            }
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
                <svg class="circuit" viewBox="0 0 400 96" preserveAspectRatio="none" fill="none" stroke="#22d3ee" stroke-width="1.5">
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
                    <span class="status" id="status"><span class="led"></span><span id="status-text">…</span></span>
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
                        <div class="split">
                            <div>
                                <div class="k">Day target</div>
                                <div class="v" id="b-day-target">0</div>
                            </div>
                            <div>
                                <div class="k"><span class="sw" style="background:var(--ink)"></span>Realtime target</div>
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
                            <div class="ico c-amber"><i class="fa-solid fa-bug"></i></div>
                            <div>
                                <div class="num" id="b-defect-qty">0</div>
                                <div class="cap">Defect garment qty</div>
                            </div>
                        </div>
                        <div class="hero">
                            <div class="ico c-green"><i class="fa-solid fa-screwdriver-wrench"></i></div>
                            <div>
                                <div class="num" id="b-rework">0</div>
                                <div class="cap">Rework output</div>
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
        </main>
    </div>

    <script>
        const lineId = @json($id);
        const params = new URLSearchParams(location.search);
        const testJam = params.get('jam'); // TEST JAM: ?jam=HH:MM menggantikan jam server
        const SLIDE_MS = 15000;
        const REFRESH_MS = 60000;
        const DEFECT_COLORS = ['#ec3032', '#fd8024', '#fffb45', '#2bff6b', 'whitesmoke'];
        const DEFECT_BORDERS = ['#b50204', '#da5d02', '#d0cb02', '#00c43b', 'gray'];
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

        function tick() {
            const now = new Date();
            $('clock-time').textContent = timeFmt.format(now).replace(/\./g, ':');
            $('clock-date').textContent = dateFmt.format(now);
        }
        tick();
        setInterval(tick, 1000);

        // ---------- slide ----------
        const slides = [...document.querySelectorAll('.slide')];
        // ?slide=1..3 mengunci slide (untuk cek tampilan)
        const lockedSlide = params.has('slide') ? Math.min(Math.max(parseInt(params.get('slide'), 10) - 1 || 0, 0), slides.length - 1) : null;
        let slideIndex = lockedSlide ?? 0;

        function showSlide(i) {
            slideIndex = i;
            slides.forEach((s, k) => s.classList.toggle('active', k === i));
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
        function sparkPath(values, w, h, pad = 4) {
            if (values.length < 2) return '';
            const min = Math.min(...values),
                max = Math.max(...values),
                span = max - min || 1;
            return values.map((v, i) => {
                const x = pad + i * (w - pad * 2) / (values.length - 1);
                const y = h - pad - (v - min) / span * (h - pad * 2);
                return `${i ? 'L' : 'M'}${x.toFixed(1)} ${y.toFixed(1)}`;
            }).join(' ');
        }

        function sparkline(el, values, color, fill) {
            const w = el.viewBox.baseVal.width,
                h = el.viewBox.baseVal.height;
            const d = sparkPath(values, w, h);
            if (!d) return el.innerHTML = '';
            const area = fill ? `<path d="${d} L${w - 4} ${h} L4 ${h} Z" fill="${color}" opacity=".18"/>` : '';
            el.innerHTML = `${area}<path d="${d}" fill="none" stroke="${color}" stroke-width="2.5" stroke-linejoin="round"
                stroke-linecap="round" style="filter:drop-shadow(0 0 4px ${color})"/>`;
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
                    style="transform-origin:150px 150px;transform:scale(1.2)"/>`).join(''); // zona warna di luar busur utama
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

        function setPill(el, diff, label) {
            const up = diff >= 0;
            el.className = `pill ${up ? 'good' : 'bad'}`;
            el.innerHTML = `<i class="fa-solid ${up ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down'}"></i> ${up ? 'Lebih' : 'Kurang'} ${fmt(Math.abs(diff))}${label ? ' ' + label : ''}`;
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
            return rows.filter((r, i) => num(r.menit_kerja) > 0 || num(r.output) > 0).filter(r => currentIndex < 0 || rows.indexOf(r) <= currentIndex);
        }

        function renderHeader(data) {
            const line = String(lineId || '').replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
            $('line-name').textContent = line || '-';
            const buyer = data.buyer || '-';
            const ws = data.no_ws || '-';
            if ($('buyer').textContent !== buyer) setScrollText($('buyer'), buyer);
            if ($('ws').textContent !== ws) setScrollText($('ws'), ws);

            const menit = num((data.summary || {}).menit_kerja);
            $('worktime').innerHTML = `${Math.floor(menit / 60)}<small>jam</small>${menit % 60}<small>menit</small>`;
            const totalMenit = num((data.summary || {}).jam_kerja) * 60;
            $('worktime-bar').style.width = (totalMenit ? Math.min(menit / totalMenit * 100, 100) : 0) + '%';
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
                        <td colspan="5" style="letter-spacing:.2em;font-size:18px"><i class="fa-solid fa-mug-hot"></i>&nbsp; ISTIRAHAT</td></tr>`;
                }
                const future = cls === 'future';
                const dot = future ? '' : (num(row.output) >= num(row.target) ? 'good' : 'bad');
                const nowTag = cls === 'current' ? `<span class="now-tag"><b>SEKARANG</b><span class="eq"><i></i><i></i><i></i><i></i><i></i></span></span>` : '';
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
            $('hourly-meta').textContent = rows.length ? `${rows[0].hours.slice(0, 5)} – ${rows[rows.length - 1].hours.slice(-5)}` : 'Belum ada plan';
        }

        function renderKpi(data) {
            const s = data.summary || {};
            const variation = num(s.variation); // target - output, positif = kurang
            $('k-target').textContent = fmt(s.target);
            $('k-output').textContent = fmt(s.tot_output);
            $('k-variation').innerHTML = varHtml(-variation);
            $('k-eff').innerHTML = `${num(s.eff).toFixed(2)}<small>%</small>`;
            $('k-defrate').innerHTML = `${num(s.def_rate).toFixed(2)}<small>%</small>`;

            // proyeksi akhir shift = laju output sejauh ini x total menit kerja plan
            const menit = num(s.menit_kerja),
                totalMenit = num(s.jam_kerja) * 60;
            if (menit > 0 && totalMenit > 0) {
                const proj = Math.round(num(s.tot_output) / menit * totalMenit);
                const gap = proj - num(s.target);
                $('k-proj').innerHTML = `PROJECTION: <b>${fmt(proj)}</b> <span class="${gap < 0 ? 'neg' : 'pos'}">(${gap < 0 ? 'SHORTFALL' : 'SURPLUS'} ${gap < 0 ? '-' : '+'}${fmt(Math.abs(gap))})</span>`;
            } else {
                $('k-proj').textContent = '';
            }

            // visual kartu dari data per jam: bar selalu selebar semua jam kerja (jam yang belum terjadi tampil tipis)
            const past = pastRows(data);
            const slots = (data.hourly || []).filter(r => num(r.menit_kerja) > 0 || num(r.output) > 0);
            bars($('k-output-viz'), slots.map(r => num(r.output)), '#ffffff', past.length);
            let cum = 0;
            sparkline($('k-variation-viz'), [0, ...past.map(r => cum += num(r.variation))], '#ffffff', true);
            ring($('k-eff-viz'), num(s.eff), '#ffffff');

            // slide 2
            const target = num(s.target),
                actual = num(s.tot_output),
                realtime = num(s.target_skrg);
            $('b-actual').textContent = fmt(actual);
            $('b-day-target').textContent = fmt(target);
            $('b-realtime-target').textContent = fmt(realtime);
            setPill($('p-variation'), -variation, 'dari target');
            const bar = $('b-actual-bar');
            bar.style.width = (target ? Math.min(actual / target * 100, 100) : 0) + '%';
            bar.className = actual >= realtime ? 'tone-good' : 'tone-bad';
            $('b-realtime-marker').style.left = (target ? Math.min(realtime / target * 100, 100) : 0) + '%';

            const req = num(s.req_hour_target),
                hourOut = num(s.output_jam_ini);
            $('b-periode').textContent = s.periode_jam || '';
            $('b-hour-output').textContent = fmt(hourOut);
            $('b-req-hour').textContent = fmt(req);
            setPill($('p-hour'), hourOut - req, '');
            const hourPct = req ? hourOut / req * 100 : 0;
            $('b-hour-pct').innerHTML = `${req ? hourPct.toFixed(0) : '-'}<small style="font-size:20px">%</small>`;
            const hbar = $('b-hour-bar');
            hbar.style.width = Math.min(hourPct, 100) + '%';
            hbar.className = 'tone-' + effTone(hourPct);

            $('b-defect-qty').textContent = fmt(s.tot_defect);
            $('b-rework').textContent = fmt(s.tot_reworked);

            setGauge(gauges.eff, num(s.eff));
            setGauge(gauges.rft, num(s.rft_pct));
            setGauge(gauges.defect, num(s.def_rate));
        }

        let lastDefectSignature = null;
        let imgTimer = null;

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

            clearInterval(imgTimer);
            const stage = $('defect-stage');
            if (!images.length) {
                stage.innerHTML = `<div class="empty"><i class="fa-regular fa-image"></i>Belum ada gambar</div>`;
                $('img-dots').innerHTML = '';
                return;
            }
            stage.innerHTML = images.map((g, i) => {
                const dots = points.filter(p => p.image === g.image).map(p => {
                    const r = rankOf[String(p.defect_type_id)];
                    const c = r === undefined ? 'whitesmoke' : DEFECT_COLORS[r];
                    const b = r === undefined ? 'gray' : DEFECT_BORDERS[r];
                    return `<span class="defect-point" style="left:${parseFloat(p.defect_area_x)}%;top:${parseFloat(p.defect_area_y)}%;background:${c};border:1px solid ${b}"></span>`;
                }).join('');
                return `<div class="defect-frame ${i === 0 ? 'active' : ''}"><div class="holder">
                    <img src="${IMG_BASE}${encodeURI(g.image)}" alt="">${dots}</div></div>`;
            }).join('');
            $('img-dots').innerHTML = images.length > 1 ? images.map((_, i) => `<i class="${i === 0 ? 'on' : ''}"></i>`).join('') : '';

            if (images.length > 1) {
                let k = 0;
                const frames = stage.querySelectorAll('.defect-frame');
                const dots = $('img-dots').querySelectorAll('i');
                imgTimer = setInterval(() => {
                    frames[k].classList.remove('active');
                    dots[k].classList.remove('on');
                    k = (k + 1) % frames.length;
                    frames[k].classList.add('active');
                    dots[k].classList.add('on');
                }, 4500);
            }
        }

        // ---------- data ----------
        let isFetching = false;

        async function getData() {
            if (isFetching) return;
            isFetching = true;

            // tanggal lokal, bukan toISOString() (UTC)
            const d = new Date();
            const tanggal = [d.getFullYear(), String(d.getMonth() + 1).padStart(2, '0'), String(d.getDate()).padStart(2, '0')].join('-');
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

                $('status').classList.remove('offline');
                $('status-text').textContent = testJam ? `TEST ${testJam}` : 'LIVE';
                $('status').title = 'Update terakhir ' + timeFmt.format(new Date()).replace(/\./g, ':');
            } catch (e) {
                console.error('Request failed:', e);
                $('status').classList.add('offline');
                $('status-text').textContent = 'OFFLINE';
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
