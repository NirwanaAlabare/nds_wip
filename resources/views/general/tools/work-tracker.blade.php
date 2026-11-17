@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <style>
        :root {
            --wt-navy: #082149; --wt-navy-2: #123a78; --wt-ink: #1b2536; --wt-muted: #68748a; --wt-line: #e4e9f2; --wt-soft: #f4f7fc;
            --wt-green: #12a150; --wt-amber: #e08a00; --wt-red: #dc3545; --wt-blue: #2f80ed; --wt-gray: #8a94a6;
        }
        .wt { color: var(--wt-ink); padding-bottom: 32px; }
        .wt-card { background: #fff; border: 1px solid var(--wt-line); border-radius: 14px; box-shadow: 0 4px 16px rgba(8, 33, 73, .05); }

        /* Hero */
        /* Hero: warna dan animasi mengikuti halaman Action Points.
           Warna didaftarkan lewat @property supaya perpindahan palet bisa berjalan halus. */
        @property --hero-a { syntax: '<color>'; inherits: true; initial-value: #2563eb; }
        @property --hero-b { syntax: '<color>'; inherits: true; initial-value: #06b6d4; }
        .wt-hero { --hero-a: #2563eb; --hero-b: #06b6d4; position: relative; overflow: hidden; border-radius: 22px; padding: 24px 28px; color: #fff;
            background: linear-gradient(120deg, var(--hero-a), var(--hero-b), var(--hero-a)); background-size: 220% 220%; animation: wt-gradient 14s ease infinite;
            box-shadow: 0 14px 34px color-mix(in srgb, var(--hero-a) 32%, transparent); transition: --hero-a 4s ease, --hero-b 4s ease; }
        @keyframes wt-gradient { 0%, 100% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } }
        .wt-blob { position: absolute; border-radius: 50%; background: rgba(255, 255, 255, .12); filter: blur(2px); pointer-events: none; animation: wt-blob 16s ease-in-out infinite; translate: calc(var(--mx, 0) * -22px) calc(var(--my, 0) * -22px); transition: translate .6s ease-out; }
        .wt-blob.b1 { width: 260px; height: 260px; right: -70px; top: -110px; }
        .wt-blob.b2 { width: 160px; height: 160px; right: 22%; bottom: -90px; animation-delay: -5s; animation-duration: 19s; }
        .wt-blob.b3 { width: 90px; height: 90px; left: 38%; top: -40px; animation-delay: -9s; animation-duration: 13s; }
        @keyframes wt-blob { 0%, 100% { transform: translate(0, 0) scale(1); } 33% { transform: translate(-24px, 18px) scale(1.08); } 66% { transform: translate(18px, -12px) scale(.94); } }
        .wt-shine { position: absolute; top: 0; bottom: 0; left: -40%; width: 30%; background: linear-gradient(100deg, transparent, rgba(255, 255, 255, .22), transparent); transform: skewX(-20deg); animation: wt-shine 7s ease-in-out infinite; pointer-events: none; }
        @keyframes wt-shine { 0%, 60% { left: -40%; } 100% { left: 130%; } }
        .wt-bubbles { position: absolute; inset: 0; overflow: hidden; pointer-events: none; }
        .wt-bubbles span { position: absolute; bottom: -20px; left: var(--l); width: var(--z); height: var(--z); border-radius: 50%; background: rgba(255, 255, 255, .28); box-shadow: inset 0 0 4px rgba(255, 255, 255, .6); animation: wt-bubble var(--t) linear infinite; animation-delay: var(--dl); }
        @keyframes wt-bubble { 0% { transform: translate(0, 0) scale(.6); opacity: 0; } 10% { opacity: 1; } 50% { transform: translate(var(--sw), -120px) scale(1); } 100% { transform: translate(0, -260px) scale(.8); opacity: 0; } }
        .wt-float { position: absolute; color: rgba(255, 255, 255, .22); font-size: var(--s, 22px); left: var(--x); top: var(--y); pointer-events: none; animation: wt-float var(--d, 7s) ease-in-out infinite; animation-delay: var(--delay, 0s); translate: calc(var(--mx, 0) * var(--depth, 14px)) calc(var(--my, 0) * var(--depth, 14px)); transition: translate .4s ease-out; }
        .wt-float.twinkle { color: rgba(255, 255, 255, .7); animation-name: wt-hero-twinkle; }
        @keyframes wt-float { 0%, 100% { transform: translateY(0) rotate(0); } 50% { transform: translateY(-14px) rotate(10deg); } }
        @keyframes wt-hero-twinkle { 0%, 100% { opacity: .15; transform: scale(.6) rotate(0); } 50% { opacity: 1; transform: scale(1.2) rotate(72deg); } }
        .wt-wave { position: absolute; left: 0; bottom: 0; width: 200%; height: 46px; pointer-events: none; }
        .wt-wave path { fill: rgba(255, 255, 255, .09); }
        .wt-wave .w1 { animation: wt-wave 12s linear infinite; }
        .wt-wave .w2 { fill: rgba(255, 255, 255, .07); animation: wt-wave 18s linear infinite reverse; }
        @keyframes wt-wave { from { transform: translateX(0); } to { transform: translateX(-50%); } }
        .wt-hero-runner { position: absolute; bottom: 10px; left: -40px; font-size: 24px; color: rgba(255, 255, 255, .32); pointer-events: none; animation: wt-hero-run 16s linear infinite; }
        @keyframes wt-hero-run { to { left: 102%; } }
        .wt-hero-inner { position: relative; z-index: 1; display: flex; flex-wrap: wrap; justify-content: space-between; gap: 18px; }
        .wt-hero-main { display: flex; align-items: center; gap: 22px; min-width: 0; }
        .wt-ring { position: relative; flex: none; width: 122px; height: 122px; animation: wt-ring-breathe 3.2s ease-in-out infinite; }
        @keyframes wt-ring-breathe { 0%, 100% { filter: drop-shadow(0 0 0 rgba(255, 255, 255, 0)); } 50% { filter: drop-shadow(0 0 14px rgba(255, 255, 255, .45)); } }
        .wt-ring svg { width: 100%; height: 100%; transform: rotate(-90deg); }
        .wt-ring circle { fill: none; stroke-width: 10; }
        .wt-ring-track { stroke: rgba(255, 255, 255, .22); }
        .wt-ring-value { stroke: #fff; stroke-linecap: round; stroke-dasharray: 100; stroke-dashoffset: 100; transition: stroke-dashoffset 1.6s cubic-bezier(.22, .8, .3, 1); filter: drop-shadow(0 0 6px rgba(255, 255, 255, .6)); }
        .wt-ring-orbit { position: absolute; inset: -9px; border-radius: 50%; border: 2px dashed rgba(255, 255, 255, .35); animation: fa-spin 22s linear infinite; }
        .wt-ring-orbit i { position: absolute; top: -5px; left: 50%; width: 9px; height: 9px; margin-left: -4.5px; border-radius: 50%; background: #fff; box-shadow: 0 0 10px 3px rgba(255, 255, 255, .8); }
        .wt-ring-text { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; line-height: 1.05; }
        .wt-ring-text b { font-size: 28px; font-weight: 800; font-variant-numeric: tabular-nums; }
        .wt-ring-text span { font-size: 11px; font-weight: 600; opacity: .9; }
        .wt-ring.pop .wt-ring-text b { animation: wt-num-pop .6s cubic-bezier(.2, 1.6, .4, 1); }
        @keyframes wt-num-pop { 40% { transform: scale(1.35); } }
        @keyframes wt-twinkle { 0%, 100% { opacity: .1; transform: scale(.6); } 50% { opacity: .75; transform: scale(1.15); } }
        .wt-alert { display: flex; flex-wrap: wrap; align-items: center; gap: 8px 12px; padding: 10px 16px; margin-bottom: 14px; border-radius: 12px; color: #fff; font-weight: 600; font-size: 13.5px; background: linear-gradient(90deg, #c21f30, #e8505f); animation: wt-alert-glow 1.4s ease-in-out infinite; }
        .wt-alert button { padding: 2px 10px; border: 1px solid rgba(255, 255, 255, .6); border-radius: 14px; background: rgba(255, 255, 255, .14); color: #fff; font-size: 12.5px; font-weight: 600; }
        .wt-alert button:hover { background: rgba(255, 255, 255, .3); }
        @keyframes wt-alert-glow { 0%, 100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, .55); } 50% { box-shadow: 0 0 0 7px rgba(220, 53, 69, 0); } }
        .wt-greet { font-size: 13px; opacity: .92; }
        .wt-clock { display: inline-block; margin-left: 4px; padding: 1px 8px; border-radius: 8px; background: rgba(255, 255, 255, .18); font-weight: 700; font-variant-numeric: tabular-nums; }
        .wt-clock .colon { animation: wt-blink 1s steps(1) infinite; }
        @keyframes wt-blink { 50% { opacity: .25; } }
        .wt-hero h1 { margin: 2px 0 0; font-size: 27px; font-weight: 800; letter-spacing: -.01em; }
        .wt-hero h1 > i { display: inline-block; animation: wt-hero-wiggle 3.5s ease-in-out infinite; }
        @keyframes wt-hero-wiggle { 0%, 86%, 100% { transform: rotate(0); } 90% { transform: rotate(-14deg); } 95% { transform: rotate(12deg); } }
        .wt-title-text .ch { display: inline-block; animation: wt-letter .7s cubic-bezier(.2, 1.5, .4, 1) backwards; animation-delay: calc(var(--i) * 45ms + .15s); }
        .wt-title-text .ch.sp { width: .28em; }
        @keyframes wt-letter { from { opacity: 0; transform: translateY(26px) rotate(12deg) scale(.6); } }
        #wt-hero-title:hover .ch { animation: wt-letter-wave .6s ease; animation-delay: calc(var(--i) * 35ms); }
        @keyframes wt-letter-wave { 40% { transform: translateY(-8px); color: rgba(255, 255, 255, .85); } }
        /* Pesan hero bergantian dengan efek balik kartu dan bar waktu di bawahnya */
        .wt-verdict { position: relative; overflow: hidden; display: inline-flex; align-items: center; min-height: 40px; margin-top: 10px; padding: 8px 15px; border-radius: 13px; background: rgba(255, 255, 255, .96); color: #18223a; font-weight: 700; font-size: 14.5px; box-shadow: 0 6px 16px rgba(0, 0, 0, .16); perspective: 400px; animation: wt-hero-pop .5s cubic-bezier(.2, 1.4, .4, 1) both; }
        @keyframes wt-hero-pop { from { opacity: 0; transform: scale(.85); } }
        .wt-msg-body { display: inline-flex; align-items: center; gap: 9px; transform-origin: center bottom; transition: transform .3s ease, opacity .3s ease; }
        .wt-verdict.flip .wt-msg-body { transform: rotateX(90deg) translateY(-6px); opacity: 0; }
        .wt-verdict.good i { color: #0f9d6b; } .wt-verdict.warn i { color: #c27803; } .wt-verdict.bad i { color: #dc2626; }
        .wt-verdict.bad .wt-msg-body i { animation: wt-bell 1.6s ease-in-out infinite; }
        @keyframes wt-bell { 0%, 50%, 100% { transform: rotate(0); } 10%, 30% { transform: rotate(-18deg); } 20%, 40% { transform: rotate(18deg); } }
        .wt-msg-bar { position: absolute; left: 0; bottom: 0; width: 0; height: 3px; background: linear-gradient(90deg, var(--hero-a), var(--hero-b)); }
        .wt-msg-bar.run { animation: wt-msg-bar var(--msg-dur, 6s) linear forwards; }
        @keyframes wt-msg-bar { to { width: 100%; } }
        .wt-hero-actions { display: flex; flex-wrap: wrap; align-items: flex-start; gap: 8px; }
        .wt-btn-ghost, .wt-btn-light { position: relative; overflow: hidden; display: inline-flex; align-items: center; gap: 6px; border-radius: 11px; padding: 6px 13px; font-size: 13.5px; font-weight: 600; text-decoration: none; transition: background .15s, transform .15s, box-shadow .15s; }
        .wt-btn-ghost { border: 1px solid rgba(255, 255, 255, .45); background: rgba(255, 255, 255, .1); color: #fff; backdrop-filter: blur(4px); }
        .wt-btn-ghost:hover { background: rgba(255, 255, 255, .24); color: #fff; transform: translateY(-1px); }
        .wt-btn-3d { border: 0; background: linear-gradient(120deg, #22c1c3, #3b82f6 55%, #8b5cf6); font-weight: 700; animation: wt-3d-pulse 2.4s infinite; }
        .wt-btn-3d:hover { background: linear-gradient(120deg, #22c1c3, #3b82f6 55%, #8b5cf6); filter: brightness(1.1); }
        @keyframes wt-3d-pulse { 0% { box-shadow: 0 0 0 0 rgba(255, 255, 255, .6); } 70% { box-shadow: 0 0 0 9px rgba(255, 255, 255, 0); } 100% { box-shadow: 0 0 0 0 rgba(255, 255, 255, 0); } }
        .wt-btn-light { border: 0; padding: 6px 15px; background: #fff; color: var(--hero-a); font-weight: 700; box-shadow: 0 6px 16px rgba(0, 0, 0, .15); }
        .wt-btn-light:hover { color: var(--hero-a); transform: translateY(-2px) scale(1.02); box-shadow: 0 10px 22px rgba(0, 0, 0, .2); }
        .wt-btn-light i { transition: transform .3s; } .wt-btn-light:hover i { transform: rotate(90deg); }
        .wt-btn-light::after { content: ""; position: absolute; top: 0; bottom: 0; left: -60%; width: 40%; background: linear-gradient(100deg, transparent, color-mix(in srgb, var(--hero-a) 25%, transparent), transparent); transform: skewX(-20deg); animation: wt-shine 3.5s ease-in-out infinite; }
        .wt-ripple { position: absolute; border-radius: 50%; background: rgba(255, 255, 255, .55); transform: scale(0); animation: wt-ripple .6s ease-out forwards; pointer-events: none; }
        .wt-btn-light .wt-ripple { background: color-mix(in srgb, var(--hero-a) 25%, transparent); }
        @keyframes wt-ripple { to { transform: scale(4); opacity: 0; } }
        .wt-demo { background: #fff5dc; border: 1px solid #f3d995; color: #755300; border-radius: 10px; padding: 8px 14px; font-size: 13px; }

        /* Tiles */
        .wt-tiles { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; margin: 14px 0; }
        .wt-tile { position: relative; overflow: hidden; display: flex; flex-direction: column; gap: 8px; padding: 13px 14px 11px; border-top: 3px solid var(--accent); background: linear-gradient(145deg, var(--tint), #fff 62%); animation: wt-rise .45s ease both; transition: transform .14s, box-shadow .14s; }
        .wt-tile:hover { transform: translateY(-3px); box-shadow: 0 10px 22px rgba(8, 33, 73, .14); }
        .wt-tile-top { display: flex; gap: 11px; align-items: center; }
        .wt-tile-icon { flex: none; width: 40px; height: 40px; border-radius: 12px; display: grid; place-items: center; font-size: 16px; color: #fff; background: var(--accent); box-shadow: 0 4px 10px rgba(8, 33, 73, .18); }
        .wt-tile-value { font-size: 26px; font-weight: 800; line-height: 1.05; font-variant-numeric: tabular-nums; }
        .wt-tile-foot { display: flex; align-items: center; height: 26px; margin-top: auto; font-size: 11.5px; color: var(--wt-muted); }
        .wt-tile-foot b { color: var(--wt-ink); margin: 0 3px; }
        .wt-trend { position: relative; display: block; width: 100%; height: 26px; }
        .wt-trend svg { display: block; width: 100%; height: 100%; overflow: visible; }
        .wt-trend path { vector-effect: non-scaling-stroke; }
        .wt-trend-line { fill: none; stroke: var(--accent); stroke-width: 2; stroke-linejoin: round; stroke-linecap: round; }
        .wt-trend-area { fill: var(--accent); opacity: .12; }
        .wt-trend-dot { position: absolute; right: -2px; width: 8px; height: 8px; margin-top: -4px; border: 2px solid #fff; border-radius: 50%; background: var(--accent); box-sizing: content-box; }
        .wt-tile-alert { animation: wt-rise .45s ease both, wt-tile-alert 1.6s ease-in-out infinite; }
        @keyframes wt-tile-alert { 0%, 100% { box-shadow: 0 4px 16px rgba(8, 33, 73, .05), 0 0 0 0 rgba(220, 53, 69, .5); } 50% { box-shadow: 0 4px 16px rgba(8, 33, 73, .05), 0 0 0 7px rgba(220, 53, 69, 0); } }
        .wt-tile:hover .wt-tile-icon { animation: wt-wiggle .5s ease; }
        @keyframes wt-wiggle { 25% { transform: rotate(-12deg); } 75% { transform: rotate(12deg); } }
        .wt-tile-label { font-size: 12px; color: var(--wt-muted); }
        @keyframes wt-rise { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }

        /* Antrean */
        .wt-tab-count { display: inline-block; min-width: 20px; margin-left: 3px; padding: 0 6px; border-radius: 10px; background: #fff2d6; color: #a86700; font-size: 11.5px; font-weight: 700; }
        .wt-tab-count.empty { background: #eceff4; color: #5b6577; }
        .wt-tile-link { width: 100%; text-align: left; cursor: pointer; }
        .wt-queue-row { display: grid; grid-template-columns: minmax(0, 1fr) 170px 120px 280px; gap: 12px; align-items: center; padding: 9px 0; border-top: 1px solid var(--wt-line); font-size: 13px; }
        .wt-queue-row:first-child { border-top: 0; }
        .wt-queue-title { display: block; padding: 0; border: 0; background: none; color: inherit; text-align: left; font-weight: 600; }
        .wt-queue-title:hover { text-decoration: underline; }
        .wt-queue-overdue { color: var(--wt-red); font-weight: 700; }
        .wt-assign { min-width: 190px; }

        /* Race */
        .wt-section-title { font-size: 15px; font-weight: 700; margin: 0; }
        /* Suasana langit (data-sky) mengikuti jam, pemandangan (data-scene) berganti tiap hari, warna bukit diredupkan lewat --shade */
        .wt-race { padding: 16px 18px 8px; margin-bottom: 14px; --sky-a: #6fc0ff; --sky-b: #ddf2ff; --ground: #d6dfeb; --hill-far: #b4e0bd; --hill-near: #86cc98; --prop: #4f9a5e; --sun: #ffd23f; --sun-top: 18px; --sun-size: 22px; --cloud: #fff; --shade: none; --line: rgba(8, 33, 73, .42); }
        .wt-race[data-sky="dawn"] { --sky-a: #5d4f8f; --sky-b: #f5b3a8; --ground: #d3cbd8; --sun: #ff9e7a; --sun-top: 54px; --sun-size: 20px; --cloud: #f6cfdb; --shade: brightness(.78) saturate(.7); }
        .wt-race[data-sky="morning"] { --sky-a: #cfe9ff; --sky-b: #fff3d6; --sun: #f7b84b; --sun-top: 38px; --sun-size: 19px; --shade: brightness(1.04); }
        .wt-race[data-sky="afternoon"] { --sky-a: #9cc8ef; --sky-b: #ffe6bf; --sun: #f5a524; --sun-top: 30px; --sun-size: 20px; --shade: sepia(.18) brightness(.97); }
        .wt-race[data-sky="sunset"] { --sky-a: #ff9f73; --sky-b: #ffe0ad; --ground: #d9cdd0; --sun: #e8590c; --sun-top: 50px; --sun-size: 24px; --cloud: #ffeedd; --shade: sepia(.45) saturate(1.1) brightness(.78) hue-rotate(-18deg); }
        .wt-race[data-sky="night"] { --sky-a: #0b1a3b; --sky-b: #2a4575; --ground: #3d4a63; --sun: #f6efc4; --sun-top: 26px; --sun-size: 18px; --cloud: #5d7199; --shade: brightness(.42) saturate(.6) hue-rotate(50deg); --line: rgba(255, 255, 255, .6); }
        .wt-race[data-sky="midnight"] { --sky-a: #040a1c; --sky-b: #13244a; --ground: #2b3549; --sun: #ece6c6; --sun-top: 14px; --sun-size: 17px; --cloud: #34456a; --shade: brightness(.3) saturate(.5) hue-rotate(50deg); --line: rgba(255, 255, 255, .6); }
        .wt-race[data-dark] .wt-runner, .wt-race[data-sky="sunset"] .wt-runner { filter: drop-shadow(0 0 1.5px #fff); }
        .wt-race[data-dark] .wt-pacer::before { color: #fff; }
        .wt-race[data-dark] .wt-cp-pole { border-left-color: rgba(255, 255, 255, .45); }
        .wt-race[data-dark] .wt-bird { color: #9fb2d3; }
        .wt-race[data-dark] .wt-sun { animation: none; }
        .wt-race[data-dark] .wt-gate { border-color: #b8c4d9; }
        .wt-race[data-scene="beach"] { --hill-far: #4fb3d9; --hill-near: #f0d9a4; --prop: #e8590c; }
        .wt-race[data-scene="beach"] .wt-hill.far { --hill-shape: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 240 40' preserveAspectRatio='none'%3E%3Cpath d='M0 40V14C20 10 40 10 60 14S100 18 120 14S160 10 180 14S220 18 240 14V40Z'/%3E%3C/svg%3E"); height: 28px; }
        .wt-race[data-scene="beach"] .wt-hill.near { --hill-shape: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 240 40' preserveAspectRatio='none'%3E%3Cpath d='M0 40V26C60 12 120 12 180 24S230 30 240 26V40Z'/%3E%3C/svg%3E"); height: 14px; }
        .wt-race[data-scene="city"] { --hill-far: #9fb0c8; --hill-near: #8fc79c; --prop: #4f9a5e; }
        .wt-race[data-scene="city"] .wt-hill.far { --hill-shape: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 240 40' preserveAspectRatio='none'%3E%3Cpath d='M0 40V22H18V10H34V18H46V4H62V16H78V24H92V8H110V20H124V12H140V26H152V6H170V18H186V14H202V22H216V10H232V22H240V40Z'/%3E%3C/svg%3E"); height: 46px; }
        .wt-race[data-scene="city"] .wt-hill.near { height: 12px; }
        .wt-race[data-dark][data-scene="city"] .wt-hill.far { filter: none; background-color: #1c2a47; background-image: radial-gradient(#ffd76a 1px, transparent 1.6px); background-size: 9px 8px; }
        .wt-race[data-scene="paddy"] { --hill-far: #8fc98a; --hill-near: #b8d65a; --prop: #c9a227; }
        .wt-race[data-scene="paddy"] .wt-hill.far { height: 30px; }
        .wt-race[data-scene="paddy"] .wt-hill.near { --hill-shape: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 240 40' preserveAspectRatio='none'%3E%3Cpath d='M0 40V22H60V16H120V22H180V16H240V22V40Z'/%3E%3C/svg%3E"); height: 16px; background-image: repeating-linear-gradient(90deg, rgba(40, 90, 20, .22) 0 2px, transparent 2px 9px); }
        .wt-scene { margin-left: 8px; padding: 2px 9px; border-radius: 10px; background: var(--wt-soft); color: var(--wt-muted); font-size: 11.5px; font-weight: 600; vertical-align: middle; white-space: nowrap; }
        .wt-scene i { margin-right: 3px; }
        .wt-legend { display: flex; flex-wrap: wrap; gap: 14px; font-size: 12px; color: var(--wt-muted); }
        .wt-lane { display: grid; grid-template-columns: 190px 1fr 190px; gap: 14px; align-items: center; padding: 8px 6px; border-radius: 10px; cursor: pointer; transition: background .15s; }
        .wt-lane:hover { background: var(--wt-soft); }
        .wt-lane.selected { background: #e9f1fe; }
        .wt-lane + .wt-lane { margin-top: 2px; }
        .wt-division-chips { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 8px; }
        .wt-division-chips button { padding: 3px 12px; border: 1px solid var(--wt-line); border-radius: 16px; background: #fff; color: var(--wt-muted); font-size: 12.5px; font-weight: 600; transition: background .12s, color .12s; }
        .wt-division-chips button:hover { background: var(--wt-soft); }
        .wt-division-chips button.active { border-color: var(--wt-navy); background: var(--wt-navy); color: #fff; }
        .wt-division-head { display: flex; align-items: center; gap: 8px; margin: 10px 0 4px; font-size: 11.5px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: var(--wt-navy); }
        .wt-division-head::after { content: ""; flex: 1; height: 1px; background: var(--wt-line); }
        .wt-division-head span { font-weight: 600; color: var(--wt-muted); text-transform: none; letter-spacing: 0; }
        .wt-member-division { width: 150px; flex: none; }
        .wt-person { display: flex; align-items: center; gap: 10px; min-width: 0; }
        .wt-avatar { flex: none; width: 36px; height: 36px; border-radius: 50%; color: #fff; display: grid; place-items: center; font-size: 13px; font-weight: 700; }
        .wt-person-name { font-weight: 600; font-size: 13.5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .wt-person-sub { font-size: 11.5px; color: var(--wt-muted); }
        .wt-track { position: relative; height: 112px; border-radius: 12px; overflow: hidden; background: linear-gradient(var(--sky-a), var(--sky-b) calc(100% - 28px), var(--ground) 0); box-shadow: inset 0 0 0 1px rgba(8, 33, 73, .08); }
        .wt-hill { position: absolute; left: 0; bottom: 28px; background: var(--hill-near); --hill-shape: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 240 40' preserveAspectRatio='none'%3E%3Cpath d='M0 40V24C40 6 80 6 120 22S200 38 240 24V40Z'/%3E%3C/svg%3E"); -webkit-mask: var(--hill-shape) 0 0 / var(--hill-w) 100% repeat-x; mask: var(--hill-shape) 0 0 / var(--hill-w) 100% repeat-x; filter: var(--shade); will-change: transform; }
        .wt-hill.far { --hill-w: 420px; width: calc(100% + 420px); height: 40px; background: var(--hill-far); }
        .wt-hill.near { --hill-w: 250px; width: calc(100% + 250px); height: 24px; }
        @keyframes wt-hill-far { to { transform: translateX(-420px); } }
        @keyframes wt-hill-near { to { transform: translateX(-250px); } }
        .tier-sprint .wt-hill.far { animation: wt-hill-far 16s linear infinite; } .tier-sprint .wt-hill.near { animation: wt-hill-near 6s linear infinite; }
        .tier-jog .wt-hill.far { animation: wt-hill-far 34s linear infinite; } .tier-jog .wt-hill.near { animation: wt-hill-near 13s linear infinite; }
        .tier-walk .wt-hill.far { animation: wt-hill-far 80s linear infinite; } .tier-walk .wt-hill.near { animation: wt-hill-near 30s linear infinite; }
        .incident-panic .wt-hill, .incident-fixing .wt-hill { animation: none !important; }
        .wt-gate { position: absolute; z-index: 1; top: 26px; bottom: 22px; border-left: 3px solid #4b5a73; border-right: 3px solid #4b5a73; pointer-events: none; }
        .wt-gate span { position: absolute; left: -3px; right: -3px; top: 0; height: 13px; border-radius: 3px; background: var(--wt-navy); color: #fff; font-size: 8px; font-weight: 800; line-height: 13px; letter-spacing: .08em; text-align: center; }
        .wt-gate.start { left: 5px; width: 46px; }
        .wt-gate.end { right: 18px; width: 46px; }
        .wt-gate.end span { background: #c21f30; }
        .wt-star { position: absolute; color: #fff; font-size: 7px; animation: wt-twinkle 2.4s ease-in-out infinite; }
        .wt-track-road { position: absolute; left: 0; right: 0; bottom: 11px; height: 2px; background-image: linear-gradient(90deg, #fff 50%, transparent 50%); background-size: 28px 2px; animation: wt-road var(--road, 0s) linear infinite; }
        @keyframes wt-road { to { background-position-x: -28px; } }
        .wt-finish { position: absolute; z-index: 1; top: 0; bottom: 0; right: 0; width: 16px; background: repeating-conic-gradient(#1b2536 0 25%, #fff 0 50%) 0 0 / 8px 8px; }
        .wt-pacer { position: absolute; top: 25px; bottom: 0; width: 0; border-left: 2px dashed var(--line); transition: left 1.2s ease; }
        .wt-pacer::before { content: "\f024"; font-family: "Font Awesome 6 Free"; font-weight: 900; position: absolute; top: -3px; left: 1px; font-size: 10px; color: var(--wt-navy); }
        .wt-runner-box { position: absolute; z-index: 2; pointer-events: none; bottom: 3px; width: 54px; height: 56px; left: 0; transition: left 1.3s cubic-bezier(.22, .8, .3, 1); }
        .wt-pct { position: absolute; bottom: 57px; left: 27px; transform: translateX(-50%); padding: 0 6px; border-radius: 9px; background: #fff; box-shadow: 0 1px 3px rgba(8, 33, 73, .2); font-size: 11.5px; font-weight: 700; color: var(--wt-navy); white-space: nowrap; }
        .wt-cp-label { position: absolute; top: 4px; height: 18px; padding: 0 2px 0 6px; border: 0; background: none; font-size: 11.5px; font-weight: 600; line-height: 18px; text-align: right; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #5b6577; }
        .wt-cp-label span { display: inline-block; max-width: 100%; height: 18px; padding: 0 5px; overflow: hidden; border-radius: 6px; background: rgba(255, 255, 255, .86); vertical-align: top; text-overflow: ellipsis; }
        .wt-cp-label:hover { text-decoration: underline; }
        .wt-cp-label.is-hidden { display: none; }
        .wt-cp-pole { position: absolute; top: 23px; bottom: 22px; border-left: 1px dotted #9aa6b8; }
        .wt-cp { position: absolute; z-index: 1; bottom: 2px; width: 20px; height: 20px; margin-left: -10px; padding: 0; display: grid; place-items: center; border: 2px solid #fff; border-radius: 50%; color: #fff; font-size: 9px; background: #9aa6b8; box-shadow: 0 1px 3px rgba(8, 33, 73, .25); transition: transform .12s; }
        .wt-cp:hover { transform: scale(1.25); }
        .wt-cp.cp-done { background: var(--wt-green); } .wt-cp.cp-late { background: var(--wt-red); }
        .wt-cp.cp-progress { background: var(--wt-blue); animation: wt-pulse 1.8s ease-out infinite; }
        .wt-cp-label.cp-done { color: #0c7f3e; } .wt-cp-label.cp-late { color: #c21f30; } .wt-cp-label.cp-progress { color: #1b5fc1; }
        @keyframes wt-pulse { 0% { box-shadow: 0 0 0 0 rgba(47, 128, 237, .55); } 100% { box-shadow: 0 0 0 9px rgba(47, 128, 237, 0); } }
        .wt-lane-note { font-size: 12px; line-height: 1.35; }
        .wt-lane-note b { display: block; font-size: 12.5px; }
        .tier-sprint b { color: var(--wt-green); } .tier-jog b { color: var(--wt-amber); } .tier-walk b { color: var(--wt-red); } .tier-rest b { color: var(--wt-gray); } .tier-finish b { color: var(--wt-blue); }

        /* Runner */
        .wt-runner { width: 54px; height: 56px; overflow: visible; }
        .wt-runner .figure { transform-origin: 27px 32px; transform: rotate(var(--lean, 0deg)); animation: wt-bob var(--stride) ease-in-out infinite; }
        .wt-runner .limb { stroke: var(--c); stroke-width: 4; stroke-linecap: round; fill: none; }
        .wt-runner .torso { stroke: var(--c); stroke-width: 5; stroke-linecap: round; }
        .wt-runner .head { fill: var(--c); }
        .wt-runner .arm-a, .wt-runner .arm-b { transform-origin: 27px 20px; }
        .wt-runner .leg-a, .wt-runner .leg-b { transform-origin: 27px 33px; }
        .wt-runner .arm-a, .wt-runner .leg-b { animation: wt-swing-a var(--stride) ease-in-out infinite; }
        .wt-runner .arm-b, .wt-runner .leg-a { animation: wt-swing-b var(--stride) ease-in-out infinite; }
        @keyframes wt-swing-a { 0%, 100% { transform: rotate(calc(var(--swing) * -1)); } 50% { transform: rotate(var(--swing)); } }
        @keyframes wt-swing-b { 0%, 100% { transform: rotate(var(--swing)); } 50% { transform: rotate(calc(var(--swing) * -1)); } }
        @keyframes wt-bob { 0%, 100% { transform: rotate(var(--lean, 0deg)) translateY(0); } 50% { transform: rotate(var(--lean, 0deg)) translateY(var(--bob, -2px)); } }
        .tier-sprint .wt-runner { --stride: .34s; --swing: 42deg; --lean: 14deg; --bob: -3px; }
        .tier-jog .wt-runner { --stride: .72s; --swing: 28deg; --lean: 7deg; --bob: -2px; }
        .tier-walk .wt-runner { --stride: 1.5s; --swing: 14deg; --lean: 0deg; --bob: -1px; }
        .tier-rest .wt-runner, .tier-finish .wt-runner { --stride: 1s; --swing: 0deg; --lean: 0deg; --bob: -3px; }
        .tier-rest .wt-runner .figure { animation-duration: 3s; --bob: -1px; }
        .tier-finish .arm-a { transform: rotate(125deg) !important; animation: none; }
        .tier-finish .arm-b { transform: rotate(-125deg) !important; animation: none; }
        .tier-finish .leg-a, .tier-finish .leg-b, .tier-rest .leg-a, .tier-rest .leg-b, .tier-rest .arm-a, .tier-rest .arm-b { animation: none; }
        .wt-fx { position: absolute; font-size: 11px; pointer-events: none; opacity: 0; }
        .wt-fx-dust { left: -2px; bottom: 2px; color: #a9b4c4; animation: wt-dust .5s ease-out infinite; }
        .wt-fx-dust.two { left: 6px; animation-delay: .25s; }
        @keyframes wt-dust { 0% { opacity: .9; transform: translate(0, 0) scale(.6); } 100% { opacity: 0; transform: translate(-14px, -6px) scale(1.2); } }
        .wt-fx-sweat { left: 36px; top: 2px; color: #58a6ff; animation: wt-sweat 1.5s ease-in infinite; }
        @keyframes wt-sweat { 0% { opacity: 1; transform: translate(0, 0); } 100% { opacity: 0; transform: translate(10px, 18px); } }
        .wt-fx-zzz { left: 34px; top: -2px; color: var(--wt-muted); font-weight: 700; animation: wt-zzz 2.6s ease-in-out infinite; }
        @keyframes wt-zzz { 0% { opacity: 0; transform: translateY(6px); } 40% { opacity: 1; } 100% { opacity: 0; transform: translate(8px, -8px); } }
        .wt-fx-cup { left: 37px; top: 0; color: #e0a400; opacity: 1; font-size: 14px; animation: wt-cup 1s ease-in-out infinite; }
        @keyframes wt-cup { 50% { transform: translateY(-4px) rotate(10deg); } }
        .wt-fx-flame { left: 2px; bottom: 16px; color: #f2994a; font-size: 13px; opacity: 1; animation: wt-flame .28s ease-in-out infinite alternate; }
        @keyframes wt-flame { to { transform: scale(1.3) rotate(-14deg); color: #eb5757; } }
        .wt-speed { position: absolute; left: -22px; top: 18px; width: 18px; height: 2px; border-radius: 2px; background: #9fb4d3; animation: wt-speed .4s linear infinite; }
        .wt-speed.two { top: 30px; left: -30px; animation-delay: .2s; }
        @keyframes wt-speed { 0% { opacity: .9; transform: translateX(10px); } 100% { opacity: 0; transform: translateX(-14px); } }
        .wt-fx-note { left: 38px; top: 6px; color: #e08a00; animation: wt-note 2s ease-out infinite; }
        .wt-fx-note.two { left: 44px; animation-delay: 1s; }
        @keyframes wt-note { 0% { opacity: 0; transform: translateY(8px); } 30% { opacity: 1; } 100% { opacity: 0; transform: translate(9px, -14px) rotate(16deg); } }
        .wt-fx-rain { left: -8px; top: -1px; color: #7f8ca1; font-size: 15px; opacity: 1; animation: wt-rain 2.4s ease-in-out infinite; }
        @keyframes wt-rain { 50% { transform: translateX(5px); } }
        .wt-fx-zzz.two { left: 42px; animation-delay: 1.2s; }
        .wt-fx-mug { left: 40px; bottom: 2px; color: #a0764a; opacity: 1; font-size: 13px; }
        .wt-fx-alarm { left: 24px; top: -7px; color: #dc3545; font-size: 15px; opacity: 1; animation: wt-alarm .3s ease-in-out infinite alternate; }
        @keyframes wt-alarm { to { transform: translateY(-4px) scale(1.25); } }
        .wt-fx-fix { left: 44px; top: 22px; color: #1b5fc1; font-size: 15px; opacity: 1; transform-origin: 80% 80%; animation: wt-fix .5s ease-in-out infinite alternate; }
        @keyframes wt-fix { from { transform: rotate(-30deg); } to { transform: rotate(35deg); } }

        /* Dekorasi lintasan */
        .wt-sun { position: absolute; top: var(--sun-top); right: 84px; color: var(--sun); font-size: var(--sun-size); animation: wt-spin 12s linear infinite; }
        .wt-race[data-sky="dawn"] .wt-star { opacity: .5; }
        .wt-prop { position: absolute; left: 100%; bottom: 25px; color: var(--prop); font-size: 15px; filter: var(--shade); animation: wt-drift var(--pdur, 30s) linear infinite; animation-delay: calc(var(--pdur, 30s) * var(--at, 0) * -1); }
        .wt-prop.small { font-size: 11px; }
        .tier-sprint .wt-prop { --pdur: 14s; } .tier-jog .wt-prop { --pdur: 30s; } .tier-walk .wt-prop { --pdur: 70s; }
        .tier-rest .wt-prop, .tier-finish .wt-prop, .incident-panic .wt-prop, .incident-fixing .wt-prop { animation-play-state: paused; }
        .wt-boat { position: absolute; left: 100%; bottom: 36px; color: #fff; font-size: 13px; filter: var(--shade); animation: wt-drift 46s linear infinite; }

        /* Tema hari khusus */
        .wt-bunting { position: absolute; left: 0; right: 0; top: 0; filter: drop-shadow(0 1px 1px rgba(8, 33, 73, .35)); pointer-events: none; }
        .wt-bunting i { display: block; height: 10px; background: repeating-linear-gradient(90deg, #dc3545 0 16px, #fff 16px 32px); -webkit-mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 10'%3E%3Cpath d='M0 0H16L8 10Z'/%3E%3C/svg%3E") 0 0 / 16px 10px repeat-x; mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 10'%3E%3Cpath d='M0 0H16L8 10Z'/%3E%3C/svg%3E") 0 0 / 16px 10px repeat-x; }
        .wt-firework { position: absolute; width: 4px; height: 4px; border-radius: 50%; background: currentColor; box-shadow: 0 -12px, 8px -8px, 12px 0, 8px 8px, 0 12px, -8px 8px, -12px 0, -8px -8px; animation: wt-firework 1.9s ease-out infinite; }
        @keyframes wt-firework { 0% { opacity: 0; transform: scale(.15); } 15% { opacity: 1; } 70% { opacity: .9; transform: scale(1.3); } 100% { opacity: 0; transform: scale(1.5) translateY(4px); } }
        .wt-sky-note { position: absolute; color: #9b51e0; font-size: 12px; animation: wt-sky-note 3.2s ease-in-out infinite; }
        @keyframes wt-sky-note { 0% { opacity: 0; transform: translateY(10px); } 30%, 70% { opacity: 1; } 100% { opacity: 0; transform: translate(10px, -10px) rotate(14deg); } }
        .wt-race[data-dark] .wt-sky-note { color: #d9b8ff; }
        .wt-plane { position: absolute; top: 30px; left: 100%; display: flex; align-items: center; gap: 3px; color: #4b5a73; font-size: 13px; white-space: nowrap; animation: wt-plane 26s linear infinite; }
        .wt-plane i { transform: scaleX(-1); }
        .wt-plane span { padding: 0 7px; border-radius: 3px; background: #fff; box-shadow: 0 1px 3px rgba(8, 33, 73, .25); color: var(--wt-navy); font-size: 10px; font-weight: 700; line-height: 15px; }
        .wt-race[data-dark] .wt-plane { color: #c7d2e6; }
        @keyframes wt-plane { to { left: -260px; } }
        @keyframes wt-spin { to { transform: rotate(360deg); } }
        .wt-cloud { position: absolute; left: 100%; color: var(--cloud); font-size: 21px; opacity: .95; animation: wt-drift var(--dur, 20s) linear infinite; }
        .wt-cloud.mini { font-size: 13px; opacity: .8; }
        @keyframes wt-drift { to { left: -40px; } }
        .wt-bird { position: absolute; top: 34px; left: -24px; color: #6d84a8; font-size: 11px; animation: wt-fly 21s linear infinite; }
        @keyframes wt-fly { 0% { left: -24px; transform: translateY(0); } 25% { transform: translateY(-6px); } 50% { transform: translateY(4px); } 75% { transform: translateY(-5px); } 100% { left: 102%; transform: translateY(0); } }
        .wt-crowd { position: absolute; right: 72px; bottom: 27px; display: flex; gap: 1px; font-size: 13px; opacity: .85; }
        .wt-crowd i { animation: wt-crowd 1.6s ease-in-out infinite; }
        .wt-crowd.cheer i { animation-duration: .45s; }
        @keyframes wt-crowd { 50% { transform: translateY(-5px); } }
        .wt-balloon { position: absolute; bottom: -18px; width: 13px; height: 16px; border-radius: 50% 50% 48% 48%; animation: wt-balloon 4.4s ease-in infinite; }
        .wt-balloon::after { content: ""; position: absolute; left: 6px; top: 16px; height: 9px; border-left: 1px solid rgba(8, 33, 73, .35); }
        @keyframes wt-balloon { 0% { bottom: -18px; transform: translateX(0); opacity: 1; } 50% { transform: translateX(7px); } 100% { bottom: 110px; transform: translateX(-4px); opacity: .6; } }
        .wt-spark { position: absolute; color: #f2c94c; font-size: 11px; animation: wt-twinkle 1.2s ease-in-out infinite; }
        .wt-say { position: absolute; z-index: 3; left: 49px; bottom: 30px; padding: 1px 8px; border-radius: 9px; background: #fff; box-shadow: 0 1px 4px rgba(8, 33, 73, .22); color: var(--wt-ink); font-size: 10.5px; font-weight: 600; white-space: nowrap; }
        .wt-say.left { left: auto; right: 49px; }
        .wt-say.pop { animation: wt-say .35s ease; }
        @keyframes wt-say { from { opacity: 0; transform: scale(.7); } to { opacity: 1; transform: none; } }

        /* Mode ada masalah */
        .incident-panic .wt-runner { --stride: .2s; --swing: 24deg; --lean: 0deg; --bob: -2px; }
        .incident-fixing .wt-runner { --stride: .5s; --swing: 34deg; --lean: 10deg; --bob: -1px; }
        .incident-panic .wt-track, .incident-fixing .wt-track { animation: wt-track-alarm 1.2s ease-in-out infinite; }
        .incident-fixing .wt-track { animation-duration: 2.4s; }
        @keyframes wt-track-alarm { 0%, 100% { box-shadow: inset 0 0 0 1px rgba(220, 53, 69, .35); } 50% { box-shadow: inset 0 0 0 3px rgba(220, 53, 69, .9), inset 0 0 22px rgba(220, 53, 69, .28); } }
        .wt-obstacle { position: absolute; z-index: 1; bottom: 5px; color: #e8590c; font-size: 25px; animation: wt-obstacle .7s ease-in-out infinite alternate; }
        .incident-fixing .wt-obstacle { animation: wt-obstacle-fix .5s ease-in-out infinite alternate; }
        @keyframes wt-obstacle { to { color: #dc3545; transform: scale(1.1); } }
        @keyframes wt-obstacle-fix { to { transform: rotate(3deg); } }
        .wt-lane-note.incident b { color: var(--wt-red); }
        .wt-runner-box.jump .wt-runner { animation: wt-jump 1s ease-out 1; }
        @keyframes wt-jump { 0% { transform: translateY(0); } 45% { transform: translateY(-30px) rotate(12deg); } 100% { transform: translateY(0); } }

        /* Tabs + filters */
        .wt-panel { padding: 14px 18px 18px; }
        .wt-tabs { display: inline-flex; gap: 3px; padding: 4px; background: var(--wt-soft); border-radius: 10px; }
        .wt-tabs button { border: 0; background: transparent; padding: 6px 14px; border-radius: 7px; font-size: 13px; font-weight: 600; color: var(--wt-muted); }
        .wt-tabs button.active { background: #fff; color: var(--wt-navy); box-shadow: 0 1px 4px rgba(8, 33, 73, .15); }
        .wt-filters { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 10px; margin: 12px 0; }
        .wt-filterbar { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin: 12px 0; }
        .wt-filterbar > div { min-width: 0; }
        .wt-search { position: relative; flex: 1 1 240px; }
        .wt-search i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--wt-muted); font-size: 13px; pointer-events: none; }
        .wt-search input { padding-left: 34px; border-radius: 10px; }
        .wt-fb-programmer { flex: 0 1 220px; }
        .wt-fb-date { flex: 0 1 190px; }
        .wt-fb-date .input-group-text { font-size: 12px; font-weight: 600; color: var(--wt-muted); background: var(--wt-soft); }
        .wt-filter-btn { display: inline-flex; align-items: center; gap: 7px; height: 38px; padding: 0 14px; border: 1px solid var(--wt-line); border-radius: 10px; background: #fff; color: var(--wt-navy); font-size: 13px; font-weight: 600; transition: background .15s, border-color .15s; }
        .wt-filter-btn:hover, .wt-filter-btn[aria-expanded="true"] { border-color: var(--wt-navy); background: var(--wt-soft); }
        .wt-filter-btn .fa-chevron-down { font-size: 10px; transition: transform .2s; }
        .wt-filter-btn[aria-expanded="true"] .fa-chevron-down { transform: rotate(180deg); }
        .wt-filter-badge { min-width: 19px; height: 19px; padding: 0 5px; border-radius: 10px; background: var(--wt-blue); color: #fff; font-size: 11px; line-height: 19px; text-align: center; }
        .wt-filter-reset { padding: 0 6px; border: 0; background: none; color: var(--wt-blue); font-size: 12.5px; font-weight: 600; }
        .wt-filter-reset:hover { text-decoration: underline; }
        .wt-filter-more { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 10px; margin: -2px 0 12px; padding: 12px; border-radius: 12px; background: var(--wt-soft); }
        .wt-filter-more label { font-size: 12px; font-weight: 600; color: var(--wt-muted); margin-bottom: 3px; }

        /* Daftar kartu baris */
        .wt-rows { display: flex; flex-direction: column; gap: 8px; }
        .wt-row { display: grid; grid-template-columns: minmax(0, 1fr) 200px 170px 100px 180px; gap: 16px; align-items: center; padding: 11px 14px; border: 1px solid var(--wt-line); border-left: 4px solid var(--wt-gray); border-radius: 12px; background: #fff; cursor: pointer; animation: wt-rise .3s ease both; transition: transform .14s, box-shadow .14s, border-color .14s; }
        .wt-row:hover { transform: translateY(-1px); border-color: #cfd8e6; box-shadow: 0 6px 16px rgba(8, 33, 73, .1); }
        .wt-row.b-aman, .wt-row.b-selesai { border-left-color: var(--wt-green); } .wt-row.b-mepet { border-left-color: var(--wt-amber); } .wt-row.b-telat, .wt-row.b-selesai_telat { border-left-color: var(--wt-red); }
        .wt-row-chips { display: flex; flex-wrap: wrap; gap: 4px; margin-bottom: 3px; }
        .wt-row .wt-title { font-size: 13.5px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .wt-row-meta { display: flex; flex-wrap: wrap; gap: 3px 10px; font-size: 11.5px; color: var(--wt-muted); }
        .wt-row-meta i { margin-right: 3px; }
        .wt-row-meta .done { color: var(--wt-green); font-weight: 600; }
        .wt-row-progress { font-size: 12px; }
        .wt-row-progress b { font-size: 13px; }
        .wt-row-date { font-size: 12.5px; line-height: 1.3; }
        .wt-row-date .wt-sub { font-size: 11px; }
        .wt-row-health .wt-sub { margin-top: 3px; line-height: 1.3; }

        /* Arena bisa dilipat */
        .wt-fold { display: inline-flex; align-items: center; gap: 6px; padding: 3px 11px; border: 1px solid var(--wt-line); border-radius: 16px; background: #fff; color: var(--wt-muted); font-size: 12px; font-weight: 600; transition: background .15s, color .15s; }
        .wt-fold:hover { background: var(--wt-soft); color: var(--wt-navy); }
        .wt-fold i { font-size: 10px; transition: transform .25s; }
        .wt-race.folded .wt-fold i { transform: rotate(-90deg); }
        .wt-race.folded { padding-bottom: 14px; }
        .wt-race.folded .wt-legend { display: none !important; }
        .wt-filters label, .wt-form label { font-size: 12px; font-weight: 600; color: var(--wt-muted); margin-bottom: 3px; }

        /* Table */
        .wt-table { width: 100%; font-size: 13px; border-collapse: separate; border-spacing: 0; min-width: 980px; }
        .wt-table th { font-size: 11.5px; text-transform: uppercase; letter-spacing: .03em; color: var(--wt-muted); font-weight: 700; padding: 8px 10px; border-bottom: 2px solid var(--wt-line); white-space: nowrap; }
        .wt-table td { padding: 10px; border-bottom: 1px solid var(--wt-line); vertical-align: middle; }
        .wt-table tbody tr { cursor: pointer; transition: background .12s; }
        .wt-table tbody tr:hover { background: var(--wt-soft); }
        .wt-title { font-weight: 600; }
        .wt-sub { font-size: 11.5px; color: var(--wt-muted); }
        .wt-chip { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 20px; font-size: 11px; font-weight: 700; white-space: nowrap; }
        .t-plan { background: #e7f0ff; color: #1b5fc1; } .t-bug { background: #ffe9ec; color: #c92a3c; } .t-request { background: #f1e9ff; color: #7338c9; }
        .l-major { background: #ffe5d4; color: #b84c00; } .l-minor { background: #e6f6ff; color: #0b6f9e; } .l-tidak { background: #eceff4; color: #5b6577; }
        .h-aman, .h-selesai { background: #e0f7ea; color: #0c7f3e; } .h-mepet { background: #fff2d6; color: #a86700; } .h-telat, .h-selesai_telat { background: #ffe3e6; color: #c21f30; } .h-belum_dinilai, .h-ditolak { background: #eceff4; color: #5b6577; }
        .wt-bar { position: relative; height: 8px; min-width: 90px; border-radius: 8px; background: #e7ecf4; overflow: hidden; }
        .wt-bar span { position: absolute; left: 0; top: 0; bottom: 0; border-radius: 8px; transition: width .9s ease; }
        .fill-aman, .fill-selesai { background: linear-gradient(90deg, #12a150, #4fd18b); } .fill-mepet { background: linear-gradient(90deg, #e08a00, #f7b84b); } .fill-telat, .fill-selesai_telat { background: linear-gradient(90deg, #dc3545, #f4727e); } .fill-belum_dinilai, .fill-ditolak { background: #9aa6b8; }
        .wt-mini-avatar { width: 24px; height: 24px; font-size: 10px; }
        .wt-empty { text-align: center; padding: 36px 16px; color: var(--wt-muted); }
        .wt-empty > i { font-size: 34px; color: #b9c6da; margin-bottom: 10px; display: block; }

        /* Kanban */
        .wt-kanban { display: grid; grid-template-columns: repeat(4, minmax(220px, 1fr)); gap: 12px; overflow-x: auto; padding-bottom: 6px; }
        .wt-col { background: var(--wt-soft); border-top: 3px solid var(--col); border-radius: 12px; padding: 10px; min-height: 240px; transition: outline .1s; }
        .wt-col.over { outline: 2px dashed var(--wt-blue); }
        .wt-col-head { display: flex; justify-content: space-between; font-weight: 700; font-size: 13px; padding: 2px 4px 9px; }
        .wt-kcard { background: #fff; border-radius: 10px; border-left: 5px solid var(--wt-gray); padding: 9px 11px; margin-bottom: 9px; font-size: 12.5px; box-shadow: 0 1px 4px rgba(8, 33, 73, .08); cursor: grab; transition: transform .14s, box-shadow .14s; }
        .wt-kcard:hover { transform: translateY(-2px); box-shadow: 0 8px 18px rgba(8, 33, 73, .16); }
        .wt-kcard.b-aman, .wt-kcard.b-selesai { border-left-color: var(--wt-green); } .wt-kcard.b-mepet { border-left-color: var(--wt-amber); } .wt-kcard.b-telat, .wt-kcard.b-selesai_telat { border-left-color: var(--wt-red); }

        /* Timeline */
        .wt-tl-scroll { overflow-x: auto; cursor: grab; user-select: none; }
        .wt-tl-scroll.panning, .wt-tl-scroll.panning * { cursor: grabbing !important; }
        .wt-tl-scroll .wt-tl-bar { cursor: pointer; }
        .wt-tl { position: relative; width: max-content; min-width: 100%; }
        .wt-tl-row { display: grid; border-bottom: 1px solid var(--wt-line); transition: background .1s; }
        .wt-tl-row.drop-target .wt-tl-area { background-color: #e9f1fe; box-shadow: inset 0 0 0 2px var(--wt-blue); }
        .wt-tl-head .wt-tl-area { height: 42px; background: none; }
        .wt-tl-side { position: sticky; left: 0; z-index: 4; display: flex; flex-direction: column; justify-content: center; gap: 2px; min-width: 0; padding: 4px 10px; background: #fff; box-shadow: 1px 0 0 var(--wt-line); }
        .wt-tl-person { display: flex; align-items: center; gap: 7px; font-size: 12.5px; font-weight: 600; }
        .wt-tl-person b { overflow: hidden; font-weight: 600; white-space: nowrap; text-overflow: ellipsis; }
        .wt-tl-more { align-self: flex-start; padding: 0 7px; border: 1px solid var(--wt-line); border-radius: 10px; background: #fff; color: #1b5fc1; font-size: 11px; font-weight: 700; }
        .wt-tl-more:hover { background: var(--wt-soft); }
        .wt-tl-division { position: sticky; left: 0; width: max-content; padding: 10px 10px 4px; font-size: 11.5px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: var(--wt-navy); }
        .wt-tl-area { position: relative; background-image: linear-gradient(90deg, var(--wt-line) 1px, transparent 1px); background-size: var(--day) 100%; }
        .wt-tl-tick { position: absolute; top: 22px; font-size: 10.5px; color: var(--wt-muted); text-align: center; white-space: nowrap; }
        .wt-tl-todaycol { position: absolute; top: 0; bottom: 0; z-index: 0; background: rgba(220, 53, 69, .07); border-left: 2px solid var(--wt-red); pointer-events: none; }
        .wt-tl-todaylabel { position: absolute; top: 3px; z-index: 1; color: var(--wt-red); font-size: 10.5px; font-weight: 700; white-space: nowrap; }
        .wt-tl-bar { position: absolute; z-index: 2; height: 24px; padding: 0 12px 0 6px; overflow: hidden; border: 1px solid #cfd8e6; border-left: 4px solid var(--wt-gray); border-radius: 6px; background: #f4f7fc; color: var(--wt-ink); font-size: 11.5px; font-weight: 600; line-height: 22px; text-align: left; white-space: nowrap; box-shadow: 0 1px 3px rgba(8, 33, 73, .16); transition: box-shadow .12s, transform .12s; }
        .wt-tl-bar:hover { transform: translateY(-1px); box-shadow: 0 5px 12px rgba(8, 33, 73, .28); }
        .wt-tl-avatar { display: inline-grid; width: 17px; height: 17px; margin-right: 4px; font-size: 7.5px; vertical-align: 1px; }
        .wt-tl-bar.can-drag { cursor: grab; touch-action: pan-y; }
        .wt-tl-bar.dragging { z-index: 5; cursor: grabbing; opacity: .92; box-shadow: 0 8px 20px rgba(8, 33, 73, .35); transition: none; }
        .wt-tl-fill { position: absolute; left: 0; top: 0; bottom: 0; background: #dbe2ee; }
        .wt-tl-text { position: relative; display: block; overflow: hidden; text-overflow: ellipsis; }
        .wt-tl-text i { color: var(--wt-red); }
        .wt-tl-handle { position: absolute; top: 0; right: 0; bottom: 0; width: 9px; cursor: ew-resize; background: linear-gradient(90deg, transparent 3px, rgba(8, 33, 73, .22) 3px 4px, transparent 4px 6px, rgba(8, 33, 73, .22) 6px 7px, transparent 7px); }
        .tl-aman, .tl-selesai { border-color: #7fd6a4; border-left-color: var(--wt-green); background: #e3f8ec; } .tl-aman .wt-tl-fill, .tl-selesai .wt-tl-fill { background: #86dfab; }
        .tl-mepet { border-color: #f0c672; border-left-color: var(--wt-amber); background: #fff4dc; } .tl-mepet .wt-tl-fill { background: #fbcb6e; }
        .tl-telat, .tl-selesai_telat { border-color: #f3a3ab; border-left-color: var(--wt-red); background: #ffe8ea; } .tl-telat .wt-tl-fill, .tl-selesai_telat .wt-tl-fill { background: #fba9b1; }
        .tl-selesai, .tl-selesai_telat, .tl-ditolak { opacity: .7; }
        .wt-tl-over { position: absolute; z-index: 1; height: 24px; border-radius: 0 6px 6px 0; background: repeating-linear-gradient(45deg, #ffb3bb 0 5px, #ffe3e6 5px 10px); pointer-events: none; }
        .wt-tl-nav { display: flex; flex-wrap: wrap; align-items: center; gap: 8px 14px; margin-bottom: 8px; }
        .wt-tl-nav label { display: flex; align-items: center; gap: 6px; margin: 0; font-size: 12.5px; font-weight: 600; color: var(--wt-muted); }
        .wt-tl-nav input[type="date"] { width: 150px; }
        .wt-tl-hint { margin-bottom: 8px; padding: 6px 10px; border-radius: 8px; background: #fff4dc; color: #7a5300; font-size: 12px; }
        .wt-tl-bar.clip-l { border-left-style: dashed; border-top-left-radius: 0; border-bottom-left-radius: 0; }
        .wt-tl-bar.clip-r { border-right: 2px dashed currentColor; border-top-right-radius: 0; border-bottom-right-radius: 0; }
        .wt-tl-tip { position: fixed; z-index: 2000; display: none; padding: 4px 10px; border-radius: 8px; background: var(--wt-navy); color: #fff; font-size: 12px; font-weight: 600; white-space: nowrap; pointer-events: none; }

        /* Modal */
        .wt-form .form-control { font-size: 13px; }
        .wt-color { display: flex; gap: 7px; flex-wrap: wrap; }
        .wt-color button { width: 28px; height: 28px; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 1px var(--wt-line); }
        .wt-color button.active { box-shadow: 0 0 0 2px var(--wt-navy); }
        .wt-member-avatar { border: 0; padding: 0; cursor: pointer; transition: transform .12s, box-shadow .12s; }
        .wt-member-avatar:hover { transform: scale(1.12); box-shadow: 0 0 0 2px #fff, 0 0 0 4px var(--wt-line); }
        .wt-member-colors { padding: 6px 0 10px 34px; }
        .wt-member-colors button { width: 24px; height: 24px; }
        .wt-color-custom { position: relative; display: inline-grid; place-items: center; width: 24px; height: 24px; margin: 0; border-radius: 50%; background: var(--wt-soft); color: var(--wt-muted); font-size: 11px; box-shadow: 0 0 0 1px var(--wt-line); cursor: pointer; }
        .wt-color-custom input { position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
        .wt-log { border-left: 2px solid var(--wt-line); padding: 0 0 12px 14px; position: relative; font-size: 12.5px; }
        .wt-log::before { content: ""; position: absolute; left: -6px; top: 3px; width: 10px; height: 10px; border-radius: 50%; background: var(--wt-blue); }
        /* Form kerjaan ringkas */
        .wt-modal-md { max-width: 640px; }
        .wt-form .wt-title-input { padding: 10px 14px; border-radius: 10px; font-size: 16px; font-weight: 600; }
        .wt-type-pills { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
        .wt-type-pills button { display: inline-flex; align-items: center; gap: 6px; padding: 5px 14px; border: 1px solid var(--wt-line); border-radius: 20px; background: #fff; color: var(--wt-muted); font-size: 12.5px; font-weight: 600; transition: background .15s, color .15s, border-color .15s, transform .15s; }
        .wt-type-pills button:hover { transform: translateY(-1px); border-color: #c6d0de; }
        .wt-type-pills button.active[data-type="plan"] { background: #e7f0ff; border-color: #9cc0f5; color: #1b5fc1; }
        .wt-type-pills button.active[data-type="bug"] { background: #ffe9ec; border-color: #f3aab3; color: #c92a3c; }
        .wt-type-pills button.active[data-type="request"] { background: #f1e9ff; border-color: #c8adf0; color: #7338c9; }
        .wt-mini-head { display: flex; align-items: center; gap: 7px; margin: 20px 0 6px; font-size: 12.5px; font-weight: 700; color: var(--wt-navy); }
        .wt-mini-head > i { color: var(--wt-blue); }
        #wt-item-form .modal-body label { color: var(--wt-navy); font-size: 12.5px; }
        .wt-suffix { position: relative; }
        .wt-suffix .form-control { padding-right: 38px; }
        .wt-suffix > span { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-size: 12.5px; color: var(--wt-muted); pointer-events: none; }

        /* Bagian di dalam modal */
        .wt-sec { padding: 14px 0; border-top: 1px solid var(--wt-line); }
        .wt-sec:first-child { padding-top: 0; border-top: 0; }
        .wt-sec-title { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; font-size: 12px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; color: var(--wt-navy); }
        .wt-sec-title > i { width: 24px; height: 24px; display: grid; place-items: center; border-radius: 7px; background: #e9f1fe; color: var(--wt-blue); font-size: 11px; }
        .wt-sec-title .ms-auto { text-transform: none; letter-spacing: 0; }

        /* Poin ala to-do */
        .wt-todo-count { font-size: 12px; font-weight: 600; color: var(--wt-muted); }
        .wt-todo-count.all-done { color: var(--wt-green); }
        .wt-todo-bar { height: 5px; margin: -2px 0 8px; border-radius: 5px; background: #e7ecf4; overflow: hidden; }
        .wt-todo-bar span { display: block; height: 100%; border-radius: 5px; background: linear-gradient(90deg, #12a150, #4fd18b); transition: width .5s cubic-bezier(.22, .8, .3, 1); }
        .wt-point { display: flex; align-items: flex-start; gap: 4px; padding: 2px 4px; border-radius: 9px; font-size: 13.5px; transition: background .15s; }
        .wt-point:hover, .wt-point:focus-within { background: var(--wt-soft); }
        .wt-point.drop-over { background: #e9f1fe; box-shadow: inset 0 0 0 2px var(--wt-blue); }
        .wt-point-body { flex: 1; min-width: 0; padding: 4px 0; }
        .wt-point-body > input { margin: 2px 0; }
        .wt-point-toggle { display: flex; align-items: flex-start; gap: 11px; width: 100%; padding: 2px 0; border: 0; background: none; color: var(--wt-ink); text-align: left; }
        .wt-point-toggle:disabled { opacity: .6; }
        .wt-check { flex: none; width: 20px; height: 20px; margin-top: 1px; display: grid; place-items: center; border: 2px solid #b7c2d3; border-radius: 50%; color: #fff; font-size: 10px; transition: background .2s, border-color .2s, transform .2s; }
        .wt-check i { opacity: 0; transform: scale(.4); transition: opacity .2s, transform .2s; }
        .wt-point-toggle:hover .wt-check { border-color: var(--wt-green); }
        .wt-point-toggle:hover .wt-check i { opacity: .35; transform: none; color: var(--wt-green); }
        .wt-point.done .wt-check { border-color: var(--wt-green); background: var(--wt-green); }
        .wt-point.done .wt-check i { opacity: 1; transform: none; color: #fff; }
        .wt-check.pop { animation: wt-check-pop .45s cubic-bezier(.2, 1.6, .4, 1); }
        @keyframes wt-check-pop { 0% { transform: scale(.6); } 60% { transform: scale(1.25); } 100% { transform: scale(1); } }
        .wt-point-text { word-break: break-word; transition: color .2s; }
        .wt-point.done .wt-point-text { text-decoration: line-through; text-decoration-color: rgba(104, 116, 138, .6); color: #8a94a6; }
        .wt-point-body > .wt-sub, .wt-point-body > .wt-files { padding-left: 31px; }
        .wt-point-actions { display: flex; flex-shrink: 0; padding-top: 3px; opacity: 0; transition: opacity .15s; }
        .wt-point:hover .wt-point-actions, .wt-point:focus-within .wt-point-actions { opacity: 1; }
        @media (hover: none) { .wt-point-actions { opacity: 1; } }
        .wt-point-actions .btn { padding: 1px 6px; color: #8a94a6; }
        .wt-point-actions .btn:hover { color: var(--wt-navy); }
        .wt-point-actions .wt-del-btn:hover { color: var(--wt-red); }
        .wt-todo-add { display: flex; align-items: center; gap: 11px; margin-top: 4px; padding: 5px 4px; border-radius: 9px; }
        .wt-todo-add:focus-within { background: var(--wt-soft); }
        .wt-todo-add > i { flex: none; width: 20px; text-align: center; color: var(--wt-blue); }
        .wt-todo-add input { flex: 1; min-width: 0; padding: 3px 0; border: 0; border-bottom: 1px dashed #c6d0de; background: none; font-size: 13.5px; outline: 0; }
        .wt-todo-add input:focus { border-bottom-color: var(--wt-blue); }
        .wt-todo-add .btn { padding: 2px 10px; font-size: 12px; }
        .wt-todo-empty { padding: 4px 4px 2px 35px; font-size: 12.5px; color: var(--wt-muted); }

        /* Lampiran: kotak drop + galeri */
        .wt-drop { display: flex; align-items: center; gap: 12px; width: 100%; margin: 0; padding: 12px 16px; border: 2px dashed #c6d0de; border-radius: 12px; background: #fafcff; color: var(--wt-muted); font-size: 12.5px; cursor: pointer; transition: border-color .15s, background .15s; }
        .wt-drop:hover, .wt-drop.over { border-color: var(--wt-blue); background: #eef5ff; }
        .wt-drop.over .wt-drop-icon { transform: translateY(-3px) scale(1.08); }
        .wt-drop input[type=file] { display: none; }
        .wt-drop > span { pointer-events: none; }
        .wt-sec-title .wt-range-value { font-size: 22px; letter-spacing: 0; }
        .wt-drop-icon { flex: none; width: 38px; height: 38px; display: grid; place-items: center; border-radius: 10px; background: #e9f1fe; color: var(--wt-blue); font-size: 17px; transition: transform .2s; }
        .wt-drop b { color: var(--wt-navy); }
        .wt-drop.compact { padding: 8px 12px; }
        .wt-drop.compact .wt-drop-icon { width: 30px; height: 30px; font-size: 14px; }
        .wt-files { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; }
        .wt-file { position: relative; display: inline-flex; animation: wt-rise .25s ease both; }
        .wt-file-box { display: inline-flex; align-items: center; gap: 7px; max-width: 220px; height: 38px; padding: 0 10px; border: 1px solid var(--wt-line); border-radius: 9px; background: #fff; color: var(--wt-navy); font-size: 12px; text-decoration: none; transition: border-color .15s, box-shadow .15s; }
        a.wt-file-box:hover { border-color: var(--wt-blue); box-shadow: 0 3px 10px rgba(8, 33, 73, .12); color: var(--wt-navy); }
        .wt-file-box span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .wt-file-box i { font-size: 15px; }
        .wt-file-box .fa-file-pdf { color: #d63a3a; } .wt-file-box .fa-file-excel { color: #1d7a45; } .wt-file-box .fa-file-word { color: #2b5fb4; } .wt-file-box .fa-file-zipper { color: #a86700; } .wt-file-box .fa-file-code { color: #e34c26; } .wt-file-box .fa-file-lines { color: var(--wt-muted); }
        .wt-file.is-image .wt-file-box { width: 76px; height: 76px; padding: 0; overflow: hidden; border-radius: 11px; }
        .wt-file.is-image img { width: 100%; height: 100%; object-fit: cover; transition: transform .25s; }
        .wt-file.is-image a.wt-file-box:hover img { transform: scale(1.07); }
        .wt-file.pending .wt-file-box { border-style: dashed; }
        .wt-file-del { position: absolute; top: -7px; right: -7px; width: 20px; height: 20px; padding: 0; border: 2px solid #fff; border-radius: 50%; background: #6b7a8c; color: #fff; font-size: 12px; line-height: 15px; opacity: 0; transition: opacity .15s, background .15s; }
        .wt-file:hover .wt-file-del, .wt-file-del:focus { opacity: 1; }
        .wt-file-del:hover { background: var(--wt-red); }
        @media (hover: none) { .wt-file-del { opacity: 1; } }
        .wt-lightbox { position: fixed; inset: 0; z-index: 2150; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; padding: 24px; background: rgba(8, 20, 45, .86); outline: 0; animation: wt-party-in .2s ease both; cursor: zoom-out; }
        .wt-lightbox img { max-width: 100%; max-height: calc(100vh - 110px); border-radius: 10px; box-shadow: 0 20px 50px rgba(0, 0, 0, .45); animation: wt-party-pop .3s ease both; }
        .wt-lightbox-bar { display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 8px 14px; color: #fff; font-size: 13px; cursor: default; }
        .wt-lightbox-bar a, .wt-lightbox-bar button { padding: 4px 12px; border: 1px solid rgba(255, 255, 255, .5); border-radius: 16px; background: rgba(255, 255, 255, .12); color: #fff; font-size: 12.5px; text-decoration: none; }
        .wt-lightbox-bar a:hover, .wt-lightbox-bar button:hover { background: rgba(255, 255, 255, .25); color: #fff; }
        .wt-range { width: 100%; accent-color: var(--wt-blue); }
        .wt-range-value { font-size: 28px; font-weight: 700; color: var(--wt-navy); }
        /* Hanya kotak pilihannya yang selebar kolom; daftar yang melayang (dropdown) tetap selebar kotaknya */
        .select2.select2-container { width: 100% !important; }
        .select2-container--open .select2-dropdown { max-width: calc(100vw - 32px); }

        /* Panduan */
        .wt-guide { font-size: 13.5px; }
        .wt-guide .accordion-button { gap: 9px; padding: 11px 14px; font-size: 14px; font-weight: 600; color: var(--wt-navy); }
        .wt-guide .accordion-button:not(.collapsed) { background: #e9f1fe; box-shadow: none; }
        .wt-guide .accordion-button i { width: 18px; text-align: center; }
        .wt-guide ol, .wt-guide ul { padding-left: 20px; }
        .wt-guide li { margin-bottom: 6px; }
        .wt-guide dt { font-weight: 700; color: var(--wt-navy); }
        .wt-guide dd { margin-bottom: 9px; }
        .wt-guide-tip { padding: 9px 12px; border-radius: 9px; background: #fff8e4; color: #6d5200; }
        .wt-guide-dot { display: inline-block; width: 12px; height: 12px; margin-right: 3px; border-radius: 50%; vertical-align: -1px; }

        /* Confetti */
        .wt-confetti { position: fixed; top: -12px; width: 9px; height: 13px; z-index: 2200; pointer-events: none; animation: wt-fall var(--dur) ease-in forwards; }
        @keyframes wt-fall { to { transform: translate(var(--drift), 105vh) rotate(760deg); opacity: .8; } }

        .wt-party { position: fixed; inset: 0; z-index: 2100; display: grid; place-items: center; padding: 16px; background: rgba(8, 20, 45, .62); backdrop-filter: blur(4px); cursor: pointer; animation: wt-party-in .25s ease both; }
        .wt-party.out { opacity: 0; transition: opacity .3s; }
        @keyframes wt-party-in { from { opacity: 0; } }
        .wt-party-card { width: min(430px, 100%); padding: 28px 26px 24px; border-radius: 22px; background: #fff; color: var(--wt-ink); text-align: center; box-shadow: 0 24px 60px rgba(0, 0, 0, .4); animation: wt-party-pop .55s cubic-bezier(.2, 1.5, .4, 1) both; }
        @keyframes wt-party-pop { from { opacity: 0; transform: scale(.55) translateY(30px); } }
        .wt-party-trophy { width: 84px; height: 84px; margin: 0 auto 12px; display: grid; place-items: center; border-radius: 50%; color: #fff; font-size: 38px; background: linear-gradient(145deg, #f7b84b, #e08a00); box-shadow: 0 0 0 9px #fff3d6; animation: wt-party-bounce 1s ease-in-out infinite alternate; }
        @keyframes wt-party-bounce { to { transform: translateY(-7px) rotate(-5deg); } }
        .wt-party-head { font-size: 25px; font-weight: 800; color: var(--wt-navy); }
        .wt-party-title { margin: 5px 0 12px; font-size: 15px; font-weight: 600; overflow-wrap: anywhere; }
        .wt-party-who { display: inline-flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; }

        @media (max-width: 991.98px) {
            .wt-lane { grid-template-columns: 1fr; gap: 6px; }
            .wt-queue-row { grid-template-columns: 1fr; gap: 6px; }
            .wt-row { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 10px 14px; }
            .wt-row-main { grid-column: 1 / -1; }
            .wt-row .wt-title { white-space: normal; }
            .wt-hero { padding: 18px; }
            .wt-ring { width: 92px; height: 92px; }
            .wt-ring-text b { font-size: 21px; }
            .wt-verdict { font-size: 14.5px; }
        }
    </style>
@endsection

@section('content')
    <div class="wt">
        <div class="wt-hero mb-3" id="wt-hero">
            <span class="wt-blob b1" aria-hidden="true"></span><span class="wt-blob b2" aria-hidden="true"></span><span class="wt-blob b3" aria-hidden="true"></span>
            <span class="wt-shine" aria-hidden="true"></span>
            <div class="wt-bubbles" id="wt-bubbles" aria-hidden="true"></div>
            <i class="fa-solid fa-laptop-code wt-float" style="--x: 46%; --y: 14%; --d: 6s; --depth: 18px;" aria-hidden="true"></i>
            <i class="fa-solid fa-list-check wt-float" style="--x: 60%; --y: 56%; --d: 8s; --delay: -2s; --s: 28px; --depth: 26px;" aria-hidden="true"></i>
            <i class="fa-solid fa-bug wt-float" style="--x: 76%; --y: 18%; --d: 7s; --delay: -4s; --s: 18px; --depth: 12px;" aria-hidden="true"></i>
            <i class="fa-solid fa-rocket wt-float" style="--x: 34%; --y: 70%; --d: 9s; --delay: -1s; --s: 20px; --depth: 20px;" aria-hidden="true"></i>
            <i class="fa-solid fa-star wt-float twinkle" style="--x: 88%; --y: 64%; --d: 5s; --delay: -3s; --s: 12px; --depth: 8px;" aria-hidden="true"></i>
            <i class="fa-solid fa-star wt-float twinkle" style="--x: 53%; --y: 30%; --d: 4s; --delay: -1.5s; --s: 9px; --depth: 6px;" aria-hidden="true"></i>
            <i class="fa-solid fa-flag-checkered wt-float" style="--x: 70%; --y: 74%; --d: 10s; --delay: -6s; --s: 16px; --depth: 16px;" aria-hidden="true"></i>
            <svg class="wt-wave" viewBox="0 0 1200 60" preserveAspectRatio="none" aria-hidden="true">
                <path class="w1" d="M0 30 Q 75 10 150 30 T 300 30 T 450 30 T 600 30 T 750 30 T 900 30 T 1050 30 T 1200 30 V60 H0 Z"/>
                <path class="w2" d="M0 38 Q 100 20 200 38 T 400 38 T 600 38 T 800 38 T 1000 38 T 1200 38 V60 H0 Z"/>
            </svg>
            <i class="fa-solid fa-person-running wt-hero-runner" aria-hidden="true"></i>

            <div class="wt-hero-inner">
                <div class="wt-hero-main">
                    <div class="wt-ring" id="wt-ring" role="img" aria-label="Progress tim">
                        <span class="wt-ring-orbit" aria-hidden="true"><i></i></span>
                        <svg viewBox="0 0 120 120" aria-hidden="true"><circle class="wt-ring-track" cx="60" cy="60" r="52"/><circle class="wt-ring-value" id="wt-ring-value" cx="60" cy="60" r="52" pathLength="100"/></svg>
                        <div class="wt-ring-text"><b id="wt-ring-pct">0%</b><span>Progress tim</span></div>
                    </div>
                    <div style="min-width:0">
                        <div class="wt-greet"><span id="wt-date"></span> <span class="wt-clock" id="wt-clock"></span></div>
                        <h1 id="wt-hero-title"><i class="fa-solid fa-person-running"></i> <span class="wt-title-text">Work Tracker</span></h1>
                        <div class="wt-verdict" id="wt-verdict" role="status"><span class="wt-msg-body"><i class="fa-solid fa-spinner fa-spin"></i> Memuat...</span><span class="wt-msg-bar"></span></div>
                    </div>
                </div>
                <div class="wt-hero-actions">
                    <a class="wt-btn-ghost" href="{{ route('work-tracker-dashboard') }}"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
                    <a class="wt-btn-ghost wt-btn-3d" href="{{ route('work-tracker-world') }}"><i class="fa-solid fa-cube"></i> Dunia 3D</a>
                    <button type="button" class="wt-btn-ghost" id="wt-btn-guide" data-bs-toggle="modal" data-bs-target="#wt-guide-modal"><i class="fa-solid fa-circle-question"></i> Panduan</button>
                    <button type="button" class="wt-btn-ghost" id="wt-btn-programmer"><i class="fa-solid fa-users"></i> Anggota</button>
                    <button type="button" class="wt-btn-light" id="wt-btn-add"><i class="fa-solid fa-plus"></i> Tambah Kerjaan</button>
                </div>
            </div>
        </div>

        <div class="wt-alert d-none" id="wt-urgent" role="alert"></div>

        <div class="wt-demo mb-3 d-none" id="wt-demo-banner">
            <i class="fa-solid fa-eye"></i> <b>Mode contoh:</b> data ini hanya untuk melihat tampilan dan tidak tersimpan.
            <a href="#" id="wt-demo-exit" class="fw-bold ms-1">Kembali ke data asli</a>
        </div>

        <div class="wt-tiles" id="wt-tiles"></div>

        <div class="wt-card wt-race" id="wt-race">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                <div class="d-flex align-items-center gap-2">
                    <h2 class="wt-section-title"><i class="fa-solid fa-flag-checkered"></i> Arena Tim<span class="wt-scene" id="wt-scene"></span></h2>
                    <button type="button" class="wt-fold" id="wt-arena-fold" aria-expanded="true" aria-controls="wt-arena-body"><i class="fa-solid fa-chevron-down"></i> <span>Lipat</span></button>
                </div>
                <div class="wt-legend">
                    <span><i class="fa-solid fa-person-running text-success"></i> Ngebut: aman</span>
                    <span><i class="fa-solid fa-person-running text-warning"></i> Jogging: mepet</span>
                    <span><i class="fa-solid fa-person-walking text-danger"></i> Jalan: tertinggal</span>
                    <span><i class="fa-solid fa-flag"></i> Posisi seharusnya hari ini</span>
                    <span><i class="fa-solid fa-circle-check text-success"></i> Pos = kerjaan</span>
                </div>
            </div>
            <div id="wt-arena-body">
                <div id="wt-lanes"></div>
            </div>
        </div>

        <div class="wt-card wt-panel">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="wt-tabs" id="wt-tabs">
                    <button class="active" data-view="list"><i class="fa-solid fa-list"></i> Daftar</button>
                    <button data-view="kanban"><i class="fa-solid fa-table-columns"></i> Kanban</button>
                    <button data-view="timeline"><i class="fa-solid fa-chart-gantt"></i> Timeline</button>
                    <button data-view="queue"><i class="fa-solid fa-inbox"></i> Antrean <span class="wt-tab-count" id="wt-queue-count">0</span></button>
                    <button data-view="report"><i class="fa-solid fa-file-excel"></i> Laporan</button>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="wt-archive">
                    <label class="form-check-label small" for="wt-archive">Tampilkan arsip (&gt; {{ \App\Http\Controllers\General\WorkTrackerController::ARCHIVE_AFTER_DAYS }} hari)</label>
                </div>
            </div>

            <div class="wt-filterbar">
                <div class="wt-search"><i class="fa-solid fa-magnifying-glass"></i><input type="search" class="form-control" id="wt-f-search" placeholder="Cari judul, modul, peminta..." aria-label="Cari kerjaan"></div>
                <div class="wt-report-only wt-fb-date d-none"><div class="input-group"><span class="input-group-text">Dari</span><input type="date" class="form-control" id="wt-f-from" aria-label="Dari tanggal"></div></div>
                <div class="wt-report-only wt-fb-date d-none"><div class="input-group"><span class="input-group-text">Sampai</span><input type="date" class="form-control" id="wt-f-to" aria-label="Sampai tanggal"></div></div>
                <div id="wt-f-programmer-box" class="wt-fb-programmer"><select class="wt-select" id="wt-f-programmer" aria-label="Anggota"></select></div>
                <button type="button" class="wt-filter-btn" id="wt-filter-toggle" aria-expanded="false" aria-controls="wt-filter-more"><i class="fa-solid fa-sliders"></i> Filter <span class="wt-filter-badge d-none" id="wt-filter-count"></span><i class="fa-solid fa-chevron-down"></i></button>
                <button type="button" class="wt-filter-reset d-none" id="wt-filter-reset">Reset filter</button>
            </div>
            <div class="wt-division-chips" id="wt-division-chips" aria-label="Filter divisi"></div>

            <div class="wt-filter-more d-none" id="wt-filter-more">
                <div id="wt-f-division-box"><label>Divisi</label><select class="wt-select" id="wt-f-division" multiple></select></div>
                <div><label>Jenis</label>
                    <select class="wt-select" id="wt-f-type">
                        <option value="">Semua jenis</option><option value="plan">Plan</option><option value="bug">Masalah</option><option value="request">Request</option>
                    </select>
                </div>
                <div id="wt-f-status-box"><label>Status</label>
                    <select class="wt-select" id="wt-f-status">
                        <option value="open">Belum selesai</option><option value="">Semua status</option><option value="baru">Baru</option><option value="dikerjakan">Dikerjakan</option><option value="selesai">Selesai</option><option value="ditolak">Ditolak</option>
                    </select>
                </div>
                <div><label>Level</label>
                    <select class="wt-select" id="wt-f-level">
                        <option value="">Semua level</option><option value="major">Major</option><option value="minor">Minor</option><option value="tidak">Tidak dikerjakan</option><option value="none">Belum ada level</option>
                    </select>
                </div>
                <div id="wt-f-health-box"><label>Kondisi</label>
                    <select class="wt-select" id="wt-f-health">
                        <option value="">Semua kondisi</option><option value="telat">Telat</option><option value="mepet">Mepet</option><option value="aman">Aman</option><option value="belum_dinilai">Belum dinilai</option>
                    </select>
                </div>
            </div>

            <div id="wt-view"></div>
        </div>
    </div>

    {{-- Form kerjaan --}}
    <div class="modal fade" id="wt-item-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable wt-modal-md">
            <form class="modal-content wt-form" id="wt-item-form">
                <div class="modal-header">
                    <h5 class="modal-title" id="wt-item-title">Tambah Kerjaan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id">
                    <input class="form-control wt-title-input" name="title" maxlength="200" required placeholder="Judul kerjaan, contoh: Perbaiki rekap packing" aria-label="Judul kerjaan">

                    {{-- Pilihan jenis tampil sebagai tombol, nilainya tetap disimpan di select tersembunyi --}}
                    <div class="wt-type-pills" id="wt-i-type-pills" role="radiogroup" aria-label="Jenis kerjaan">
                        <button type="button" role="radio" data-type="plan"><i class="fa-solid fa-compass-drafting"></i> Plan</button>
                        <button type="button" role="radio" data-type="bug"><i class="fa-solid fa-triangle-exclamation"></i> Masalah</button>
                        <button type="button" role="radio" data-type="request"><i class="fa-solid fa-comment-dots"></i> Request</button>
                    </div>
                    <div class="d-none"><select class="wt-select-modal" name="type" id="wt-i-type" aria-hidden="true" tabindex="-1"><option value="plan">Plan</option><option value="bug">Masalah</option><option value="request">Request</option></select></div>

                    <div class="row g-3 mt-1 wt-form-grid">
                        <div class="col-sm-6"><label>Anggota</label><select class="wt-select-modal" name="programmer_id" id="wt-i-programmer"></select></div>
                        <div class="col-sm-6"><label>Peminta / pelapor</label><input class="form-control" name="requester" maxlength="100" placeholder="Nama peminta"></div>
                        <div class="col-sm-6"><label>Modul</label><input class="form-control" name="module" maxlength="100" placeholder="Contoh: Cutting"></div>
                        <div class="col-sm-6"><label>Departemen</label><input class="form-control" name="requester_dept" maxlength="100" placeholder="Contoh: Marketing"></div>
                        <div class="col-sm-6 wt-not-plan"><label>Level</label>
                            <select class="wt-select-modal" name="level" id="wt-i-level"><option value="">Belum ditentukan</option><option value="major">Major</option><option value="minor">Minor</option><option value="tidak">Tidak dikerjakan</option></select>
                        </div>
                        <div class="col-sm-6 d-none" id="wt-i-reason-box"><label>Alasan tidak dikerjakan</label><input class="form-control" name="reject_reason" maxlength="500"></div>
                        <div class="col-sm-5 col-6"><label>Tanggal mulai</label><input type="date" class="form-control" name="start_date"></div>
                        <div class="col-sm-5 col-6"><label>Target selesai</label><input type="date" class="form-control" name="target_date"></div>
                        <div class="col-sm-2 col-6"><label>Durasi</label>
                            <div class="wt-suffix"><input type="number" class="form-control" name="estimate_days" min="0.5" max="999" step="0.5" placeholder="Otomatis" title="Otomatis dari tanggal, boleh diubah"><span>hari</span></div>
                        </div>
                    </div>

                    <div class="wt-mini-head"><i class="fa-solid fa-list-check"></i> Poin-poin <span class="wt-todo-count ms-auto" id="wt-i-points-count"></span></div>
                    <div class="wt-todo-bar d-none" id="wt-i-points-bar"><span></span></div>
                    <div id="wt-i-points"></div>
                    <div class="wt-todo-add">
                        <i class="fa-solid fa-plus"></i>
                        <input id="wt-i-point-input" maxlength="500" placeholder="Tambah poin, lalu Enter" aria-label="Tambah poin">
                        <button class="btn btn-outline-primary btn-sm" type="button" id="wt-i-point-add">Tambah</button>
                    </div>

                    <div class="mt-3"><label>Catatan</label><textarea class="form-control" name="description" rows="2" maxlength="5000"></textarea></div>

                    <div class="wt-mini-head"><i class="fa-solid fa-paperclip"></i> <span id="wt-i-files-label">Lampiran</span></div>
                    <label class="wt-drop compact" id="wt-i-drop">
                        <input type="file" multiple accept="image/*,.pdf,.xls,.xlsx,.doc,.docx,.txt,.csv,.zip,.html,.htm">
                        <span class="wt-drop-icon"><i class="fa-solid fa-cloud-arrow-up"></i></span>
                        <span><b>Tarik foto/file ke sini</b> atau klik untuk pilih<br><span class="wt-sub">Maks 10 file, 10 MB per file</span></span>
                    </label>
                    <div id="wt-i-files"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger btn-sm me-auto d-none" id="wt-item-delete"><i class="fa-solid fa-trash"></i> Hapus</button>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sb btn-sm"><i class="fa-solid fa-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Detail + update progress --}}
    <div class="modal fade" id="wt-detail-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <div class="wt-sub" id="wt-d-code"></div>
                        <h5 class="modal-title" id="wt-d-title"></h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="wt-sec"><div id="wt-d-info"></div></div>

                    <div class="wt-sec">
                        <div class="wt-sec-title"><i class="fa-solid fa-list-check"></i> Poin-poin <span class="wt-todo-count ms-auto" id="wt-points-count"></span></div>
                        <div class="wt-todo-bar d-none" id="wt-points-bar"><span></span></div>
                        <div id="wt-points"></div>
                        <form id="wt-point-form" class="wt-todo-add">
                            <i class="fa-solid fa-plus"></i>
                            <input id="wt-point-input" maxlength="500" placeholder="Tambah poin, lalu Enter" aria-label="Tambah poin">
                            <button class="btn btn-outline-primary btn-sm" type="submit">Tambah</button>
                        </form>
                    </div>

                    <div class="wt-sec">
                        <div class="wt-sec-title"><i class="fa-solid fa-paperclip"></i> Lampiran</div>
                        <label class="wt-drop compact" id="wt-d-drop">
                            <input type="file" multiple accept="image/*,.pdf,.xls,.xlsx,.doc,.docx,.txt,.csv,.zip,.html,.htm">
                            <span class="wt-drop-icon"><i class="fa-solid fa-cloud-arrow-up"></i></span>
                            <span><b>Tarik foto/file ke sini</b> atau klik untuk pilih. Langsung tersimpan.</span>
                        </label>
                        <div id="wt-item-files"></div>
                    </div>
                    <input type="file" id="wt-attach-input" class="d-none" multiple accept="image/*,.pdf,.xls,.xlsx,.doc,.docx,.txt,.csv,.zip,.html,.htm">

                    <div class="wt-sec">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <form id="wt-progress-form" class="wt-form">
                                    <div class="wt-sec-title"><i class="fa-solid fa-gauge-high"></i> Update progress <span class="wt-range-value ms-auto"><span id="wt-range-value">0</span>%</span></div>
                                    <input type="range" class="wt-range" id="wt-range" min="0" max="100" step="5" aria-label="Progress">
                                    <label class="mt-2">Catatan hari ini</label>
                                    <textarea class="form-control mb-2" id="wt-note" rows="3" maxlength="2000" placeholder="Hari ini mengerjakan apa?"></textarea>
                                    <label class="wt-drop compact mb-1" id="wt-progress-drop">
                                        <input type="file" multiple accept="image/*,.pdf,.xls,.xlsx,.doc,.docx,.txt,.csv,.zip,.html,.htm">
                                        <span class="wt-drop-icon"><i class="fa-solid fa-camera"></i></span>
                                        <span><b>Lampirkan bukti</b> (opsional)</span>
                                    </label>
                                    <div id="wt-progress-files" class="mb-2"></div>
                                    <button class="btn btn-sb btn-sm w-100" type="submit"><i class="fa-solid fa-save"></i> Simpan Progress</button>
                                </form>
                            </div>
                            <div class="col-md-6">
                                <div class="wt-sec-title"><i class="fa-solid fa-clock-rotate-left"></i> Riwayat</div>
                                <div id="wt-logs" style="max-height: 320px; overflow-y: auto; padding: 4px 4px 0 6px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-secondary btn-sm" id="wt-d-edit"><i class="fa-solid fa-pen"></i> Edit Kerjaan</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Panduan --}}
    <div class="modal fade" id="wt-guide-modal" tabindex="-1" aria-labelledby="wt-guide-title" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="wt-guide-title"><i class="fa-solid fa-circle-question"></i> Panduan Work Tracker</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body wt-guide">
                    <p>Work Tracker adalah papan untuk mencatat semua kerjaan tim, seperti Programmer, IE, dan SA, dan melihat apakah kerjaan itu akan selesai tepat waktu. Bayangkan tiap anggota sedang lomba lari: makin banyak kerjaan yang beres, makin dekat ke garis finish.</p>

                    <div class="accordion" id="wt-guide-list">
                        <div class="accordion-item">
                            <h2 class="accordion-header"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#wt-g-start"><i class="fa-solid fa-rocket"></i> Mulai dalam 4 langkah</button></h2>
                            <div id="wt-g-start" class="accordion-collapse collapse show" data-bs-parent="#wt-guide-list">
                                <div class="accordion-body">
                                    <ol>
                                        <li><b>Daftarkan anggota.</b> Klik tombol <b>Anggota</b> di atas, pilih orangnya, pilih divisinya, pilih warna, lalu klik <b>Tambahkan</b>. Kalau divisinya belum ada, ketik saja nama divisi baru.</li>
                                        <li><b>Catat kerjaan.</b> Klik <b>Tambah Kerjaan</b>, isi judul, tanggal mulai, dan target selesai. Estimasi hari terisi sendiri.</li>
                                        <li><b>Tugaskan.</b> Pilih anggota di form. Kalau belum tahu siapa yang mengerjakan, kosongkan saja. Kerjaan akan menunggu di tab <b>Antrean</b>.</li>
                                        <li><b>Update tiap hari.</b> Klik kerjaannya, geser persen progress, tulis catatan singkat, lalu klik <b>Simpan Progress</b>. Saat mencapai 100%, kerjaan otomatis selesai.</li>
                                    </ol>
                                    <div class="wt-guide-tip"><i class="fa-solid fa-lightbulb"></i> Yang paling penting adalah rutin mengisi progress. Prediksi hanya akurat kalau progress-nya terbaru.</div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#wt-g-arena"><i class="fa-solid fa-person-running"></i> Cara membaca Arena</button></h2>
                            <div id="wt-g-arena" class="accordion-collapse collapse" data-bs-parent="#wt-guide-list">
                                <div class="accordion-body">
                                    <p>Tiap baris adalah satu anggota. Posisi pelari menunjukkan progress gabungan semua kerjaannya.</p>
                                    <table class="table table-sm align-middle">
                                        <thead><tr><th>Yang terlihat</th><th>Artinya</th></tr></thead>
                                        <tbody>
                                            <tr><td class="text-nowrap"><i class="fa-solid fa-person-running text-success"></i> <b>Ngebut</b></td><td>Waktunya masih longgar. Aman.</td></tr>
                                            <tr><td class="text-nowrap"><i class="fa-solid fa-person-running text-warning"></i> <b>Jaga pace</b></td><td>Masih bisa keburu, tetapi mepet atau mulai tertinggal.</td></tr>
                                            <tr><td class="text-nowrap"><i class="fa-solid fa-person-walking text-danger"></i> <b>Ayo kejar</b></td><td>Waktunya kurang. Perlu dikejar, dibantu, atau targetnya dibicarakan ulang.</td></tr>
                                            <tr><td class="text-nowrap"><i class="fa-solid fa-mug-hot text-secondary"></i> <b>Santai</b></td><td>Belum ada kerjaan. Bisa diberi tugas baru.</td></tr>
                                            <tr><td class="text-nowrap"><i class="fa-solid fa-trophy text-warning"></i> <b>Semua beres</b></td><td>Semua kerjaan aktifnya sudah selesai.</td></tr>
                                            <tr><td class="text-nowrap"><i class="fa-solid fa-flag"></i> <b>Bendera</b></td><td>Posisi yang seharusnya sudah dicapai hari ini. Pelari di belakang bendera berarti tertinggal.</td></tr>
                                        </tbody>
                                    </table>
                                    <p class="mb-1"><b>Lingkaran di jalan adalah kerjaan.</b> Arahkan kursor untuk melihat detailnya, atau klik untuk update progress.</p>
                                    <div class="d-flex flex-wrap gap-3 small">
                                        <span><span class="wt-guide-dot" style="background: var(--wt-green);"></span> Selesai</span>
                                        <span><span class="wt-guide-dot" style="background: var(--wt-blue);"></span> Sedang dikerjakan</span>
                                        <span><span class="wt-guide-dot" style="background: var(--wt-red);"></span> Diprediksi telat</span>
                                        <span><span class="wt-guide-dot" style="background: #9aa6b8;"></span> Belum dimulai</span>
                                    </div>
                                    <p class="mt-2 mb-0 small text-muted">Pelari bisa berada di depan lingkaran yang belum selesai kalau anggota mengerjakan beberapa hal sekaligus. Warna lingkaran tetap menunjukkan status sebenarnya.</p>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#wt-g-pace"><i class="fa-solid fa-stopwatch"></i> Bagaimana sistem tahu keburu atau tidak?</button></h2>
                            <div id="wt-g-pace" class="accordion-collapse collapse" data-bs-parent="#wt-guide-list">
                                <div class="accordion-body">
                                    <p>Sistem melihat kecepatan kerja sejauh ini, lalu menghitung kapan kerjaan akan selesai kalau kecepatannya tetap sama. <b>Semua hari ikut dihitung</b>, termasuk Sabtu, Minggu, dan hari libur.</p>
                                    <div class="wt-guide-tip mb-3">
                                        <b>Contoh:</b> mulai 3 Nov, hari ini 5 Nov (hari ke-3), progress 30%.<br>
                                        Kecepatannya 30% ÷ 3 hari = <b>10% per hari</b>.<br>
                                        Sisa 70% butuh 7 hari lagi, jadi <b>prediksi selesai 12 Nov</b>.
                                    </div>
                                    <ul class="mb-0">
                                        <li><span class="wt-chip h-aman">Aman</span> Prediksi selesai minimal 2 hari sebelum target.</li>
                                        <li><span class="wt-chip h-mepet">Mepet</span> Prediksi selesai tepat di target atau 1 hari sebelumnya.</li>
                                        <li><span class="wt-chip h-telat">Telat</span> Prediksi selesai setelah target.</li>
                                        <li><span class="wt-chip h-belum_dinilai">Belum dinilai</span> Data belum cukup, biasanya target atau estimasinya belum diisi.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#wt-g-urgent"><i class="fa-solid fa-road-barrier"></i> Ada masalah mendesak</button></h2>
                            <div id="wt-g-urgent" class="accordion-collapse collapse" data-bs-parent="#wt-guide-list">
                                <div class="accordion-body">
                                    <p>Kalau ada <b>Masalah</b> berlevel <b>Major</b> yang belum selesai dan targetnya hari ini atau sudah lewat, peringatan merah muncul di atas halaman dan rintangan muncul di lintasan programmernya.</p>
                                    <ul class="mb-0">
                                        <li><b>Pelari panik:</b> ada masalah mendesak yang belum mulai dikerjakan.</li>
                                        <li><b>Pelari membetulkan rintangan:</b> masalah sedang dikerjakan.</li>
                                        <li><b>Pelari melompat:</b> masalah sudah selesai, dan rintangannya hilang.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#wt-g-tabs"><i class="fa-solid fa-table-columns"></i> Kegunaan tiap tab</button></h2>
                            <div id="wt-g-tabs" class="accordion-collapse collapse" data-bs-parent="#wt-guide-list">
                                <div class="accordion-body">
                                    <ul class="mb-0">
                                        <li><b>Daftar:</b> semua kerjaan, yang paling bermasalah di atas. Anggota bisa langsung diganti dari sini.</li>
                                        <li><b>Kanban:</b> kartu kerjaan per status. Tarik kartu ke kolom lain untuk mengubah statusnya.</li>
                                        <li><b>Timeline:</b> jadwal kerjaan per anggota. Klik batang untuk membuka detail, geser untuk memindah jadwal atau anggota, dan tarik garis di ujung kanan untuk mengubah target. Arsiran merah menunjukkan perkiraan keterlambatan.</li>
                                        <li><b>Antrean:</b> kerjaan yang belum punya anggota. Pilih orangnya di dropdown, dan kerjaan langsung tersimpan.</li>
                                        <li><b>Laporan:</b> daftar kerjaan per periode. Klik <b>Export Excel</b> untuk mengunduhnya.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#wt-g-terms"><i class="fa-solid fa-book"></i> Arti istilah</button></h2>
                            <div id="wt-g-terms" class="accordion-collapse collapse" data-bs-parent="#wt-guide-list">
                                <div class="accordion-body">
                                    <dl class="mb-0">
                                        <dt>Plan</dt><dd>Kerjaan yang sudah direncanakan.</dd>
                                        <dt>Masalah</dt><dd>Kendala atau error yang perlu dibereskan.</dd>
                                        <dt>Request</dt><dd>Permintaan dari user, misalnya tambah kolom di laporan.</dd>
                                        <dt>Major / Minor</dt><dd>Besar atau kecilnya dampak masalah atau request.</dd>
                                        <dt>Tidak dikerjakan</dt><dd>Masalah atau request yang diputuskan tidak dikerjakan. Alasannya wajib diisi.</dd>
                                        <dt>Estimasi</dt><dd>Perkiraan lama pengerjaan dalam hari. Terisi otomatis dari tanggal, dan sebaiknya diubah kalau pengerjaan sebenarnya lebih singkat.</dd>
                                        <dt>Pace</dt><dd>Kecepatan kerja, yaitu rata-rata persen progress per hari.</dd>
                                        <dt>Sisa beban</dt><dd>Perkiraan jumlah hari kerja yang masih dibutuhkan.</dd>
                                        <dt>Tersedia</dt><dd>Jumlah hari dari hari ini sampai target terjauh anggota itu.</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#wt-g-faq"><i class="fa-solid fa-circle-question"></i> Pertanyaan yang sering muncul</button></h2>
                            <div id="wt-g-faq" class="accordion-collapse collapse" data-bs-parent="#wt-guide-list">
                                <div class="accordion-body">
                                    <dl class="mb-0">
                                        <dt>Bagaimana cara menghapus kerjaan?</dt><dd>Klik kerjaannya, klik <b>Edit Kerjaan</b>, lalu klik <b>Hapus</b> di kiri bawah.</dd>
                                        <dt>Bagaimana cara memindahkan kerjaan ke anggota lain?</dt><dd>Di tab <b>Daftar</b>, ganti nama pada kolom Anggota.</dd>
                                        <dt>Bagaimana cara menandai kerjaan selesai?</dt><dd>Geser progress ke 100%, atau tarik kartunya ke kolom <b>Selesai</b> di tab Kanban.</dd>
                                        <dt>Bagaimana cara menolak request?</dt><dd>Di form Edit, pilih level <b>Tidak dikerjakan</b> dan isi alasannya. Bisa juga dengan menarik kartunya ke kolom <b>Ditolak</b> di tab Kanban.</dd>
                                        <dt>Kerjaan lama tidak muncul, ke mana?</dt><dd>Kerjaan yang selesai atau ditolak lebih dari 60 hari lalu masuk arsip. Nyalakan <b>Tampilkan arsip</b>, atau cari lewat tab <b>Laporan</b>.</dd>
                                        <dt>Kenapa pelari saya berjalan pelan padahal kerjaan terkendali?</dt><dd>Periksa progress dan estimasinya. Kalau beberapa kerjaan dikerjakan bersamaan, estimasi otomatis bisa terlalu besar. Ubah angkanya sesuai lama pengerjaan sebenarnya.</dd>
                                        <dt>Bagaimana melihat kerjaan satu divisi saja?</dt><dd>Klik nama divisinya, misalnya <b>IE</b>, di atas Arena. Filter ini juga berlaku di tab bawah.</dd>
                                        <dt>Kenapa pelarinya tidak bergerak?</dt><dd>Pelari memang diam kalau anggota belum punya kerjaan atau semua kerjaannya sudah selesai. Kalau ada kerjaan tetapi tetap diam, muat ulang halaman dengan <b>Ctrl+F5</b>.</dd>
                                        <dt>Bagaimana melihat kerjaan satu anggota saja?</dt><dd>Klik barisnya di Arena. Klik sekali lagi untuk kembali melihat semua.</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sb btn-sm" data-bs-dismiss="modal">Mengerti</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Programmer --}}
    <div class="modal fade" id="wt-programmer-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content wt-form" id="wt-programmer-form">
                <div class="modal-header">
                    <h5 class="modal-title">Anggota Tim</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div id="wt-programmer-list" class="mb-3"></div>
                    <label>Tambah dari user</label>
                    <select id="wt-p-user" class="mb-2"></select>
                    <label class="mt-2">Divisi</label>
                    <select id="wt-p-division"></select>
                    <div class="wt-sub mt-1">Pilih divisi, atau ketik nama divisi baru.</div>
                    <label class="mt-2">Warna</label>
                    <div class="wt-color" id="wt-p-colors"></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sb btn-sm"><i class="fa-solid fa-plus"></i> Tambahkan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('custom-script')
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        (function () {
            const ROUTES = {
                data: @json(route('data-work-tracker')),
                users: @json(route('users-work-tracker')),
                logs: @json(url('work-tracker/logs')),
                detail: @json(url('work-tracker/detail')),
                pointStore: @json(route('store-point-work-tracker')),
                pointToggle: @json(route('toggle-point-work-tracker')),
                pointDelete: @json(route('delete-point-work-tracker')),
                attachmentStore: @json(route('store-attachment-work-tracker')),
                attachmentDelete: @json(route('delete-attachment-work-tracker')),
                store: @json(route('store-work-tracker')),
                progress: @json(route('progress-work-tracker')),
                status: @json(route('status-work-tracker')),
                report: @json(route('report-work-tracker')),
                exportExcel: @json(route('export-work-tracker')),
                assign: @json(route('assign-work-tracker')),
                reschedule: @json(route('reschedule-work-tracker')),
                remove: @json(route('delete-work-tracker')),
                programmerStore: @json(route('store-programmer-work-tracker')),
                programmerDelete: @json(route('delete-programmer-work-tracker')),
            };
            const COLORS = [
                '#2f80ed', '#9b51e0', '#f2994a', '#27ae60', '#eb5757', '#00a3a3', '#d6336c', '#5c6ac4',
                '#0ea5e9', '#c026d3', '#d97706', '#65a30d', '#be123c', '#0891b2', '#3b6e9e', '#b45309',
                '#1e3a8a', '#6d28d9', '#8b5e3c', '#166534', '#7f1d1d', '#0f766e', '#475569', '#6b7a1f'
            ];
            const TYPE_LABEL = { plan: 'Plan', bug: 'Masalah', request: 'Request' };
            const TYPE_ICON = { plan: 'fa-compass-drafting', bug: 'fa-triangle-exclamation', request: 'fa-comment-dots' };
            const LEVEL_LABEL = { major: 'Major', minor: 'Minor', tidak: 'Tidak' };
            const STATUS_LABEL = { baru: 'Baru', dikerjakan: 'Dikerjakan', selesai: 'Selesai', ditolak: 'Ditolak' };
            const HEALTH_LABEL = { aman: 'Aman', mepet: 'Mepet', telat: 'Telat', selesai: 'Selesai', selesai_telat: 'Selesai telat', ditolak: 'Ditolak', belum_dinilai: 'Belum dinilai' };
            const SAY = {
                sprint: ['Gas terus!', 'Ngebut nih', 'Aman, lanjut!', 'Target? Lewat!'],
                jog: ['Jaga ritme', 'Dikit lagi', 'Fokus, fokus', 'Pelan tapi pasti'],
                walk: ['Ayo kejar!', 'Semangat!', 'Kopi dulu dong', 'Belum nyerah!'],
                rest: ['Kerjaan mana?', 'Ngopi dulu', 'Siap ditugaskan'],
                finish: ['Beres semua!', 'Hore!', 'Mantap!'],
                panic: ['Aduh, ada masalah!', 'Tolong dicek!', 'Darurat!'],
                fixing: ['Lagi dibetulin', 'Sebentar lagi beres', 'Pegang kunci pas'],
            };
            const TIER_TITLE = { sprint: 'Ngebut!', jog: 'Jaga pace', walk: 'Ayo kejar!', rest: 'Belum ada kerjaan', finish: 'Semua beres!' };

            const state = { timelineOpen: {}, tlMode: 'auto', tlFrom: null, tlTo: null, tlRange: null, tlZoom: null, tlScroll: null, tlExtending: false, tlPan: null, drag: null, justDragged: false, divisions: [], board: null, demo: false, view: 'list', selected: null, color: COLORS[0], current: null, pos: {}, jumpers: [], urgentIds: [], reportItems: [], reportTimer: null, reportSeq: 0, statusBeforeReport: null, estimateManual: false, tileValues: {}, ringPct: 0, formFiles: [], progressFiles: [], formPoints: [], formPointsReady: true, detail: null, attachPoint: null };
            const USER_NAME = @json(auth()->user()->name ?? '');
            // Samudra, Senja, Hutan, Anggur, Nirwana, Emas (sama dengan palet Action Points)
            const HERO_PALETTES = [['#2563eb', '#06b6d4'], ['#ea580c', '#e11d48'], ['#059669', '#65a30d'], ['#7c3aed', '#db2777'], ['#082149', '#1c5bb8'], ['#b45309', '#eab308']];
            const FILE_LIMIT = 10;
            const FILE_MAX_BYTES = 10 * 1024 * 1024;
            const $view = $('#wt-view');

            function esc(value) {
                return $('<div>').text(value === null || value === undefined ? '' : value).html().replace(/"/g, '&quot;').replace(/'/g, '&#39;');
            }

            function parseDate(iso) {
                const p = iso.split('-').map(Number);
                return new Date(p[0], p[1] - 1, p[2]);
            }

            function fmt(iso, withYear) {
                if (!iso) return '-';
                const options = { day: 'numeric', month: 'short' };
                if (withYear) options.year = 'numeric';
                return parseDate(iso).toLocaleDateString('id-ID', options);
            }

            function days(a, b) {
                return Math.round((parseDate(b) - parseDate(a)) / 86400000);
            }

            function isoOf(date) {
                return date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0');
            }

            function addDays(iso, n) {
                const date = parseDate(iso);
                date.setDate(date.getDate() + n);
                return isoOf(date);
            }

            // Tanggal 1 dan tanggal terakhir bulan, digeser sejumlah bulan dari bulan tanggal iso
            function monthStart(iso, add) {
                const date = parseDate(iso);
                return isoOf(new Date(date.getFullYear(), date.getMonth() + add, 1));
            }

            function monthEnd(iso, add) {
                const date = parseDate(iso);
                return isoOf(new Date(date.getFullYear(), date.getMonth() + add + 1, 0));
            }

            // Rentang Timeline bawaan per pilihan: 2 minggu dari kemarin, bulan berjalan, atau bulan lalu sampai bulan depan
            function timelinePreset(mode, anchor) {
                if (mode === '1m') return { from: monthStart(anchor, 0), to: monthEnd(anchor, 0) };
                if (mode === '3m') return { from: monthStart(anchor, -1), to: monthEnd(anchor, 1) };
                return { from: addDays(anchor, -1), to: addDays(anchor, 12) };
            }

            function initials(name) {
                return (name || '?').trim().split(/\s+/).slice(0, 2).map(function (w) { return w[0]; }).join('').toUpperCase();
            }

            function num(value) {
                return Number(value || 0).toLocaleString('id-ID', { maximumFractionDigits: 1 });
            }

            function programmerOf(item) {
                return state.board.programmers.find(function (p) { return p.id === Number(item.programmer_id); });
            }

            function toast(message, type) {
                iziToast[type || 'success']({ message: message, position: 'topCenter', timeout: 2500 });
            }

            function failed(xhr) {
                if (xhr.status === 413) {
                    toast('File terlalu besar untuk diunggah', 'error');
                    return;
                }
                const json = xhr.responseJSON || {};
                const first = json.errors ? Object.values(json.errors)[0][0] : (json.message || 'Terjadi kesalahan');
                toast(first, 'error');
            }

            function guardDemo() {
                if (state.demo) {
                    toast('Mode contoh tidak menyimpan data', 'warning');
                    return true;
                }
                return false;
            }

            function post(url, data, done) {
                if (guardDemo()) return;
                return $.post(url, data).done(function (res) {
                    if (res.status && Number(res.status) !== 200) {
                        toast(res.message || 'Terjadi kesalahan', 'error');
                        return;
                    }
                    done(res);
                }).fail(failed);
            }

            // Sama seperti post, tetapi untuk FormData yang membawa file
            function postForm(url, formData, done) {
                if (guardDemo()) return;
                return $.ajax({ url: url, type: 'POST', data: formData, processData: false, contentType: false }).done(function (res) {
                    if (res.status && Number(res.status) !== 200) {
                        toast(res.message || 'Terjadi kesalahan', 'error');
                        return;
                    }
                    done(res);
                }).fail(failed);
            }

            function load() {
                const params = { archive: $('#wt-archive').is(':checked') ? 1 : 0 };
                if (state.demo) params.demo = 1;
                return $.get(ROUTES.data, params).done(function (board) {
                    // Masalah yang baru beres membuat pelarinya melompati rintangan
                    state.jumpers = state.urgentIds.map(function (id) {
                        const item = board.items.find(function (i) { return i.id === id; });
                        return item && item.status === 'selesai' && item.programmer_id ? Number(item.programmer_id) : null;
                    }).filter(Boolean);
                    state.urgentIds = board.summary.urgent.map(function (u) { return u.id; });
                    state.board = board;
                    fillProgrammerSelects();
                    render();
                }).fail(failed);
            }

            function render() {
                renderHero();
                renderTiles();
                renderLanes();
                renderView();
            }

            function renderHero() {
                const b = state.board;
                const s = b.summary;
                const hour = new Date().getHours();
                const greet = hour < 11 ? 'Selamat pagi' : hour < 15 ? 'Selamat siang' : hour < 18 ? 'Selamat sore' : 'Selamat malam';
                $('#wt-date').text(greet + (USER_NAME ? ', ' + USER_NAME : '') + ' · ' + parseDate(b.today).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }));
                $('#wt-demo-banner').toggleClass('d-none', !state.demo);
                $('#wt-queue-count').text(s.unassigned).toggleClass('empty', !s.unassigned);
                const urgent = s.urgent || [];
                $('#wt-urgent').toggleClass('d-none', !urgent.length).html(urgent.length ? '<span><i class="fa-solid fa-triangle-exclamation"></i> ' + urgent.length + ' masalah mendesak:</span>' + urgent.map(function (u) {
                    const owner = b.programmers.find(function (p) { return p.id === Number(u.programmer_id); });
                    return '<button type="button" data-urgent="' + u.id + '" data-assigned="' + (owner ? 1 : 0) + '">' + esc(u.title) + ' (' + esc(owner ? owner.name : 'belum ditugaskan') + ')</button>';
                }).join('') : '');

                let kind = 'good', icon = 'fa-circle-check', text = 'Semua kerjaan masih keburu dengan pace sekarang';
                if (!s.active) {
                    kind = 'good'; icon = 'fa-mug-hot'; text = 'Belum ada kerjaan aktif';
                } else if (s.shortfall_days > 0) {
                    kind = 'bad'; icon = 'fa-triangle-exclamation'; text = 'Tim kekurangan ' + num(s.shortfall_days) + ' hari kerja untuk mengejar semua target';
                } else if (s.late > 0) {
                    kind = 'bad'; icon = 'fa-triangle-exclamation'; text = s.late + ' kerjaan diprediksi telat dengan pace sekarang';
                } else if (s.tight > 0) {
                    kind = 'warn'; icon = 'fa-hourglass-half'; text = s.tight + ' kerjaan mepet target, jaga pace';
                }
                // Kondisi utama tampil pertama, lalu ringkasan lain bergantian
                const msgs = [{ cls: kind, icon: icon, text: text }];
                if (s.active && s.latest_predicted) msgs.push({ cls: 'good', icon: 'fa-flag-checkered', text: 'Perkiraan semua kerjaan aktif selesai ' + fmt(s.latest_predicted, true) });
                if (s.active) msgs.push({ cls: 'good', icon: 'fa-person-running', text: s.active + ' kerjaan aktif, ' + s.in_progress + ' sedang dikerjakan' });
                if (s.bugs_open) msgs.push({ cls: (s.urgent || []).length ? 'bad' : 'warn', icon: 'fa-bug', text: s.bugs_open + ' masalah masih terbuka' });
                if (s.requests_new) msgs.push({ cls: 'good', icon: 'fa-comment-dots', text: s.requests_new + ' request baru menunggu ditinjau' });
                if (s.unassigned) msgs.push({ cls: 'warn', icon: 'fa-inbox', text: s.unassigned + ' kerjaan di Antrean belum punya anggota' });
                msgs.push({ cls: 'good', icon: 'fa-list-check', text: 'Tips: pecah kerjaan jadi poin-poin kecil supaya gampang dipantau' });
                setHeroMessages(msgs);

                $('#wt-ring').attr('aria-label', 'Progress tim ' + s.progress + '%');
                countUp(document.getElementById('wt-ring-pct'), state.ringPct, s.progress, function (v) { return Math.round(v) + '%'; });
                if (state.ringPct !== s.progress) {
                    // Angka memantul begitu ring selesai berputar
                    const ring = document.getElementById('wt-ring');
                    ring.classList.remove('pop');
                    setTimeout(function () { ring.classList.add('pop'); }, 1300);
                }
                state.ringPct = s.progress;
                requestAnimationFrame(function () {
                    requestAnimationFrame(function () {
                        document.getElementById('wt-ring-value').style.strokeDashoffset = 100 - s.progress;
                    });
                });
            }

            function setHeroMessages(msgs) {
                const key = JSON.stringify(msgs);
                if (state.msgKey === key) return;
                const firstChanged = !state.msgs || JSON.stringify(state.msgs[0]) !== JSON.stringify(msgs[0]);
                state.msgKey = key;
                state.msgs = msgs;
                if (firstChanged || state.msgIdx >= msgs.length) showHeroMessage(0);
            }

            function showHeroMessage(idx) {
                clearTimeout(state.msgTimer);
                state.msgIdx = idx;
                const m = state.msgs[idx];
                const $msg = $('#wt-verdict');
                $msg.addClass('flip');
                setTimeout(function () {
                    $msg.attr('class', 'wt-verdict ' + m.cls + ' flip');
                    $msg.find('.wt-msg-body').html('<i class="fa-solid ' + m.icon + '"></i> ' + esc(m.text));
                    void $msg[0].offsetHeight;
                    $msg.removeClass('flip');
                    const $bar = $msg.find('.wt-msg-bar').removeClass('run');
                    if (state.msgs.length > 1) {
                        const seconds = m.cls === 'bad' ? 9 : 6;
                        $msg[0].style.setProperty('--msg-dur', seconds + 's');
                        void $bar[0].offsetWidth;
                        $bar.addClass('run');
                        state.msgTimer = setTimeout(function () { showHeroMessage((state.msgIdx + 1) % state.msgs.length); }, seconds * 1000);
                    }
                }, 300);
            }

            function tickClock() {
                const now = new Date();
                const pad = function (n) { return String(n).padStart(2, '0'); };
                $('#wt-clock').html(pad(now.getHours()) + '<span class="colon">:</span>' + pad(now.getMinutes()) + '<span class="colon">:</span>' + pad(now.getSeconds()));
            }

            function initHeroFx() {
                // Judul muncul per huruf
                const $title = $('#wt-hero-title .wt-title-text');
                $title.html($title.text().split('').map(function (ch, i) {
                    return '<span class="ch' + (ch === ' ' ? ' sp' : '') + '" style="--i:' + i + '">' + (ch === ' ' ? '&nbsp;' : esc(ch)) + '</span>';
                }).join(''));

                // Gelembung naik dengan ukuran dan kecepatan acak
                $('#wt-bubbles').html(Array.from({ length: 16 }, function () {
                    const t = 7 + Math.random() * 9;
                    return '<span style="--l:' + (Math.random() * 100).toFixed(1) + '%;--z:' + (4 + Math.random() * 12).toFixed(0) + 'px;--t:' + t.toFixed(1) + 's;--dl:-' + (Math.random() * t).toFixed(1) + 's;--sw:' + ((Math.random() - .5) * 50).toFixed(0) + 'px"></span>';
                }).join(''));

                // Ikon dan bulatan latar ikut bergeser mengikuti kursor
                const hero = document.getElementById('wt-hero');
                $(hero).on('mousemove', function (e) {
                    const r = hero.getBoundingClientRect();
                    hero.style.setProperty('--mx', (((e.clientX - r.left) / r.width) - .5) * 2);
                    hero.style.setProperty('--my', (((e.clientY - r.top) / r.height) - .5) * 2);
                }).on('mouseleave', function () {
                    hero.style.setProperty('--mx', 0);
                    hero.style.setProperty('--my', 0);
                });

                // Efek riak saat tombol hero diklik
                $(hero).on('pointerdown', '.wt-btn-light, .wt-btn-ghost', function (e) {
                    const r = this.getBoundingClientRect();
                    const size = Math.max(r.width, r.height);
                    $('<span class="wt-ripple"></span>').css({ width: size, height: size, left: e.clientX - r.left - size / 2, top: e.clientY - r.top - size / 2 })
                        .appendTo(this).on('animationend', function () { $(this).remove(); });
                });

                tickClock();
                setInterval(tickClock, 1000);

                // Warna hero berpindah pelan dari satu palet ke palet berikutnya
                let palette = 0;
                setInterval(function () {
                    palette = (palette + 1) % HERO_PALETTES.length;
                    hero.style.setProperty('--hero-a', HERO_PALETTES[palette][0]);
                    hero.style.setProperty('--hero-b', HERO_PALETTES[palette][1]);
                }, 20000);
            }

            function countUp(el, from, to, format) {
                cancelAnimationFrame(el.wtCount);
                if (from === to) {
                    el.textContent = format(to);
                    return;
                }
                const DURATION = 900;
                const begin = performance.now();
                el.textContent = format(from);
                const step = function (now) {
                    const t = Math.min(1, (now - begin) / DURATION);
                    el.textContent = format(from + (to - from) * (1 - Math.pow(1 - t, 3)));
                    if (t < 1) el.wtCount = requestAnimationFrame(step);
                };
                el.wtCount = requestAnimationFrame(step);
            }

            function assignOptionText(p) {
                if (!p.active) return p.name + ' (belum ada kerjaan)';
                const load = 'sisa ' + num(p.effort_left) + ' hari';
                return p.name + ' (' + (p.available_days === null ? load : load + ', tersedia ' + p.available_days) + ')';
            }

            function memberDivisions() {
                return state.board.divisions.filter(function (d) {
                    return state.board.programmers.some(function (p) { return p.division === d; });
                });
            }

            function programmerOptions(optionHtml) {
                return memberDivisions().map(function (d) {
                    const members = state.board.programmers.filter(function (p) { return p.division === d; });
                    return '<optgroup label="' + esc(d) + '">' + members.map(optionHtml).join('') + '</optgroup>';
                }).join('');
            }

            function assignSelect(item, emptyText) {
                const current = item.programmer_id === null ? '' : Number(item.programmer_id);
                const options = programmerOptions(function (p) {
                    return '<option value="' + p.id + '" data-name="' + esc(p.name) + '"' + (p.id === current ? ' selected' : '') + '>' + esc(assignOptionText(p)) + '</option>';
                });
                return '<div class="wt-assign"><select class="wt-assign-select" data-id="' + item.id + '" data-current="' + current + '" aria-label="Anggota untuk ' + esc(item.title) + '"><option value="">' + esc(emptyText) + '</option>' + options + '</select></div>';
            }

            function initAssignSelects($root) {
                $root.find('.wt-assign-select').select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    dropdownAutoWidth: true,
                    minimumResultsForSearch: 8,
                    templateSelection: function (option) {
                        return option.element && option.element.dataset.name ? option.element.dataset.name : option.text;
                    },
                });
            }

            function destroyAssignSelects($root) {
                $root.find('.wt-assign-select.select2-hidden-accessible').select2('destroy');
            }

            function renderQueue(items) {
                const rank = { major: 1, minor: 2 };
                const queue = items.filter(function (i) {
                    return ['baru', 'dikerjakan'].includes(i.status) && i.programmer_id === null;
                }).sort(function (a, b) {
                    const levelA = a.urgent ? 0 : (rank[a.level] || 3);
                    const levelB = b.urgent ? 0 : (rank[b.level] || 3);
                    if (levelA !== levelB) return levelA - levelB;
                    const targetA = a.target_date || '9999-12-31';
                    const targetB = b.target_date || '9999-12-31';
                    return targetA < targetB ? -1 : (targetA > targetB ? 1 : a.id - b.id);
                });

                if (!queue.length) {
                    const text = state.board.summary.unassigned ? 'Tidak ada antrean untuk filter ini.' : 'Semua kerjaan sudah punya programmer.';
                    $view.html('<div class="wt-empty"><i class="fa-solid fa-inbox"></i>' + text + '</div>');
                    return;
                }

                const noProgrammer = !state.board.programmers.length;
                $view.html(queue.map(function (i) {
                    const overdue = i.target_date && i.target_date < state.board.today;
                    const target = i.target_date ? '<span class="' + (overdue ? 'wt-queue-overdue' : '') + '">Target ' + fmt(i.target_date) + '</span>' : '<span class="wt-sub">Belum ada target</span>';
                    return '<div class="wt-queue-row"><div style="min-width:0"><button type="button" class="wt-queue-title" data-item="' + i.id + '">' + esc(i.title) + '</button><div class="wt-sub">' + esc([i.code, i.module].filter(Boolean).join(' · ')) + '</div></div>' +
                        '<div>' + typeChip(i) + ' ' + levelChip(i) + (i.urgent ? ' <span class="wt-chip h-telat">Mendesak</span>' : '') + '</div><div>' + target + '</div>' +
                        '<div>' + (noProgrammer ? '<span class="wt-sub">Tambah anggota dulu</span>' : assignSelect(i, 'Pilih anggota')) + '</div></div>';
                }).join(''));
                initAssignSelects($view);
            }

            function setView(view) {
                state.view = view;
                $('#wt-tabs button').each(function () { $(this).toggleClass('active', this.dataset.view === view); });
                $('#wt-f-division-box, #wt-f-programmer-box, #wt-f-health-box, #wt-division-chips').toggleClass('d-none', view === 'queue');
                $('#wt-f-status-box').toggleClass('d-none', view === 'queue' || view === 'kanban');
                $('.wt-report-only').toggleClass('d-none', view !== 'report');
                $('#wt-f-programmer-box').toggleClass('d-none', view === 'queue');

                // Laporan bawaannya menampilkan semua status, pilihan sebelumnya dikembalikan saat keluar
                const $status = $('#wt-f-status');
                if (view === 'report' && state.statusBeforeReport === null) {
                    state.statusBeforeReport = $status.val();
                    $status.val('').trigger('change.select2');
                    if (!$('#wt-f-from').val() && state.board) {
                        const today = parseDate(state.board.today);
                        const iso = function (d) { return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0'); };
                        $('#wt-f-from').val(iso(new Date(today.getFullYear(), today.getMonth(), 1)));
                        $('#wt-f-to').val(iso(new Date(today.getFullYear(), today.getMonth() + 1, 0)));
                    }
                } else if (view !== 'report' && state.statusBeforeReport !== null) {
                    $status.val(state.statusBeforeReport).trigger('change.select2');
                    state.statusBeforeReport = null;
                }
                renderView();
            }

            function reportParams() {
                const params = {
                    from: $('#wt-f-from').val(), to: $('#wt-f-to').val(), division: state.divisions.join(','), programmer: $('#wt-f-programmer').val(), type: $('#wt-f-type').val(),
                    status: $('#wt-f-status').val(), level: $('#wt-f-level').val(), health: $('#wt-f-health').val(), search: $('#wt-f-search').val(),
                };
                if (state.demo) params.demo = 1;
                return params;
            }

            function renderReport() {
                clearTimeout(state.reportTimer);
                state.reportTimer = setTimeout(function () {
                    const params = reportParams();
                    if (!params.from || !params.to) {
                        $view.html('<div class="wt-empty"><i class="fa-regular fa-calendar"></i>Isi periode dulu.</div>');
                        return;
                    }
                    const seq = ++state.reportSeq;
                    $.get(ROUTES.report, params).done(function (res) {
                        if (seq !== state.reportSeq || state.view !== 'report') return;
                        state.reportItems = res.items;
                        drawReport(res.items);
                    }).fail(failed);
                }, 200);
            }

            function drawReport(items) {
                const count = function (status) { return items.filter(function (i) { return i.status === status; }).length; };
                const header = '<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2"><div class="small"><b>' + items.length + ' kerjaan</b>: ' +
                    count('selesai') + ' selesai, ' + count('dikerjakan') + ' dikerjakan, ' + count('baru') + ' baru, ' + count('ditolak') + ' ditolak</div>' +
                    '<button type="button" class="btn btn-success btn-sm" id="wt-export"' + (items.length ? '' : ' disabled') + '><i class="fa-solid fa-file-excel"></i> Export Excel</button></div>';
                if (!items.length) {
                    $view.html(header + '<div class="wt-empty"><i class="fa-regular fa-folder-open"></i>Tidak ada kerjaan pada periode dan filter ini.</div>');
                    return;
                }
                const rows = items.map(function (i) {
                    const sub = [i.code, i.module, i.requester ? 'dari ' + i.requester + (i.requester_dept ? ' (' + i.requester_dept + ')' : '') : null].filter(Boolean).join(' · ');
                    return '<tr data-id="' + i.id + '"><td><div class="wt-title">' + esc(i.title) + '</div><div class="wt-sub">' + esc(sub) + '</div></td>' +
                        '<td>' + typeChip(i) + ' ' + levelChip(i) + '</td><td>' + esc(i.programmer_name || 'Belum ditugaskan') + (i.programmer_division ? '<div class="wt-sub">' + esc(i.programmer_division) + '</div>' : '') + '</td>' +
                        '<td>' + esc(STATUS_LABEL[i.status]) + '</td><td><b>' + Number(i.progress) + '%</b></td>' +
                        '<td>' + fmt(i.start_date, true) + '</td><td>' + fmt(i.target_date, true) + '</td><td>' + fmt(i.finished_at, true) + '</td><td>' + healthChip(i) + '</td></tr>';
                }).join('');
                $view.html(header + '<div class="table-responsive"><table class="wt-table"><thead><tr><th>Kerjaan</th><th>Jenis</th><th>Anggota</th><th>Status</th><th>Progress</th><th>Mulai</th><th>Target</th><th>Selesai</th><th>Kondisi</th></tr></thead><tbody>' + rows + '</tbody></table></div>');
            }

            function assignProgrammer(id, programmerId) {
                if (guardDemo()) {
                    render();
                    return;
                }
                const request = post(ROUTES.assign, { id: id, programmer_id: programmerId }, function (res) { toast(res.message); });
                if (request) request.always(load);
            }

            function sparkline(values) {
                const max = Math.max.apply(null, values.concat([1]));
                const y = function (v) { return 24 - v / max * 21; };
                const x = function (n) { return n / (values.length - 1) * 100; };
                const line = values.map(function (v, n) { return (n ? 'L' : 'M') + x(n) + ' ' + y(v); }).join(' ');
                const text = values.map(num).join(', ');
                return '<span class="wt-trend" role="img" title="7 hari terakhir: ' + text + '" aria-label="Tren 7 hari terakhir: ' + text + '">' +
                    '<svg viewBox="0 0 100 26" preserveAspectRatio="none" aria-hidden="true"><path class="wt-trend-area" d="' + line + ' L100 26 L0 26 Z"/><path class="wt-trend-line" d="' + line + '"/></svg>' +
                    '<span class="wt-trend-dot" style="top:' + y(values[values.length - 1]) + 'px"></span></span>';
            }

            function renderTiles() {
                const s = state.board.summary;
                const urgentCount = (s.urgent || []).length;
                const ofActive = s.active ? 'dari<b>' + s.active + '</b>kerjaan aktif' : 'Belum ada kerjaan aktif';
                const tiles = [
                    { key: 'active', label: 'Kerjaan aktif', value: s.active, icon: 'fa-layer-group', tint: '#e7f0ff', accent: '#1b5fc1', trend: true },
                    { key: 'late', label: 'Diprediksi telat', value: s.late, icon: 'fa-fire', tint: '#ffe3e6', accent: '#c21f30', foot: ofActive, alert: s.late > 0 },
                    { key: 'tight', label: 'Mepet target', value: s.tight, icon: 'fa-hourglass-half', tint: '#fff2d6', accent: '#a86700', foot: ofActive },
                    { key: 'bugs_open', label: urgentCount ? 'Masalah terbuka, ' + urgentCount + ' mendesak' : 'Masalah terbuka', value: s.bugs_open, icon: 'fa-triangle-exclamation', tint: '#ffe9ec', accent: '#c92a3c', trend: true, alert: urgentCount > 0 },
                    { key: 'requests_new', label: 'Request baru', value: s.requests_new, icon: 'fa-comment-dots', tint: '#f1e9ff', accent: '#7338c9', trend: true },
                    { key: 'effort_left', label: 'Sisa beban (hari)', value: s.effort_left, icon: 'fa-stopwatch', tint: '#e0f7ea', accent: '#0c7f3e', trend: true },
                    { key: 'unassigned', label: 'Belum ditugaskan', value: s.unassigned, icon: 'fa-inbox', tint: '#fff2d6', accent: '#a86700', foot: s.unassigned ? 'Klik untuk mengatur' : 'Semua sudah punya anggota', goto: 'queue' },
                ];
                $('#wt-tiles').html(tiles.map(function (t, i) {
                    const link = t.goto ? ' wt-tile-link" role="button" tabindex="0" title="Buka tab Antrean" data-goto="' + t.goto : '';
                    const foot = t.trend ? sparkline(s.trend[t.key]) : t.foot;
                    return '<div class="wt-card wt-tile' + (t.alert ? ' wt-tile-alert' : '') + link + '" style="--tint:' + t.tint + ';--accent:' + t.accent + ';animation-delay:' + (i * 50) + 'ms">' +
                        '<div class="wt-tile-top"><div class="wt-tile-icon"><i class="fa-solid ' + t.icon + '"></i></div>' +
                        '<div><div class="wt-tile-value" data-key="' + t.key + '"></div><div class="wt-tile-label">' + esc(t.label) + '</div></div></div>' +
                        '<div class="wt-tile-foot">' + foot + '</div></div>';
                }).join(''));
                tiles.forEach(function (t) {
                    countUp(document.querySelector('.wt-tile-value[data-key="' + t.key + '"]'), state.tileValues[t.key] || 0, Number(t.value), function (v) {
                        return t.key === 'effort_left' ? num(Math.round(v * 10) / 10) : String(Math.round(v));
                    });
                    state.tileValues[t.key] = Number(t.value);
                });
            }

            function runnerSvg(p) {
                const fx = {
                    sprint: '<i class="fa-solid fa-cloud wt-fx wt-fx-dust"></i><i class="fa-solid fa-cloud wt-fx wt-fx-dust two"></i><i class="fa-solid fa-fire-flame-curved wt-fx wt-fx-flame"></i><span class="wt-speed"></span><span class="wt-speed two"></span>',
                    jog: '<i class="fa-solid fa-music wt-fx wt-fx-note"></i><i class="fa-solid fa-music wt-fx wt-fx-note two"></i>',
                    walk: '<i class="fa-solid fa-droplet wt-fx wt-fx-sweat"></i><i class="fa-solid fa-cloud-showers-heavy wt-fx wt-fx-rain"></i>',
                    rest: '<span class="wt-fx wt-fx-zzz">z</span><span class="wt-fx wt-fx-zzz two">z</span><i class="fa-solid fa-mug-hot wt-fx wt-fx-mug"></i>',
                    finish: '<i class="fa-solid fa-trophy wt-fx wt-fx-cup"></i>',
                };
                const incidentFx = {
                    panic: '<i class="fa-solid fa-exclamation wt-fx wt-fx-alarm"></i>',
                    fixing: '<i class="fa-solid fa-wrench wt-fx wt-fx-fix"></i>',
                };
                return (p.incident ? incidentFx[p.incident] : fx[p.tier]) + '<svg class="wt-runner" viewBox="0 0 54 56" style="--c:' + esc(p.color) + '" aria-hidden="true"><g class="figure">' +
                    '<circle class="head" cx="27" cy="9" r="6.5"/>' +
                    '<path class="limb arm-b" d="M27 20 L27 33" opacity=".55"/>' +
                    '<path class="limb leg-a" d="M27 33 L27 52" opacity=".55"/>' +
                    '<line class="torso" x1="27" y1="18" x2="27" y2="33"/>' +
                    '<path class="limb leg-b" d="M27 33 L27 52"/>' +
                    '<path class="limb arm-a" d="M27 20 L27 33"/>' +
                    '</g></svg>';
            }

            // Suasana Arena mengikuti jam & tanggal di komputer pengguna, dicek ulang tiap menit
            const SKY_LABEL = { dawn: 'Subuh', morning: 'Pagi', day: 'Siang', afternoon: 'Sore', sunset: 'Senja', night: 'Malam', midnight: 'Tengah malam' };
            const SCENES = ['hills', 'beach', 'city', 'paddy'];
            const SCENE_LABEL = { hills: 'Perbukitan', beach: 'Pantai', city: 'Kota', paddy: 'Sawah' };
            const SPECIAL_LABEL = { newyear: 'Tahun baru', merdeka: 'Bulan kemerdekaan', monthend: 'Akhir bulan', friday: 'Jumat sore' };
            const STARS = [[8, 16], [24, 36], [41, 22], [58, 40], [73, 18], [15, 30], [33, 12], [50, 34], [66, 26], [82, 38]];

            function skyAt(hour) {
                if (hour >= 4 && hour < 6) return 'dawn';
                if (hour >= 6 && hour < 10) return 'morning';
                if (hour >= 10 && hour < 14) return 'day';
                if (hour >= 14 && hour < 16) return 'afternoon';
                if (hour >= 16 && hour < 18) return 'sunset';
                if (hour >= 18 && hour < 22) return 'night';
                return 'midnight';
            }

            function ambienceNow() {
                const now = new Date();
                const hour = now.getHours(), date = now.getDate(), month = now.getMonth();
                const lastDay = new Date(now.getFullYear(), month + 1, 0).getDate();
                const dayNumber = Math.floor(Date.UTC(now.getFullYear(), month, date) / 864e5);
                const specials = [];
                if ((month === 11 && date === 31 && hour >= 18) || (month === 0 && date === 1)) specials.push('newyear');
                if (month === 7) specials.push('merdeka');
                if (date >= lastDay - 1) specials.push('monthend');
                if (now.getDay() === 5 && hour >= 15) specials.push('friday');
                const sky = skyAt(hour);
                return { sky: sky, dark: sky === 'night' || sky === 'midnight', scene: SCENES[dayNumber % SCENES.length], specials: specials };
            }

            function ambienceKey(a) {
                return a.sky + '|' + a.scene + '|' + a.specials.join(',');
            }

            function applyAmbience() {
                const a = state.ambience = ambienceNow();
                $('.wt-race').attr({ 'data-sky': a.sky, 'data-scene': a.scene, 'data-dark': a.dark ? '1' : null });
                $('#wt-scene').html('<i class="fa-solid ' + (a.dark ? 'fa-moon' : 'fa-sun') + '"></i>' +
                    esc([SKY_LABEL[a.sky], SCENE_LABEL[a.scene]].concat(a.specials.map(function (s) { return SPECIAL_LABEL[s]; })).join(' · ')));
            }

            function sceneProps(scene, index) {
                const at = function (n) { return ((index * 0.37 + n) % 1).toFixed(2); };
                const prop = function (icon, n, extra) {
                    return '<i class="fa-solid ' + icon + ' wt-prop' + (extra || '') + '" style="--at:' + at(n) + '"></i>';
                };
                if (scene === 'beach') return '<i class="fa-solid fa-sailboat wt-boat" style="animation-delay:-' + (index * 9 + 5) + 's"></i>' + prop('fa-umbrella-beach', 0.2);
                if (scene === 'city') return prop('fa-tree', 0.1, ' small') + prop('fa-tree', 0.6, ' small');
                if (scene === 'paddy') return prop('fa-wheat-awn', 0.15) + prop('fa-wheat-awn', 0.45, ' small') + prop('fa-wheat-awn', 0.8);
                return prop('fa-tree', 0.25) + prop('fa-tree', 0.7, ' small');
            }

            function specialDecor(specials, index) {
                let html = '';
                if (specials.includes('merdeka')) html += '<div class="wt-bunting"><i></i></div>';
                if (specials.includes('newyear')) {
                    html += [[18, 22, '#f2c94c'], [44, 14, '#eb5757'], [67, 28, '#56ccf2']].map(function (f, n) {
                        return '<span class="wt-firework" style="left:' + f[0] + '%;top:' + f[1] + 'px;color:' + f[2] + ';animation-delay:' + ((n * .6 + index * .3) % 1.9).toFixed(1) + 's"></span>';
                    }).join('');
                }
                if (specials.includes('friday')) {
                    html += [[22, 30], [48, 22], [70, 34]].map(function (pos, n) {
                        return '<i class="fa-solid fa-music wt-sky-note" style="left:' + pos[0] + '%;top:' + pos[1] + 'px;animation-delay:' + (n * 1.1) + 's"></i>';
                    }).join('');
                }
                if (specials.includes('monthend')) {
                    html += '<div class="wt-plane" style="animation-delay:-' + (index * 7 + 3) + 's"><i class="fa-solid fa-plane"></i><span>Akhir bulan, tetap semangat!</span></div>';
                }
                return html;
            }

            function trackDecor(p, index) {
                const speed = { sprint: 9, jog: 16, walk: 30, rest: 46, finish: 22 }[p.tier];
                const a = state.ambience || ambienceNow();
                const starCount = { dawn: 3, night: 5, midnight: 10 }[a.sky] || 0;
                let html = STARS.slice(0, starCount).map(function (star, n) {
                        return '<i class="fa-solid fa-star wt-star" style="left:' + star[0] + '%;top:' + (star[1] + 12) + 'px;animation-delay:' + (n * .5) + 's"></i>';
                    }).join('') +
                    '<i class="fa-solid ' + (a.dark ? 'fa-moon' : 'fa-sun') + ' wt-sun"></i>' +
                    '<div class="wt-hill far" style="animation-delay:-' + (index * 7) + 's"></div><div class="wt-hill near" style="animation-delay:-' + (index * 3) + 's"></div>' +
                    sceneProps(a.scene, index) +
                    '<i class="fa-solid fa-cloud wt-cloud" style="top:26px;--dur:' + speed + 's;animation-delay:-' + (index * 3 + 2) + 's"></i>' +
                    '<i class="fa-solid fa-cloud wt-cloud mini" style="top:44px;--dur:' + (speed * 1.5) + 's;animation-delay:-' + (index * 5 + 9) + 's"></i>' +
                    '<i class="fa-solid fa-dove wt-bird" style="animation-delay:-' + (index * 6 + 4) + 's"></i>' +
                    specialDecor(a.specials, index);
                const cheering = p.tier === 'finish' || p.progress >= 70;
                html += '<div class="wt-gate start"><span>START</span></div><div class="wt-gate end"><span>FINISH</span></div>';
                html += '<div class="wt-crowd' + (cheering ? ' cheer' : '') + '" title="Penonton">' + ['#eb5757', '#2f80ed', '#f2c94c', '#27ae60', '#9b51e0'].map(function (color, n) {
                    return '<i class="fa-solid fa-person" style="color:' + color + ';animation-delay:' + (n * .12) + 's"></i>';
                }).join('') + '</div>';
                if (p.tier === 'finish') {
                    html += ['#eb5757', '#f2c94c', '#2f80ed', '#27ae60'].map(function (color, n) {
                        return '<span class="wt-balloon" style="background:' + color + ';right:' + (70 + n * 34) + 'px;animation-delay:-' + (n * 1.1) + 's"></span>';
                    }).join('') + [0, 1, 2].map(function (n) {
                        return '<i class="fa-solid fa-star wt-spark" style="right:' + (60 + n * 62) + 'px;top:' + (28 + (n % 2) * 18) + 'px;animation-delay:' + (n * .4) + 's"></i>';
                    }).join('');
                }
                return html;
            }

            function sayKind(p) {
                return p.incident || p.tier;
            }

            function updateSay() {
                document.querySelectorAll('.wt-say').forEach(function (el) {
                    const lines = SAY[el.dataset.kind] || [];
                    if (!lines.length) return;
                    el.textContent = lines[Math.floor(Math.random() * lines.length)];
                    el.classList.remove('pop');
                    void el.offsetWidth;
                    el.classList.add('pop');
                });
            }

            function laneNote(p) {
                if (p.incident) {
                    const first = p.urgent[0];
                    const more = p.urgent.length > 1 ? ' (+' + (p.urgent.length - 1) + ' lagi)' : '';
                    return '<b>' + (p.incident === 'panic' ? 'Ada masalah!' : 'Sedang dibetulkan') + '</b>' + esc(first.title + more);
                }
                let line = '';
                if (p.active === 0) {
                    line = p.done_month > 0 ? p.done_month + ' kerjaan selesai bulan ini' : 'Bisa diberi kerjaan baru';
                } else if (p.available_days === null) {
                    line = 'Sisa ' + num(p.effort_left) + ' hari, belum ada target';
                } else if (p.gap_days < 0) {
                    line = 'Sisa ' + num(p.effort_left) + ' hari, tersedia ' + p.available_days + '. Kurang ' + num(Math.abs(p.gap_days)) + ' hari';
                } else {
                    line = 'Sisa ' + num(p.effort_left) + ' hari, tersedia ' + p.available_days + ' hari';
                }
                return '<b>' + esc(TIER_TITLE[p.tier]) + '</b>' + esc(line);
            }

            function checkpointKind(c) {
                if (c.status === 'selesai') return 'done';
                if (c.health === 'telat') return 'late';
                return c.status === 'dikerjakan' ? 'progress' : 'todo';
            }

            function checkpointsHtml(p) {
                const icons = { done: 'fa-check', late: 'fa-exclamation', progress: 'fa-wrench', todo: 'fa-hourglass-start' };
                return p.checkpoints.map(function (c) {
                    const kind = checkpointKind(c);
                    const center = 'calc((100% - 72px) * ' + (c.position / 100) + ' + 27px)';
                    const labelLeft = 'calc((100% - 72px) * ' + (c.start_position / 100) + ' + 27px)';
                    const labelWidth = 'calc((100% - 72px) * ' + ((c.position - c.start_position) / 100) + ')';
                    const title = esc(c.code + ' ' + c.title + ' | ' + c.progress + '% | Target ' + fmt(c.target_date) + ' | ' + HEALTH_LABEL[c.health]);
                    return '<button type="button" class="wt-cp-label cp-' + kind + '" data-item="' + c.id + '" title="' + title + '" style="left:' + labelLeft + ';width:' + labelWidth + '"><span>' + esc(c.title) + '</span></button>' +
                        '<div class="wt-cp-pole" style="left:' + center + '"></div>' +
                        '<button type="button" class="wt-cp cp-' + kind + '" data-item="' + c.id + '" title="' + title + '" aria-label="' + title + '" style="left:' + center + '"><i class="fa-solid ' + icons[kind] + '"></i></button>';
                }).join('');
            }

            // Nama kerjaan disembunyikan kalau ruangnya terlalu sempit, judul tetap muncul saat kursor diarahkan ke pos
            function fitCheckpointLabels() {
                document.querySelectorAll('.wt-cp-label').forEach(function (label) {
                    label.classList.remove('is-hidden');
                    label.classList.toggle('is-hidden', label.clientWidth < 48);
                });
            }

            // Divisi boleh dipilih lebih dari satu; tanpa pilihan berarti semua divisi
            function divisionShown(d) {
                return !state.divisions.length || state.divisions.includes(d);
            }

            function renderDivisionChips() {
                const divisions = memberDivisions();
                if (divisions.length < 2) {
                    $('#wt-division-chips').empty();
                    return;
                }
                $('#wt-division-chips').html([''].concat(divisions).map(function (d) {
                    const active = d ? state.divisions.includes(d) : !state.divisions.length;
                    return '<button type="button" data-division="' + esc(d) + '" class="' + (active ? 'active' : '') + '" aria-pressed="' + active + '">' + (d && active ? '<i class="fa-solid fa-check"></i> ' : '') + esc(d || 'Semua divisi') + '</button>';
                }).join(''));
            }

            function renderLanes() {
                applyAmbience();
                const programmers = state.board.programmers;
                if (!programmers.length) {
                    $('#wt-division-chips').empty();
                    $('#wt-lanes').html('<div class="wt-empty"><i class="fa-solid fa-person-running"></i>Belum ada anggota di arena.<div class="mt-3 d-flex gap-2 justify-content-center flex-wrap">' +
                        '<button class="btn btn-sb btn-sm" id="wt-empty-programmer"><i class="fa-solid fa-user-plus"></i> Tambah Anggota</button>' +
                        '<button class="btn btn-outline-secondary btn-sm" id="wt-demo-start"><i class="fa-solid fa-eye"></i> Lihat Contoh Tampilan</button></div></div>');
                    return;
                }

                const road = { sprint: '.45s', jog: '1s', walk: '2.6s', rest: '0s', finish: '0s' };

                const laneHtml = function (p, index) {
                    const selected = state.selected === p.id ? ' selected' : '';
                    const incident = p.incident ? ' incident-' + p.incident : '';
                    const pos = 'calc((100% - 72px) * ' + (p.progress / 100) + ')';
                    const pacerLeft = 'calc((100% - 72px) * ' + (p.expected_progress / 100) + ' + 27px)';
                    const pacer = p.active ? '<div class="wt-pacer" data-left="' + pacerLeft + '" style="left:' + (state.pos[p.id] ? pacerLeft : '27px') + '" title="Posisi seharusnya hari ini: ' + p.expected_progress + '%"></div>' : '';
                    const jump = state.jumpers.includes(p.id) ? ' jump' : '';
                    const obstacle = p.incident ? '<div class="wt-obstacle" style="left:min((100% - 72px) * ' + (p.progress / 100) + ' + 60px, 100% - 56px)" title="' + esc(p.urgent[0].title) + '"><i class="fa-solid fa-road-barrier"></i></div>' : '';
                    const sayCorner = p.progress >= 70 ? ' left' : '';
                    return '<div class="wt-lane tier-' + p.tier + incident + selected + '" data-programmer="' + p.id + '" title="Klik untuk filter kerjaan anggota ini">' +
                        '<div class="wt-person"><div class="wt-avatar" style="background:' + esc(p.color) + '">' + esc(initials(p.name)) + '</div>' +
                        '<div style="min-width:0"><div class="wt-person-name">' + esc(p.name) + '</div>' +
                        '<div class="wt-person-sub">' + p.active + ' aktif' + (p.late ? ', ' + p.late + ' telat' : '') + '</div></div></div>' +
                        '<div class="wt-track">' + trackDecor(p, index) + '<div class="wt-track-road" style="--road:' + (p.incident ? '0s' : road[p.tier]) + '"></div>' + pacer +
                        checkpointsHtml(p) + obstacle + '<div class="wt-finish"></div>' +
                        '<div class="wt-runner-box' + jump + '" data-programmer-box="' + p.id + '" data-left="' + pos + '" style="left:' + (state.pos[p.id] || '0') + '">' +
                        '<div class="wt-pct">' + p.progress + '%</div>' +
                        '<div class="wt-say' + sayCorner + '" data-kind="' + sayKind(p) + '">' + esc((SAY[sayKind(p)] || [''])[0]) + '</div>' + runnerSvg(p) + '</div></div>' +
                        '<div class="wt-lane-note tier-' + p.tier + (p.incident ? ' incident' : '') + '">' + laneNote(p) + '</div></div>';
                };

                renderDivisionChips();
                let laneIndex = 0;
                $('#wt-lanes').html(memberDivisions().filter(divisionShown).map(function (d) {
                    const members = programmers.filter(function (p) { return p.division === d; });
                    return '<div class="wt-division-head">' + esc(d) + ' <span>' + members.length + ' orang</span></div>' + members.map(function (p) { return laneHtml(p, laneIndex++); }).join('');
                }).join(''));
                fitCheckpointLabels();

                // Posisi diisi setelah render supaya pelari terlihat berlari dari garis start
                requestAnimationFrame(function () {
                    requestAnimationFrame(function () {
                        $('#wt-lanes [data-left]').each(function () { this.style.left = this.dataset.left; });
                        programmers.forEach(function (p) { state.pos[p.id] = 'calc((100% - 72px) * ' + (p.progress / 100) + ')'; });
                        state.jumpers = [];
                    });
                });
            }

            function filteredItems() {
                const search = ($('#wt-f-search').val() || '').toLowerCase();
                const programmer = $('#wt-f-programmer').val();
                const type = $('#wt-f-type').val();
                const status = $('#wt-f-status').val();
                const health = $('#wt-f-health').val();
                const level = $('#wt-f-level').val();
                const queueView = state.view === 'queue';

                return state.board.items.filter(function (i) {
                    if (!queueView && state.divisions.length) {
                        const owner = programmerOf(i);
                        if (!owner || !state.divisions.includes(owner.division)) return false;
                    }
                    if (!queueView && (programmer === 'none' ? i.programmer_id !== null : (programmer && Number(i.programmer_id) !== Number(programmer)))) return false;
                    if (type && i.type !== type) return false;
                    if (level === 'none' ? i.level !== null : (level && i.level !== level)) return false;
                    if (state.view !== 'kanban' && !queueView) {
                        if (status === 'open' && !['baru', 'dikerjakan'].includes(i.status)) return false;
                        if (status && status !== 'open' && i.status !== status) return false;
                    }
                    if (!queueView && health && i.health !== health) return false;
                    if (search && ![i.title, i.module, i.requester, i.requester_dept, i.code].join(' ').toLowerCase().includes(search)) return false;
                    return true;
                });
            }

            // Badge di tombol Filter: jumlah filter tambahan yang aktif di tab ini
            function updateFilterCount() {
                const statusDefault = state.view === 'report' ? '' : 'open';
                let count = 0;
                if (!$('#wt-f-division-box').hasClass('d-none') && state.divisions.length) count++;
                [[null, '#wt-f-type', ''], ['#wt-f-status-box', '#wt-f-status', statusDefault], [null, '#wt-f-level', ''], ['#wt-f-health-box', '#wt-f-health', '']].forEach(function (f) {
                    if (f[0] && $(f[0]).hasClass('d-none')) return;
                    if (($(f[1]).val() || '') !== f[2]) count++;
                });
                const programmerActive = !$('#wt-f-programmer-box').hasClass('d-none') && !!$('#wt-f-programmer').val();
                $('#wt-filter-count').text(count).toggleClass('d-none', !count);
                $('#wt-filter-reset').toggleClass('d-none', !count && !programmerActive && !$('#wt-f-search').val());
            }

            function setArenaFolded(folded, animate) {
                const $body = $('#wt-arena-body');
                $('#wt-race').toggleClass('folded', folded);
                $('#wt-arena-fold').attr('aria-expanded', String(!folded)).find('span').text(folded ? 'Buka' : 'Lipat');
                if (!animate) {
                    $body.toggle(!folded);
                } else if (folded) {
                    $body.stop(true).slideUp(250);
                } else {
                    // Label pos diukur ulang setelah terbuka karena saat terlipat lebarnya nol
                    $body.stop(true).slideDown(250, fitCheckpointLabels);
                }
                try { localStorage.setItem('wt-arena-folded', folded ? '1' : '0'); } catch (e) { /* penyimpanan browser tidak tersedia */ }
            }

            function renderView() {
                updateFilterCount();
                if (!state.board) return;
                destroyAssignSelects($view);
                if (state.view === 'report') {
                    renderReport();
                    return;
                }
                const items = filteredItems();
                if (state.view === 'queue') renderQueue(items);
                else if (state.view === 'kanban') renderKanban(items);
                else if (state.view === 'timeline') renderTimeline(items);
                else renderList(items);
            }

            function typeChip(i) {
                return '<span class="wt-chip t-' + esc(i.type) + '"><i class="fa-solid ' + TYPE_ICON[i.type] + '"></i> ' + TYPE_LABEL[i.type] + '</span>';
            }
            function levelChip(i) {
                return i.level ? '<span class="wt-chip l-' + esc(i.level) + '">' + LEVEL_LABEL[i.level] + '</span>' : '';
            }
            function healthChip(i) {
                return '<span class="wt-chip h-' + esc(i.health) + '" title="' + esc(i.message) + '">' + HEALTH_LABEL[i.health] + '</span>';
            }
            function avatar(i) {
                const p = programmerOf(i);
                if (!p) return '<span class="wt-sub">Belum ditugaskan</span>';
                return '<span class="d-inline-flex align-items-center gap-2"><span class="wt-avatar wt-mini-avatar" style="background:' + esc(p.color) + '">' + esc(initials(p.name)) + '</span>' + esc(p.name) + '</span>';
            }

            function renderList(items) {
                if (!items.length) {
                    $view.html('<div class="wt-empty"><i class="fa-regular fa-folder-open"></i>Tidak ada kerjaan untuk filter ini.</div>');
                    return;
                }
                const order = { telat: 0, mepet: 1, belum_dinilai: 2, aman: 3, selesai_telat: 4, selesai: 5, ditolak: 6 };
                const sorted = items.slice().sort(function (a, b) { return order[a.health] - order[b.health]; });
                const rows = sorted.map(function (i, n) {
                    const meta = [esc(i.code)];
                    if (i.module) meta.push('<span><i class="fa-solid fa-cube"></i>' + esc(i.module) + '</span>');
                    if (i.requester) meta.push('<span><i class="fa-regular fa-user"></i>' + esc(i.requester) + (i.requester_dept ? ' · ' + esc(i.requester_dept) : '') + '</span>');
                    if (i.points_total) meta.push('<span class="' + (i.points_done === i.points_total ? 'done' : '') + '" title="Poin selesai"><i class="fa-regular fa-square-check"></i>' + Number(i.points_done) + '/' + Number(i.points_total) + '</span>');
                    if (i.attachments_count) meta.push('<span title="Lampiran"><i class="fa-solid fa-paperclip"></i>' + Number(i.attachments_count) + '</span>');
                    return '<div class="wt-row b-' + esc(i.health) + '" data-id="' + i.id + '" style="animation-delay:' + Math.min(n, 12) * 25 + 'ms">' +
                        '<div class="wt-row-main"><div class="wt-row-chips">' + typeChip(i) + levelChip(i) + '</div>' +
                        '<div class="wt-title" title="' + esc(i.title) + '">' + esc(i.title) + '</div><div class="wt-row-meta">' + meta.join('') + '</div></div>' +
                        '<div class="wt-row-who">' + (['baru', 'dikerjakan'].includes(i.status) ? assignSelect(i, 'Belum ditugaskan') : avatar(i)) + '</div>' +
                        '<div class="wt-row-progress"><div class="d-flex align-items-center gap-2"><div class="wt-bar flex-grow-1"><span class="fill-' + esc(i.health) + '" style="width:' + Number(i.progress) + '%"></span></div><b>' + Number(i.progress) + '%</b></div>' +
                        '<div class="wt-sub">' + (i.pace ? num(i.pace) + '% per hari' : esc(STATUS_LABEL[i.status])) + '</div></div>' +
                        '<div class="wt-row-date"><div class="wt-sub">Target</div><b>' + fmt(i.target_date) + '</b>' + (i.predicted_date && i.predicted_date !== i.target_date ? '<div class="wt-sub">Prediksi ' + fmt(i.predicted_date) + '</div>' : '') + '</div>' +
                        '<div class="wt-row-health">' + healthChip(i) + '<div class="wt-sub">' + esc(i.message) + '</div></div></div>';
                }).join('');
                $view.html('<div class="wt-rows">' + rows + '</div>');
                initAssignSelects($view);
            }

            // Penanda jumlah poin selesai dan lampiran di kartu
            function cardExtras(i) {
                let html = '';
                if (i.points_total) html += ' · <span title="Poin selesai"><i class="fa-regular fa-square-check"></i> ' + Number(i.points_done) + '/' + Number(i.points_total) + '</span>';
                if (i.attachments_count) html += ' · <span title="Lampiran"><i class="fa-solid fa-paperclip"></i> ' + Number(i.attachments_count) + '</span>';
                return html;
            }

            function renderKanban(items) {
                const COLUMN_COLOR = { baru: '#8a94a6', dikerjakan: '#2f80ed', selesai: '#12a150', ditolak: '#dc3545' };
                const html = Object.keys(STATUS_LABEL).map(function (status) {
                    const own = items.filter(function (i) { return i.status === status; });
                    const cards = own.map(function (i) {
                        return '<div class="wt-kcard b-' + esc(i.health) + '" draggable="true" data-id="' + i.id + '">' +
                            '<div class="d-flex justify-content-between gap-1 mb-1">' + typeChip(i) + levelChip(i) + '</div>' +
                            '<div class="wt-title mb-1">' + esc(i.title) + '</div>' +
                            '<div class="wt-bar mb-1"><span class="fill-' + esc(i.health) + '" style="width:' + Number(i.progress) + '%"></span></div>' +
                            '<div class="d-flex justify-content-between align-items-center wt-sub"><span>' + Number(i.progress) + '% · ' + fmt(i.target_date) + cardExtras(i) + '</span>' + (programmerOf(i) ? '<span class="wt-avatar wt-mini-avatar" title="' + esc(programmerOf(i).name) + '" style="background:' + esc(programmerOf(i).color) + '">' + esc(initials(programmerOf(i).name)) + '</span>' : '') + '</div></div>';
                    }).join('');
                    return '<div class="wt-col" data-status="' + status + '" style="--col:' + COLUMN_COLOR[status] + '"><div class="wt-col-head"><span>' + STATUS_LABEL[status] + '</span><span class="text-muted">' + own.length + '</span></div>' + cards + '</div>';
                }).join('');
                $view.html('<div class="wt-kanban">' + html + '</div>');
            }

            function renderTimeline(items) {
                const LANE_HEIGHT = 28;
                const MAX_LANES = 3;
                const SIDE_WIDTH = 170;
                const today = state.board.today;
                const shiftIso = addDays;

                const doneOf = function (i) {
                    return i.status === 'selesai' && i.finished_at ? String(i.finished_at).slice(0, 10) : null;
                };
                const allBars = items.filter(function (i) { return i.start_date || i.target_date || i.predicted_date || doneOf(i); }).map(function (i) {
                    const doneAt = doneOf(i);
                    const start = i.start_date || i.target_date || i.predicted_date || doneAt;
                    // Kerjaan selesai digambar sampai tanggal benar-benar selesai
                    const planEnd = doneAt || i.target_date || i.predicted_date || start;
                    const end = planEnd < start ? start : planEnd;
                    const overrun = !doneAt && i.health === 'telat' && i.predicted_date && i.predicted_date > end ? i.predicted_date : null;
                    return { item: i, start: start, end: end, overrun: overrun, visualEnd: overrun || end };
                });

                let min, max;
                const fixedPeriod = !!(state.tlFrom && state.tlTo);
                if (fixedPeriod) {
                    min = state.tlFrom;
                    max = state.tlTo;
                } else {
                    // Rentang waktu diberi ruang kosong supaya batang bisa digeser melewati tanggal terakhir
                    min = shiftIso(today, -1);
                    max = shiftIso(today, 13);
                    allBars.forEach(function (b) {
                        if (b.start < min) min = b.start;
                        if (b.visualEnd > max) max = b.visualEnd;
                    });
                    max = shiftIso(max, 3);
                }
                state.tlRange = { from: min, to: max };
                const bars = allBars.filter(function (b) { return b.start <= max && b.visualEnd >= min; });
                const span = days(min, max) + 1;
                // Lebar per hari dikunci setelah Timeline diperluas lewat scroll supaya skalanya tidak berubah
                const fitW = ($view.width() - SIDE_WIDTH) / span;
                const dayW = fixedPeriod && state.tlZoom ? Math.max(state.tlZoom, fitW) : Math.max(span > 45 ? 20 : 38, fitW);
                const areaWidth = span * dayW;
                const rowStyle = 'grid-template-columns:' + SIDE_WIDTH + 'px ' + areaWidth + 'px;--day:' + dayW + 'px';
                const todayInRange = today >= min && today <= max;
                const todayLeft = days(min, today) * dayW;
                const todayMark = todayInRange ? '<div class="wt-tl-todaycol" style="left:' + todayLeft + 'px;width:' + dayW + 'px"></div>' : '';

                const step = Math.max(1, Math.ceil(56 / dayW));
                let ticks = '';
                for (let d = 0; d < span; d += step) {
                    ticks += '<span class="wt-tl-tick" style="left:' + (d * dayW) + 'px;width:' + dayW + 'px">' + fmt(shiftIso(min, d)) + '</span>';
                }

                const rowHtml = function (key, name, color, own) {
                    // Batang yang tanggalnya bentrok turun ke tingkat berikutnya
                    const laneEnds = [];
                    const placed = own.slice().sort(function (a, b) {
                        return a.start < b.start ? -1 : (a.start > b.start ? 1 : a.item.id - b.item.id);
                    }).map(function (b) {
                        let lane = laneEnds.findIndex(function (end) { return end < b.start; });
                        if (lane === -1) {
                            lane = laneEnds.length;
                            laneEnds.push(b.visualEnd);
                        } else {
                            laneEnds[lane] = b.visualEnd;
                        }
                        return { bar: b, lane: lane };
                    });
                    const expanded = !!state.timelineOpen[key];
                    const hidden = expanded ? 0 : placed.filter(function (p) { return p.lane >= MAX_LANES; }).length;
                    const shown = placed.filter(function (p) { return expanded || p.lane < MAX_LANES; });
                    const lanes = Math.max(1, laneEnds.length === 0 ? 1 : (expanded ? laneEnds.length : Math.min(MAX_LANES, laneEnds.length)));

                    const barsHtml = shown.map(function (p) {
                        const b = p.bar;
                        const i = b.item;
                        const top = 4 + p.lane * LANE_HEIGHT;
                        // Batang yang melewati batas periode dipotong di tepi periode
                        const clipLeft = b.start < min;
                        const clipRight = b.end > max;
                        const from = clipLeft ? min : b.start;
                        const to = clipRight ? max : b.end;
                        const left = days(min, from) * dayW + 1;
                        const width = (days(from, to) + 1) * dayW - 2;
                        const canDrag = ['baru', 'dikerjakan'].includes(i.status);
                        const owner = programmerOf(i);
                        const doneAt = doneOf(i);
                        const tip = i.code + ' ' + i.title + ' | ' + fmt(b.start, true) + ' sampai ' + fmt(b.end, true) + (doneAt ? ' (selesai)' : '') + ' | ' + i.progress + '% | ' + HEALTH_LABEL[i.health];
                        let over = '';
                        if (b.overrun) {
                            const overFrom = shiftIso(b.end, 1) < min ? min : shiftIso(b.end, 1);
                            const overTo = b.overrun > max ? max : b.overrun;
                            if (overFrom <= overTo) {
                                over = '<div class="wt-tl-over" style="top:' + top + 'px;left:' + (days(min, overFrom) * dayW - 1) + 'px;width:' + ((days(overFrom, overTo) + 1) * dayW) + 'px"></div>';
                            }
                        }
                        // Batang yang seluruhnya sebelum periode (hanya sisa keterlambatannya terlihat) cukup digambar sisanya
                        if (b.end < min) return over;
                        return over + '<button type="button" class="wt-tl-bar tl-' + esc(i.health) + (canDrag ? ' can-drag' : '') + (clipLeft ? ' clip-l' : '') + (clipRight ? ' clip-r' : '') + '" data-id="' + i.id + '" data-start="' + b.start + '" data-end="' + b.end + '" data-owner="' + (owner ? owner.id : '') + '" title="' + esc(tip) + '" style="top:' + top + 'px;left:' + left + 'px;width:' + width + 'px">' +
                            '<span class="wt-tl-fill" style="width:' + Number(i.progress) + '%"></span>' +
                            '<span class="wt-tl-text">' + (owner ? '<span class="wt-avatar wt-tl-avatar" style="background:' + esc(owner.color) + '">' + esc(initials(owner.name)) + '</span>' : '') + (i.urgent ? '<i class="fa-solid fa-road-barrier"></i> ' : '') + esc(i.title) + '</span>' +
                            (canDrag && !clipRight ? '<span class="wt-tl-handle" title="Tarik untuk mengubah target"></span>' : '') + '</button>';
                    }).join('');

                    const moreButton = hidden > 0 ? '<button type="button" class="wt-tl-more" data-row="' + esc(key) + '">+' + hidden + ' lainnya</button>' : (expanded ? '<button type="button" class="wt-tl-more" data-row="' + esc(key) + '">Ringkas</button>' : '');
                    return '<div class="wt-tl-row" data-row-key="' + esc(key) + '" data-programmer-id="' + (key === 'none' ? '' : esc(key)) + '" data-name="' + esc(name) + '" style="' + rowStyle + '">' +
                        '<div class="wt-tl-side"><div class="wt-tl-person"><span class="wt-avatar wt-mini-avatar" style="background:' + esc(color) + '">' + esc(initials(name)) + '</span><b title="' + esc(name) + '">' + esc(name) + '</b></div>' + moreButton + '</div>' +
                        '<div class="wt-tl-area" style="height:' + (8 + lanes * LANE_HEIGHT) + 'px">' + todayMark + barsHtml + '</div></div>';
                };

                const programmerFilter = $('#wt-f-programmer').val();
                const barsOf = function (id) {
                    return bars.filter(function (b) { return (b.item.programmer_id === null ? null : Number(b.item.programmer_id)) === id; });
                };
                const body = memberDivisions().filter(function (d) {
                    return divisionShown(d) && programmerFilter !== 'none';
                }).map(function (d) {
                    const members = state.board.programmers.filter(function (p) {
                        return p.division === d && (!programmerFilter || Number(programmerFilter) === p.id);
                    });
                    if (!members.length) return '';
                    return '<div class="wt-tl-division">' + esc(d) + '</div>' + members.map(function (p) {
                        return rowHtml(String(p.id), p.name, p.color, barsOf(p.id));
                    }).join('');
                }).join('');
                const unassigned = barsOf(null);
                const showUnassigned = !state.divisions.length &&(!programmerFilter || programmerFilter === 'none') && (unassigned.length || state.board.programmers.length);
                const bottom = showUnassigned ? '<div class="wt-tl-division">Belum ditugaskan</div>' + rowHtml('none', 'Belum ditugaskan', '#9aa6b8', unassigned) : '';

                if (!body && !bottom) {
                    $view.html('<div class="wt-empty"><i class="fa-solid fa-chart-gantt"></i>Belum ada kerjaan dengan tanggal.</div>');
                    return;
                }

                // Petunjuk saat melihat periode lampau tetapi filter menyembunyikan kerjaan yang sudah lewat
                const status = $('#wt-f-status').val();
                const hints = [];
                if (fixedPeriod && min < today && status !== '' && status !== 'selesai') {
                    hints.push('Kerjaan yang sudah selesai tidak tampil karena filter Status. Ubah Status ke <b>Semua status</b> atau <b>Selesai</b> untuk melihatnya.');
                }
                if (fixedPeriod && min < shiftIso(today, -{{ \App\Http\Controllers\General\WorkTrackerController::ARCHIVE_AFTER_DAYS }}) && !$('#wt-archive').is(':checked')) {
                    hints.push('Periode ini lebih dari {{ \App\Http\Controllers\General\WorkTrackerController::ARCHIVE_AFTER_DAYS }} hari lalu. Nyalakan <b>Tampilkan arsip</b> supaya kerjaan lama ikut tampil.');
                }
                if (!bars.length) hints.unshift('Tidak ada kerjaan pada periode ini.');

                const nav = '<div class="wt-tl-nav">' +
                    '<div class="btn-group btn-group-sm">' +
                    '<button type="button" class="btn btn-outline-secondary" data-tl-nav="-1" title="Periode sebelumnya"><i class="fa-solid fa-chevron-left"></i></button>' +
                    '<button type="button" class="btn btn-outline-secondary' + (todayInRange ? ' active' : '') + '" data-tl-nav="0" title="Kembali ke periode yang memuat hari ini">Hari ini</button>' +
                    '<button type="button" class="btn btn-outline-secondary" data-tl-nav="1" title="Periode berikutnya"><i class="fa-solid fa-chevron-right"></i></button>' +
                    '</div>' +
                    '<div class="btn-group btn-group-sm" role="group" aria-label="Lebar rentang">' +
                    [['auto', 'Otomatis'], ['2w', '2 Minggu'], ['1m', '1 Bulan'], ['3m', '3 Bulan']].map(function (m) {
                        return '<button type="button" class="btn btn-outline-primary' + (state.tlMode === m[0] ? ' active' : '') + '" data-tl-mode="' + m[0] + '">' + m[1] + '</button>';
                    }).join('') +
                    '</div>' +
                    '<label>Dari <input type="date" class="form-control form-control-sm" id="wt-tl-from" value="' + min + '"></label>' +
                    '<label>s/d <input type="date" class="form-control form-control-sm" id="wt-tl-to" value="' + max + '"></label>' +
                    '<span class="wt-sub">' + span + ' hari</span>' +
                    '</div>' +
                    (hints.length ? '<div class="wt-tl-hint"><i class="fa-solid fa-circle-info"></i> ' + hints.join(' ') + '</div>' : '');

                $view.html(nav + '<div class="wt-sub mb-2"><i class="fa-solid fa-hand-pointer"></i> Klik batang untuk membuka detail. Geser untuk memindah jadwal atau anggota. Tarik garis di ujung kanan untuk mengubah target. Geser tanggal: Shift + scroll, atau tahan klik/klik tengah di area kosong lalu tarik.</div>' +
                    '<div class="wt-tl-scroll"><div class="wt-tl" data-day-width="' + dayW + '">' +
                    '<div class="wt-tl-row wt-tl-head" style="' + rowStyle + '"><div class="wt-tl-side"></div><div class="wt-tl-area">' + todayMark +
                    (todayInRange ? '<span class="wt-tl-todaylabel" style="left:' + (todayLeft + 4) + 'px">Hari ini</span>' : '') + ticks + '</div></div>' + body + bottom + '</div></div>' +
                    '<div class="wt-tl-tip" id="wt-tl-tip"></div>');

                // Posisi geser disimpan sebagai tanggal paling kiri yang terlihat, jadi tetap di tanggal yang sama
                // walau Timeline digambar ulang (ganti divisi, geser batang, rentang bertambah)
                const scroller = $view.find('.wt-tl-scroll')[0];
                if (state.tlScroll) scroller.scrollLeft = (days(min, state.tlScroll.date) + state.tlScroll.days) * dayW;
                state.tlScroll = { date: min, days: scroller.scrollLeft / dayW };
                scroller.addEventListener('scroll', function () {
                    state.tlScroll = { date: min, days: scroller.scrollLeft / dayW };
                    if (!state.drag && !state.tlPan && !state.tlExtending) {
                        const edge = dayW * 2;
                        if (scroller.scrollLeft <= edge && scroller.dataset.lastLeft > scroller.scrollLeft) timelineExtend(-1);
                        else if (scroller.scrollLeft + scroller.clientWidth >= scroller.scrollWidth - edge && scroller.dataset.lastLeft < scroller.scrollLeft) timelineExtend(1);
                    }
                    scroller.dataset.lastLeft = scroller.scrollLeft;
                }, { passive: true });
                scroller.dataset.lastLeft = scroller.scrollLeft;
            }

            // Seperti Notion: saat digeser sampai ujung, Timeline bertambah 2 minggu ke arah itu
            function timelineExtend(dir) {
                const range = state.tlRange;
                const scroller = $view.find('.wt-tl-scroll')[0];
                if (!range || !scroller || state.tlExtending) return;
                const dayW = Number($view.find('.wt-tl').data('dayWidth'));
                const add = 14;
                if (days(range.from, range.to) + 1 + add > 731) return;
                const from = dir < 0 ? addDays(range.from, -add) : range.from;
                const to = dir < 0 ? range.to : addDays(range.to, add);
                state.tlExtending = true;
                state.tlZoom = dayW;
                state.tlMode = 'custom';
                state.tlFrom = from;
                state.tlTo = to;
                // Tanggal yang sedang dilihat tetap di tempat setelah hari baru ditambahkan di kiri
                state.tlScroll = { date: range.from, days: scroller.scrollLeft / dayW };
                renderView();
                state.tlExtending = false;
                return dir < 0 ? add * dayW : 0;
            }

            function startBarDrag(e) {
                const native = e.originalEvent;
                if (native.button !== 0) return;
                const bar = e.currentTarget;
                try {
                    bar.setPointerCapture(native.pointerId);
                } catch (error) {
                    // Sebagian browser menolak capture, geser tetap jalan selama pointer di atas batang
                }
                state.drag = {
                    bar: bar,
                    id: Number(bar.dataset.id),
                    mode: $(native.target).closest('.wt-tl-handle').length ? 'resize' : 'move',
                    x: native.clientX,
                    y: native.clientY,
                    left: parseFloat(bar.style.left),
                    width: parseFloat(bar.style.width),
                    dayW: Number($(bar).closest('.wt-tl').data('dayWidth')),
                    originRow: bar.closest('.wt-tl-row'),
                    targetRow: bar.closest('.wt-tl-row'),
                    delta: 0,
                    moved: false,
                };
            }

            function dragDates(drag) {
                const item = state.board.items.find(function (i) { return i.id === drag.id; });
                const shift = function (iso) {
                    const date = parseDate(iso);
                    date.setDate(date.getDate() + drag.delta);
                    return date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0');
                };
                if (drag.mode === 'resize') {
                    return { start: item.start_date, target: shift(drag.bar.dataset.end), shownStart: drag.bar.dataset.start, shownEnd: shift(drag.bar.dataset.end) };
                }
                // Tanggal yang masih kosong tetap kosong, kecuali kerjaan belum punya tanggal sama sekali
                const start = item.start_date || !item.target_date ? shift(drag.bar.dataset.start) : null;
                const target = item.target_date ? shift(item.target_date) : null;
                return { start: start, target: target, shownStart: shift(drag.bar.dataset.start), shownEnd: shift(drag.bar.dataset.end) };
            }

            function moveBarDrag(e) {
                const drag = state.drag;
                if (!drag) return;
                const native = e.originalEvent;
                const dx = native.clientX - drag.x;
                const dy = native.clientY - drag.y;
                if (!drag.moved && Math.abs(dx) + Math.abs(dy) < 5) return;
                drag.moved = true;
                drag.bar.classList.add('dragging');
                drag.delta = Math.round(dx / drag.dayW);

                if (drag.mode === 'resize') {
                    const minDelta = 1 - Math.round((drag.width + 2) / drag.dayW);
                    drag.delta = Math.max(minDelta, drag.delta);
                    drag.bar.style.width = (drag.width + drag.delta * drag.dayW) + 'px';
                } else {
                    drag.bar.style.transform = 'translate(' + (drag.delta * drag.dayW) + 'px,' + dy + 'px)';
                    const under = document.elementsFromPoint(native.clientX, native.clientY).find(function (el) {
                        return el.classList.contains('wt-tl-row') && el.dataset.rowKey;
                    });
                    if (under && under !== drag.targetRow) {
                        drag.targetRow = under;
                    }
                    $('.wt-tl-row').removeClass('drop-target');
                    if (drag.targetRow !== drag.originRow) drag.targetRow.classList.add('drop-target');
                }

                const dates = dragDates(drag);
                const person = drag.targetRow !== drag.originRow ? ' ke ' + drag.targetRow.dataset.name : '';
                $('#wt-tl-tip').text(fmt(dates.shownStart) + ' sampai ' + fmt(dates.shownEnd) + person).css({ display: 'block', left: native.clientX + 14, top: native.clientY + 14 });
            }

            function endBarDrag() {
                const drag = state.drag;
                if (!drag) return;
                state.drag = null;
                $('#wt-tl-tip').hide();
                if (!drag.moved) return;

                // Klik yang menyusul setelah menggeser tidak boleh membuka detail
                state.justDragged = true;
                setTimeout(function () { state.justDragged = false; }, 50);

                const changedPerson = drag.targetRow !== drag.originRow;
                if (drag.delta === 0 && !changedPerson) {
                    renderView();
                    return;
                }
                if (guardDemo()) {
                    renderView();
                    return;
                }

                const dates = dragDates(drag);
                const payload = { id: drag.id, start_date: dates.start || '', target_date: dates.target || '' };
                if (changedPerson) payload.programmer_id = drag.targetRow.dataset.programmerId;
                const request = post(ROUTES.reschedule, payload, function (res) { toast(res.message); });
                if (request) request.always(load);
            }

            function fillProgrammerSelects() {
                const programmers = state.board.programmers;
                const filterValue = $('#wt-f-programmer').val();
                const options = programmerOptions(function (p) { return '<option value="' + p.id + '">' + esc(p.name) + '</option>'; });
                const divisionValue = $('#wt-f-division').val() || [];
                $('#wt-f-division').html(memberDivisions().map(function (d) { return '<option value="' + esc(d) + '">' + esc(d) + '</option>'; }).join('')).val(divisionValue).trigger('change.select2');
                state.divisions = $('#wt-f-division').val() || [];
                $('#wt-f-programmer').html('<option value="">Semua anggota</option><option value="none">Belum ditugaskan</option>' + options).val(filterValue || '').trigger('change.select2');
                $('#wt-i-programmer').html('<option value="">Belum ditugaskan</option>' + options).trigger('change.select2');
            }

            // Jumlah hari dari mulai sampai target, termasuk kedua tanggalnya
            function rangeDays() {
                const form = document.getElementById('wt-item-form');
                const start = form.elements.start_date.value;
                const target = form.elements.target_date.value;
                return start && target && target >= start ? days(start, target) + 1 : null;
            }

            function fillEstimate() {
                if (state.estimateManual) return;
                const range = rangeDays();
                document.getElementById('wt-item-form').elements.estimate_days.value = range === null ? '' : range;
            }

            function openItemForm(item) {
                const form = document.getElementById('wt-item-form');
                form.reset();
                form.elements.id.value = item ? item.id : '';
                ['title', 'module', 'requester', 'requester_dept', 'reject_reason', 'start_date', 'target_date', 'estimate_days', 'description'].forEach(function (f) {
                    form.elements[f].value = item && item[f] !== null && item[f] !== undefined ? item[f] : '';
                });
                $('#wt-i-type').val(item ? item.type : 'plan').trigger('change');
                $('#wt-i-programmer').val(item && item.programmer_id ? item.programmer_id : (!item && state.selected ? state.selected : '')).trigger('change');
                $('#wt-i-level').val(item && item.level ? item.level : '').trigger('change');
                state.estimateManual = !!item && item.estimate_days !== null && Number(item.estimate_days) !== rangeDays();
                $('#wt-item-title').text(item ? 'Edit ' + item.code : 'Tambah Kerjaan');
                $('#wt-item-delete').toggleClass('d-none', !item);
                $('#wt-i-files-label').text(item ? 'Tambah lampiran' : 'Lampiran');
                // Kerjaan baru langsung mulai hari ini
                if (!item) form.elements.start_date.value = isoOf(new Date());
                clearPending(state.formFiles);
                $('#wt-i-files').empty();

                // Poin kerjaan lama diambil dulu. Selama belum termuat, form tidak mengirim poin supaya poin lama tidak ikut terhapus.
                state.formItemId = item ? item.id : null;
                state.formPoints = [];
                state.formPointsReady = !item || state.demo;
                $('#wt-i-point-input').val('');
                if (state.formPointsReady) {
                    renderFormPoints();
                } else {
                    pointsProgress('#wt-i-points', []);
                    $('#wt-i-points').html('<div class="wt-todo-empty">Memuat poin...</div>');
                    $.get(ROUTES.detail + '/' + item.id).done(function (detail) {
                        if (state.formItemId !== item.id) return;
                        state.formPoints = detail.points.map(function (p) { return { id: p.id, content: p.content, done: !!Number(p.is_done) }; });
                        state.formPointsReady = true;
                        renderFormPoints();
                    }).fail(failed);
                }
                bootstrap.Modal.getOrCreateInstance(document.getElementById('wt-item-modal')).show();
            }

            // Angka "x/y selesai" dan bar tipis di atas daftar poin. prefix: #wt-i-points (form) atau #wt-points (detail)
            function pointsProgress(prefix, points) {
                const done = points.filter(function (p) { return p.done; }).length;
                const allDone = points.length > 0 && done === points.length;
                $(prefix + '-count').html(points.length ? (allDone ? '<i class="fa-solid fa-circle-check"></i> Semua beres' : done + '/' + points.length + ' selesai') : '').toggleClass('all-done', allDone);
                $(prefix + '-bar').toggleClass('d-none', !points.length).children('span').css('width', (points.length ? done / points.length * 100 : 0) + '%');
            }

            function actionBtn(cls, icon, label) {
                return '<button type="button" class="btn btn-sm btn-link ' + cls + '" title="' + label + '" aria-label="' + label + '"><i class="fa-solid ' + icon + '"></i></button>';
            }

            // Satu baris poin: lingkaran centang + teks. below = keterangan/lampiran di bawah teks.
            function pointRow(p, attr, actions, below) {
                return '<div class="wt-point' + (p.done ? ' done' : '') + '" ' + attr + '>' +
                    '<div class="wt-point-body"><button type="button" class="wt-point-toggle" aria-pressed="' + p.done + '" title="' + (p.done ? 'Klik untuk membatalkan' : 'Klik kalau poin ini sudah beres') + '">' +
                    '<span class="wt-check"><i class="fa-solid fa-check"></i></span><span class="wt-point-text">' + esc(p.content) + '</span></button>' + (below || '') + '</div>' +
                    '<div class="wt-point-actions">' + actions + '</div></div>';
            }

            // Centang langsung di layar tanpa menunggu server, lingkarannya memantul saat dicentang
            function setPointDone($point, done) {
                $point.toggleClass('done', done);
                $point.find('.wt-point-toggle').attr({ 'aria-pressed': done, title: done ? 'Klik untuk membatalkan' : 'Klik kalau poin ini sudah beres' });
                const check = $point.find('.wt-check')[0];
                check.classList.remove('pop');
                if (done) {
                    void check.offsetWidth;
                    check.classList.add('pop');
                }
            }

            function renderFormPoints() {
                $('#wt-i-points').html(state.formPoints.map(function (p, index) {
                    return pointRow(p, 'data-index="' + index + '"', actionBtn('wt-fp-edit', 'fa-pen', 'Ubah poin') + actionBtn('wt-fp-del wt-del-btn', 'fa-xmark', 'Buang poin'));
                }).join(''));
                pointsProgress('#wt-i-points', state.formPoints);
            }

            // Ubah poin langsung di barisnya. Enter atau klik di luar menyimpan, Esc membatalkan. save(null) berarti tidak ada perubahan.
            function inlineEditPoint($point, current, save) {
                const $input = $('<input class="form-control form-control-sm" maxlength="500" aria-label="Ubah poin">').val(current);
                $point.find('.wt-point-toggle').replaceWith($input);
                $point.find('.wt-point-actions').addClass('invisible');
                $input.trigger('focus').trigger('select');
                let finished = false;
                const finish = function (commit) {
                    if (finished) return;
                    finished = true;
                    const value = $input.val().trim();
                    save(commit && value && value !== current ? value : null);
                };
                $input.on('keydown', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        finish(true);
                    } else if (e.key === 'Escape') {
                        // Jangan sampai Esc ikut menutup modal
                        e.preventDefault();
                        e.stopPropagation();
                        finish(false);
                    }
                }).on('blur', function () { finish(true); });
            }

            function addFormPoint() {
                const content = $('#wt-i-point-input').val().trim();
                if (!content || !state.formPointsReady) return false;
                state.formPoints.push({ id: null, content: content, done: false });
                $('#wt-i-point-input').val('').trigger('focus');
                renderFormPoints();
                return true;
            }

            // Berkas yang sudah dipilih tapi baru diunggah saat Simpan (form kerjaan dan form progress)
            function addPending(list, files) {
                let skipped = 0;
                Array.prototype.forEach.call(files, function (file) {
                    if (file.size > FILE_MAX_BYTES || list.length >= FILE_LIMIT) {
                        skipped++;
                        return;
                    }
                    if (/^image\//.test(file.type)) file.previewUrl = URL.createObjectURL(file);
                    list.push(file);
                });
                if (skipped) toast(skipped + ' file dilewati: maksimal ' + FILE_LIMIT + ' file, 10 MB per file', 'warning');
            }

            function removePending(list, index) {
                const file = list.splice(index, 1)[0];
                if (file && file.previewUrl) URL.revokeObjectURL(file.previewUrl);
            }

            function clearPending(list) {
                while (list.length) removePending(list, 0);
            }

            function pendingHtml(list) {
                if (!list.length) return '';
                return '<div class="wt-files">' + list.map(function (f, n) {
                    const body = f.previewUrl ? '<img src="' + f.previewUrl + '" alt="' + esc(f.name) + '">' : '<i class="fa-solid ' + fileIcon(f.name) + '"></i><span>' + esc(f.name) + '</span>';
                    return '<span class="wt-file pending' + (f.previewUrl ? ' is-image' : '') + '"><span class="wt-file-box" title="' + esc(f.name) + '">' + body + '</span>' +
                        '<button type="button" class="wt-file-del" data-pending="' + n + '" title="Batal lampirkan" aria-label="Batal lampirkan ' + esc(f.name) + '">&times;</button></span>';
                }).join('') + '</div>';
            }

            function hasFiles(e) {
                const dt = e.originalEvent && e.originalEvent.dataTransfer;
                return !!dt && Array.prototype.indexOf.call(dt.types || [], 'Files') >= 0;
            }

            function bindDrop($zone, onFiles) {
                $zone.on('dragenter dragover', function (e) {
                    e.preventDefault();
                    $zone.addClass('over');
                }).on('dragleave drop', function (e) {
                    e.preventDefault();
                    $zone.removeClass('over');
                }).on('drop', function (e) {
                    const files = e.originalEvent.dataTransfer.files;
                    if (files.length) onFiles(files);
                });
                $zone.find('input[type=file]').on('change', function () {
                    if (this.files.length) onFiles(this.files);
                    this.value = '';
                });
            }

            // Pratinjau foto di atas modal. Esc hanya menutup pratinjau, modalnya tetap terbuka.
            function openLightbox(url, name) {
                const $host = $('.modal.show').last();
                const $box = $('<div class="wt-lightbox" tabindex="-1" role="dialog" aria-modal="true" aria-label="Pratinjau foto">' +
                    '<img src="' + esc(url) + '" alt="' + esc(name) + '">' +
                    '<div class="wt-lightbox-bar"><span>' + esc(name) + '</span>' +
                    '<a href="' + esc(url) + '" target="_blank" rel="noopener"><i class="fa-solid fa-up-right-from-square"></i> Buka di tab baru</a>' +
                    '<button type="button"><i class="fa-solid fa-xmark"></i> Tutup</button></div></div>');
                const close = function () { $box.remove(); };
                $box.on('click', function (e) {
                    if (!$(e.target).closest('a').length) close();
                }).on('keydown', function (e) {
                    if (e.key !== 'Escape') return;
                    e.preventDefault();
                    e.stopPropagation();
                    close();
                });
                ($host.length ? $host : $('body')).append($box);
                $box.trigger('focus');
            }

            function openDetail(id) {
                const byId = function (i) { return i.id === id; };
                const item = state.board.items.find(byId) || state.reportItems.find(byId);
                if (!item) return;
                state.current = item;
                $('#wt-d-code').text(item.code);
                $('#wt-d-title').text(item.title);
                const pairs = [
                    ['Anggota', programmerOf(item) ? programmerOf(item).name : 'Belum ditugaskan'], ['Mulai', fmt(item.start_date, true)], ['Target', fmt(item.target_date, true)],
                    ['Prediksi', fmt(item.predicted_date, true)], ['Pace', item.pace ? num(item.pace) + '% per hari' : '-'], ['Sisa beban', item.effort_left !== null ? num(item.effort_left) + ' hari' : '-'],
                ];
                let info = '<div class="d-flex flex-wrap gap-2 mb-2">' + typeChip(item) + levelChip(item) + healthChip(item) + '<span class="wt-sub align-self-center">' + esc(item.message) + '</span></div>' +
                    '<div class="row g-2">' + pairs.map(function (p) { return '<div class="col-6 col-md-4"><div class="wt-sub">' + p[0] + '</div><div class="fw-bold small">' + esc(p[1]) + '</div></div>'; }).join('') + '</div>';
                if (item.requester) info += '<div class="wt-sub mt-2">Dari ' + esc(item.requester) + (item.requester_dept ? ' (' + esc(item.requester_dept) + ')' : '') + '</div>';
                if (item.description) info += '<div class="small mt-2" style="white-space:pre-wrap">' + esc(item.description) + '</div>';
                if (item.reject_reason) info += '<div class="small mt-2 text-danger">Alasan: ' + esc(item.reject_reason) + '</div>';
                $('#wt-d-info').html(info);

                const rejected = item.status === 'ditolak';
                $('#wt-range').val(item.progress).prop('disabled', rejected);
                $('#wt-range-value').text(item.progress);
                $('#wt-note').val('');
                clearPending(state.progressFiles);
                $('#wt-progress-files').empty();
                $('#wt-progress-drop').toggleClass('d-none', rejected);
                $('#wt-progress-form button[type=submit]').prop('disabled', rejected);
                $('#wt-point-input').val('');
                $('#wt-point-form :input').prop('disabled', state.demo);
                $('#wt-d-drop').toggleClass('d-none', state.demo);
                pointsProgress('#wt-points', []);

                state.detail = null;
                if (state.demo) {
                    $('#wt-points').html('<div class="wt-todo-empty">Poin tampil di data asli.</div>');
                    $('#wt-item-files').html('<div class="wt-sub">Lampiran tampil di data asli.</div>');
                    $('#wt-logs').html('<div class="wt-sub">Riwayat tampil di data asli.</div>');
                } else {
                    $('#wt-points').html('<div class="wt-todo-empty">Memuat...</div>');
                    $('#wt-item-files, #wt-logs').html('<div class="wt-sub">Memuat...</div>');
                    loadDetail(id);
                }

                bootstrap.Modal.getOrCreateInstance(document.getElementById('wt-detail-modal')).show();
            }

            function loadDetail(id) {
                return $.get(ROUTES.detail + '/' + id).done(function (detail) {
                    if (!state.current || state.current.id !== id) return;
                    state.detail = detail;
                    renderDetail();
                }).fail(failed);
            }

            // Setelah poin atau lampiran berubah: isi modal dimuat ulang, papan juga supaya penanda di kartu ikut berubah
            function refreshDetail() {
                if (!state.current) return;
                loadDetail(state.current.id);
                load();
            }

            function uploadAttachments(files, pointId) {
                if (!files.length || !state.current || guardDemo()) return;
                if (files.length > FILE_LIMIT) {
                    toast('Maksimal ' + FILE_LIMIT + ' file sekali unggah', 'warning');
                    return;
                }
                const data = new FormData();
                data.append('work_item_id', state.current.id);
                if (pointId) data.append('point_id', pointId);
                Array.prototype.forEach.call(files, function (file) { data.append('files[]', file); });
                toast('Mengunggah ' + files.length + ' file...', 'info');
                postForm(ROUTES.attachmentStore, data, function (res) {
                    toast(res.message);
                    refreshDetail();
                });
            }

            function fileIcon(name) {
                const ext = String(name || '').split('.').pop().toLowerCase();
                if (ext === 'pdf') return 'fa-file-pdf';
                if (['xls', 'xlsx', 'csv'].indexOf(ext) >= 0) return 'fa-file-excel';
                if (['doc', 'docx'].indexOf(ext) >= 0) return 'fa-file-word';
                if (ext === 'zip') return 'fa-file-zipper';
                if (ext === 'html' || ext === 'htm') return 'fa-file-code';
                return 'fa-file-lines';
            }

            function filesHtml(list) {
                if (!list.length) return '';
                return '<div class="wt-files">' + list.map(function (a) {
                    const body = a.is_image
                        ? '<img src="' + esc(a.url) + '" alt="' + esc(a.original_name) + '" loading="lazy">'
                        : '<i class="fa-solid ' + fileIcon(a.original_name) + '"></i><span>' + esc(a.original_name) + '</span>';
                    // File HTML hanya diunduh, tidak dibuka di tab
                    const link = a.download_only
                        ? ' download title="Unduh ' + esc(a.original_name) + '"'
                        : ' target="_blank" rel="noopener" title="' + esc(a.original_name) + '"' + (a.is_image ? ' data-lightbox' : '');
                    const tail = a.download_only ? ' <i class="fa-solid fa-download" style="font-size:11px;color:var(--wt-muted)"></i>' : '';
                    return '<span class="wt-file' + (a.is_image ? ' is-image' : '') + '"><a class="wt-file-box" href="' + esc(a.url) + '"' + link + '>' + body + tail + '</a>' +
                        '<button type="button" class="wt-file-del" data-attachment="' + a.id + '" title="Hapus lampiran" aria-label="Hapus lampiran ' + esc(a.original_name) + '">&times;</button></span>';
                }).join('') + '</div>';
            }

            function renderDetail() {
                const d = state.detail;
                const filesOf = function (key, id) { return d.attachments.filter(function (a) { return Number(a[key]) === Number(id); }); };
                const points = d.points.map(function (p) { return { id: p.id, content: p.content, done: !!Number(p.is_done), done_at: p.done_at, done_by: p.done_by }; });

                pointsProgress('#wt-points', points);
                $('#wt-points').html(points.length ? points.map(function (p) {
                    const below = (p.done && p.done_by ? '<div class="wt-sub">Selesai ' + esc((p.done_at || '').slice(0, 16)) + ' · ' + esc(p.done_by) + '</div>' : '') + filesHtml(filesOf('point_id', p.id));
                    const actions = actionBtn('wt-point-attach', 'fa-paperclip', 'Lampirkan foto/file ke poin ini') + actionBtn('wt-point-edit', 'fa-pen', 'Ubah poin') + actionBtn('wt-point-del wt-del-btn', 'fa-trash', 'Hapus poin');
                    return pointRow(p, 'data-point="' + p.id + '"', actions, below);
                }).join('') : '<div class="wt-todo-empty">Belum ada poin. Pecah kerjaan jadi langkah kecil supaya gampang dipantau.</div>');

                const itemFiles = d.attachments.filter(function (a) { return !a.point_id && !a.log_id; });
                $('#wt-item-files').html(filesHtml(itemFiles));

                $('#wt-logs').html(d.logs.length ? d.logs.map(function (l) {
                    const change = l.progress_before !== null && l.progress_before !== l.progress_after ? l.progress_before + '% → ' + l.progress_after + '%' : (STATUS_LABEL[l.status] || '');
                    return '<div class="wt-log"><b>' + esc(change) + '</b> <span class="wt-sub">' + esc((l.created_at || '').slice(0, 16)) + ' · ' + esc(l.created_by) + '</span>' +
                        (l.note ? '<div style="white-space:pre-wrap">' + esc(l.note) + '</div>' : '') + filesHtml(filesOf('log_id', l.id)) + '</div>';
                }).join('') : '<div class="wt-sub">Belum ada riwayat.</div>');
            }

            function celebrate(item) {
                const colors = ['#2f80ed', '#12a150', '#f2c94c', '#eb5757', '#9b51e0'];
                for (let n = 0; n < 110; n++) {
                    const piece = document.createElement('div');
                    piece.className = 'wt-confetti';
                    piece.style.left = Math.random() * 100 + 'vw';
                    piece.style.background = colors[n % colors.length];
                    piece.style.setProperty('--dur', (1.6 + Math.random() * 1.6) + 's');
                    piece.style.setProperty('--drift', (Math.random() * 160 - 80) + 'px');
                    piece.style.animationDelay = Math.random() * .4 + 's';
                    document.body.appendChild(piece);
                    setTimeout(function () { piece.remove(); }, 3800);
                }

                const owner = programmerOf(item);
                const who = owner ? '<div class="wt-party-who"><span class="wt-avatar wt-mini-avatar" style="background:' + esc(owner.color) + '">' + esc(initials(owner.name)) + '</span>' + esc(owner.name) + '</div>' : '';
                const $party = $('<div class="wt-party" role="status"><div class="wt-party-card"><div class="wt-party-trophy"><i class="fa-solid fa-trophy"></i></div>' +
                    '<div class="wt-party-head">Mantap, beres!</div><div class="wt-party-title">' + esc(item.title) + '</div>' + who + '</div></div>');
                const close = function () {
                    clearTimeout($party.data('timer'));
                    $(document).off('keydown.wtParty');
                    $party.addClass('out');
                    setTimeout(function () { $party.remove(); }, 300);
                };
                $party.on('click', close).data('timer', setTimeout(close, 3000));
                $(document).on('keydown.wtParty', function (e) { if (e.key === 'Escape') close(); });
                $('body').append($party);
            }

            function moveStatus(id, status) {
                const item = state.board.items.find(function (i) { return i.id === id; });
                if (!item || item.status === status) return;
                if (state.demo && status === 'selesai') celebrate(item);
                if (guardDemo()) return;
                const send = function (reason) {
                    post(ROUTES.status, { id: id, status: status, reason: reason }, function (res) {
                        if (res.just_finished) celebrate(item);
                        load();
                    });
                };
                if (status !== 'ditolak') {
                    send();
                    return;
                }
                Swal.fire({
                    title: 'Alasan tidak dikerjakan', input: 'text', inputAttributes: { maxlength: 500 }, showCancelButton: true,
                    confirmButtonText: 'Simpan', cancelButtonText: 'Batal', confirmButtonColor: '#082149',
                    inputValidator: function (value) { return value ? null : 'Alasan wajib diisi'; },
                }).then(function (r) { if (r.isConfirmed) send(r.value); });
            }

            function openProgrammers() {
                renderProgrammerList();
                $('#wt-p-colors').html(COLORS.map(function (c) {
                    return '<button type="button" data-color="' + c + '" class="' + (c === state.color ? 'active' : '') + '" style="background:' + c + '" aria-label="Warna ' + c + '"></button>';
                }).join(''));
                loadUsers();
                bootstrap.Modal.getOrCreateInstance(document.getElementById('wt-programmer-modal')).show();
            }

            function divisionOptions(selected) {
                return state.board.divisions.map(function (d) {
                    return '<option value="' + esc(d) + '"' + (d === selected ? ' selected' : '') + '>' + esc(d) + '</option>';
                }).join('');
            }

            function initDivisionSelect($select) {
                $select.select2({ theme: 'bootstrap4', width: '100%', tags: true, dropdownParent: $('#wt-programmer-modal') });
            }

            function renderProgrammerList() {
                $('#wt-programmer-list .wt-member-select.select2-hidden-accessible').select2('destroy');
                $('#wt-p-division').html(divisionOptions($('#wt-p-division').val() || state.board.divisions[0])).trigger('change.select2');
                const openColor = $('#wt-programmer-list .wt-member-colors:not(.d-none)').data('colorsFor');
                $('#wt-programmer-list').html(state.board.programmers.length ? '<div class="wt-sub mb-1">Klik avatar untuk mengganti warna anggota.</div>' + state.board.programmers.map(function (p) {
                    const palette = COLORS.map(function (c) {
                        return '<button type="button" data-member-color="' + c + '" class="' + (c.toLowerCase() === String(p.color).toLowerCase() ? 'active' : '') + '" style="background:' + c + '" aria-label="Warna ' + c + '"></button>';
                    }).join('') + '<label class="wt-color-custom" title="Warna lain"><i class="fa-solid fa-eye-dropper"></i><input type="color" data-member-color-custom value="' + esc(p.color) + '" aria-label="Warna lain untuk ' + esc(p.name) + '"></label>';
                    return '<div class="wt-member-row" data-user="' + p.user_id + '" data-color="' + esc(p.color) + '" data-division="' + esc(p.division) + '">' +
                        '<div class="d-flex align-items-center gap-2 py-1"><button type="button" class="wt-avatar wt-mini-avatar wt-member-avatar" data-colors-toggle="' + p.id + '" style="background:' + esc(p.color) + '" title="Ganti warna" aria-label="Ganti warna ' + esc(p.name) + '">' + esc(initials(p.name)) + '</button><span class="flex-grow-1 small fw-bold">' + esc(p.name) + '</span>' +
                        '<div class="wt-member-division"><select class="wt-member-select" aria-label="Divisi ' + esc(p.name) + '" data-user="' + p.user_id + '" data-color="' + esc(p.color) + '" data-current="' + esc(p.division) + '">' + divisionOptions(p.division) + '</select></div>' +
                        '<button type="button" class="btn btn-sm btn-link text-danger p-0" data-remove-programmer="' + p.id + '" aria-label="Nonaktifkan ' + esc(p.name) + '"><i class="fa-solid fa-user-minus"></i></button></div>' +
                        '<div class="wt-color wt-member-colors' + (openColor === p.id ? '' : ' d-none') + '" data-colors-for="' + p.id + '">' + palette + '</div></div>';
                }).join('') : '<div class="wt-sub">Belum ada anggota.</div>');
                initDivisionSelect($('#wt-programmer-list .wt-member-select'));
            }

            function loadUsers() {
                if (state.demo) return;
                $.get(ROUTES.users).done(function (users) {
                    $('#wt-p-user').html('<option value="">Pilih user</option>' + users.map(function (u) {
                        return '<option value="' + u.id + '">' + esc(u.name) + ' (' + esc(u.username) + ')</option>';
                    }).join('')).trigger('change.select2');
                }).fail(failed);
            }

            // Events
            $('.wt-select').not('#wt-f-division').select2({ theme: 'bootstrap4', width: '100%', minimumResultsForSearch: 8 }).on('change', renderView);
            $('#wt-f-division').select2({ theme: 'bootstrap4', width: '100%', placeholder: 'Semua divisi', closeOnSelect: false });
            $('.wt-select-modal').select2({ theme: 'bootstrap4', width: '100%', dropdownParent: $('#wt-item-modal') });
            initDivisionSelect($('#wt-p-division'));
            $('#wt-p-user').select2({ theme: 'bootstrap4', width: '100%', dropdownParent: $('#wt-programmer-modal'), placeholder: 'Pilih user' });
            $('#wt-f-search').on('input', renderView);

            $('#wt-filter-toggle').on('click', function () {
                const open = $('#wt-filter-more').hasClass('d-none');
                $('#wt-filter-more').toggleClass('d-none', !open);
                this.setAttribute('aria-expanded', String(open));
            });
            $('#wt-filter-reset').on('click', function () {
                state.tlMode = 'auto';
                state.tlFrom = null;
                state.tlTo = null;
                state.tlZoom = null;
                state.tlScroll = null;
                $('#wt-f-search').val('');
                $('#wt-f-type, #wt-f-level, #wt-f-health').val('').trigger('change.select2');
                $('#wt-f-status').val(state.view === 'report' ? '' : 'open').trigger('change.select2');
                $('#wt-f-programmer').val('').trigger('change');
                $('#wt-f-division').val([]).trigger('change');
            });

            $('#wt-arena-fold').on('click', function () {
                setArenaFolded(!$('#wt-race').hasClass('folded'), true);
            });
            (function () {
                let folded = false;
                try { folded = localStorage.getItem('wt-arena-folded') === '1'; } catch (e) { /* penyimpanan browser tidak tersedia */ }
                if (folded) setArenaFolded(true, false);
            })();
            $('#wt-f-from, #wt-f-to').on('change', renderView);
            $('#wt-archive').on('change', load);

            $('#wt-tabs').on('click', 'button', function () {
                setView(this.dataset.view);
            });

            $(window).on('resize', fitCheckpointLabels);

            $('#wt-lanes').on('click', '[data-item]', function (e) {
                e.stopPropagation();
                openDetail(Number(this.dataset.item));
            });

            $('#wt-division-chips').on('click', '[data-division]', function () {
                const d = this.dataset.division;
                let next = [];
                if (d) {
                    // Chip divisi bersifat hidup/mati; kalau semua terpilih sama saja dengan Semua divisi
                    next = state.divisions.includes(d) ? state.divisions.filter(function (x) { return x !== d; }) : state.divisions.concat([d]);
                    if (next.length === memberDivisions().length) next = [];
                }
                $('#wt-f-division').val(next).trigger('change');
            });

            $('#wt-f-division').on('change', function () {
                state.divisions = $(this).val() || [];
                const owner = state.board.programmers.find(function (p) { return p.id === state.selected; });
                if (state.divisions.length && owner && !state.divisions.includes(owner.division)) {
                    state.selected = null;
                    $('#wt-f-programmer').val('').trigger('change.select2');
                }
                // Arena di atas ikut berubah tinggi; jaga chip divisi tetap di posisi layar yang sama
                const anchor = document.getElementById('wt-division-chips');
                const before = anchor.getBoundingClientRect().top;
                renderLanes();
                renderView();
                const after = anchor.getBoundingClientRect().top;
                if (after !== before) window.scrollBy(0, after - before);
            });

            $('#wt-lanes').on('click', '.wt-lane', function () {
                const id = Number(this.dataset.programmer);
                state.selected = state.selected === id ? null : id;
                $('#wt-f-programmer').val(state.selected || '').trigger('change.select2');
                $('.wt-lane').removeClass('selected');
                if (state.selected) $(this).addClass('selected');
                renderView();
            }).on('click', '#wt-empty-programmer', openProgrammers).on('click', '#wt-demo-start', function () {
                state.demo = true;
                load();
            });

            $('#wt-f-programmer').on('change', function () {
                const value = Number(this.value);
                state.selected = value || null;
                $('.wt-lane').each(function () { $(this).toggleClass('selected', Number(this.dataset.programmer) === value); });
            });

            $('#wt-demo-exit').on('click', function (e) {
                e.preventDefault();
                state.demo = false;
                state.selected = null;
                $('#wt-f-programmer').val('').trigger('change.select2');
                load();
            });

            $view.on('click', 'tbody tr, .wt-row, .wt-kcard, .wt-tl-bar', function (e) {
                if ($(e.target).closest('.wt-assign').length || state.justDragged) return;
                openDetail(Number(this.dataset.id));
            });
            $view.on('pointerdown', '.wt-tl-bar.can-drag', startBarDrag);
            $view.on('pointermove', '.wt-tl-bar.can-drag', moveBarDrag);
            $view.on('pointerup', '.wt-tl-bar.can-drag', endBarDrag);
            $view.on('pointercancel', '.wt-tl-bar.can-drag', function () {
                state.drag = null;
                $('#wt-tl-tip').hide();
                renderView();
            });

            // Shift + scroll menggeser tanggal; di ujung, Timeline bertambah
            $view.on('wheel', '.wt-tl-scroll', function (e) {
                const ev = e.originalEvent;
                if (!ev.shiftKey || state.drag) return;
                let delta = ev.deltaX || ev.deltaY;
                if (!delta) return;
                if (ev.deltaMode === 1) delta *= 16;
                ev.preventDefault();
                const before = this.scrollLeft;
                this.scrollLeft += delta;
                if (this.scrollLeft === before) timelineExtend(delta < 0 ? -1 : 1);
            });

            // Hand tool: tahan klik kiri di area kosong atau klik tengah di mana saja, lalu tarik
            $view.on('mousedown', '.wt-tl-scroll', function (e) {
                if (e.button === 1) e.preventDefault(); // matikan autoscroll bawaan klik tengah
            });
            $view.on('pointerdown', '.wt-tl-scroll', function (e) {
                const ev = e.originalEvent;
                if (ev.pointerType === 'touch' || state.drag) return;
                const onControl = $(ev.target).closest('.wt-tl-bar, .wt-tl-more, button, a, input').length > 0;
                if (!(ev.button === 1 || (ev.button === 0 && !onControl))) return;
                ev.preventDefault();
                state.tlPan = { el: this, x: ev.clientX, y: ev.clientY, left: this.scrollLeft, top: window.scrollY, moved: false };
                this.classList.add('panning');
            });
            $(document).on('pointermove', function (e) {
                const pan = state.tlPan;
                if (!pan) return;
                const ev = e.originalEvent;
                const dx = ev.clientX - pan.x;
                const dy = ev.clientY - pan.y;
                if (!pan.moved && Math.abs(dx) + Math.abs(dy) < 4) return;
                pan.moved = true;
                const target = pan.left - dx;
                const maxLeft = pan.el.scrollWidth - pan.el.clientWidth;
                if (target < 0 || target > maxLeft) {
                    // Tarik melewati ujung: tambah tanggal, lalu lanjutkan pada elemen Timeline yang baru
                    const shift = timelineExtend(target < 0 ? -1 : 1);
                    const fresh = $view.find('.wt-tl-scroll')[0];
                    if (shift !== undefined && fresh) {
                        pan.left += shift;
                        pan.el = fresh;
                        fresh.classList.add('panning');
                    }
                }
                pan.el.scrollLeft = pan.left - dx;
                window.scrollTo(window.scrollX, pan.top - dy);
            });
            $(document).on('pointerup pointercancel', function () {
                if (!state.tlPan) return;
                state.tlPan.el.classList.remove('panning');
                state.tlPan = null;
            });
            $view.on('click', '[data-tl-nav]', function () {
                const dir = Number(this.dataset.tlNav);
                const range = state.tlRange;
                const mode = state.tlMode;
                state.tlZoom = null;
                state.tlScroll = null;
                if (!dir || !range) {
                    // Hari ini: pilihan rentang tetap, periodenya dipusatkan lagi ke hari ini
                    if (mode === 'auto' || mode === 'custom') {
                        state.tlMode = 'auto';
                        state.tlFrom = null;
                        state.tlTo = null;
                    } else {
                        const preset = timelinePreset(mode, state.board.today);
                        state.tlFrom = preset.from;
                        state.tlTo = preset.to;
                    }
                } else if (mode === '1m' || mode === '3m') {
                    // Mode bulanan bergeser per bulan kalender penuh
                    const months = mode === '1m' ? 1 : 3;
                    state.tlFrom = monthStart(range.from, dir * months);
                    state.tlTo = monthEnd(range.from, dir * months + months - 1);
                } else {
                    // Maju atau mundur sepanjang periode yang sedang tampil
                    const span = days(range.from, range.to) + 1;
                    state.tlFrom = addDays(range.from, dir * span);
                    state.tlTo = addDays(range.to, dir * span);
                    if (mode === 'auto') state.tlMode = 'custom';
                }
                renderView();
            });
            $view.on('click', '[data-tl-mode]', function () {
                const mode = this.dataset.tlMode;
                state.tlMode = mode;
                state.tlZoom = null;
                state.tlScroll = null;
                if (mode === 'auto') {
                    state.tlFrom = null;
                    state.tlTo = null;
                } else {
                    // Periode baru mengikuti periode yang sedang dilihat, bukan selalu kembali ke hari ini
                    const range = state.tlRange;
                    const anchor = range && !(state.board.today >= range.from && state.board.today <= range.to) ? range.from : state.board.today;
                    const preset = timelinePreset(mode, mode === '3m' && anchor !== state.board.today ? monthStart(anchor, 1) : anchor);
                    state.tlFrom = preset.from;
                    state.tlTo = preset.to;
                }
                renderView();
            });
            $view.on('change', '#wt-tl-from, #wt-tl-to', function () {
                let from = $('#wt-tl-from').val();
                let to = $('#wt-tl-to').val();
                const valid = /^\d{4}-\d{2}-\d{2}$/;
                if (!valid.test(from) || !valid.test(to)) return;
                if (to < from) {
                    const swap = from;
                    from = to;
                    to = swap;
                }
                // Batasi satu tahun supaya Timeline tidak terlalu panjang
                if (days(from, to) > 366) to = addDays(from, 366);
                state.tlMode = 'custom';
                state.tlZoom = null;
                state.tlScroll = null;
                state.tlFrom = from;
                state.tlTo = to;
                renderView();
            });
            $view.on('click', '.wt-tl-more', function () {
                state.timelineOpen[this.dataset.row] = !state.timelineOpen[this.dataset.row];
                renderView();
            });
            $(window).on('resize', function () {
                if (state.view === 'timeline' && !state.drag) renderView();
            });
            $view.on('click', '.wt-queue-title', function () { openDetail(Number(this.dataset.item)); });
            $('#wt-tiles').on('click keydown', '[data-goto]', function (e) {
                if (e.type === 'keydown' && e.key !== 'Enter') return;
                setView(this.dataset.goto);
                document.querySelector('.wt-panel').scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
            $('.wt').on('change', '.wt-assign-select', function () {
                if (String(this.value) !== String(this.dataset.current)) assignProgrammer(Number(this.dataset.id), this.value);
            });
            $view.on('dragstart', '.wt-kcard', function (e) { e.originalEvent.dataTransfer.setData('text/plain', this.dataset.id); });
            $view.on('dragover', '.wt-col', function (e) { e.preventDefault(); $(this).addClass('over'); });
            $view.on('dragleave', '.wt-col', function () { $(this).removeClass('over'); });
            $view.on('drop', '.wt-col', function (e) {
                e.preventDefault();
                $(this).removeClass('over');
                moveStatus(Number(e.originalEvent.dataTransfer.getData('text/plain')), this.dataset.status);
            });

            $('#wt-btn-add').on('click', function () { openItemForm(null); });
            $('#wt-btn-programmer').on('click', openProgrammers);

            $('#wt-item-form [name=start_date], #wt-item-form [name=target_date]').on('change', fillEstimate);
            $('#wt-item-form [name=estimate_days]').on('input', function () { state.estimateManual = this.value !== ''; });
            $view.on('click', '#wt-export', function () {
                if (guardDemo()) return;
                window.location = ROUTES.exportExcel + '?' + $.param(reportParams());
            });
            $('#wt-urgent').on('click', '[data-urgent]', function () {
                if (Number(this.dataset.assigned)) {
                    openDetail(Number(this.dataset.urgent));
                } else {
                    setView('queue');
                    document.querySelector('.wt-panel').scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
            initHeroFx();
            setInterval(updateSay, 4000);
            // Suasana Arena dicek tiap menit, lintasan hanya digambar ulang kalau suasananya berganti
            setInterval(function () {
                if (state.board && state.ambience && ambienceKey(ambienceNow()) !== ambienceKey(state.ambience)) renderLanes();
            }, 60000);

            $('#wt-i-type').on('change', function () {
                const type = this.value;
                $('.wt-not-plan').toggleClass('d-none', type === 'plan');
                $('#wt-i-type-pills button').each(function () {
                    const active = this.dataset.type === type;
                    $(this).toggleClass('active', active).attr('aria-checked', String(active));
                });
            });
            $('#wt-i-type-pills').on('click', 'button', function () {
                $('#wt-i-type').val(this.dataset.type).trigger('change');
            });
            $('#wt-item-modal').on('shown.bs.modal', function () {
                if (!this.querySelector('[name=id]').value) this.querySelector('[name=title]').focus();
            });
            $('#wt-i-level').on('change', function () { $('#wt-i-reason-box').toggleClass('d-none', this.value !== 'tidak'); });

            $('#wt-item-form').on('submit', function (e) {
                e.preventDefault();
                // Poin yang sudah diketik tapi belum ditambahkan ikut tersimpan
                addFormPoint();
                const data = new FormData(this);
                if (state.formPointsReady) data.append('points', JSON.stringify(state.formPoints.map(function (p) { return { id: p.id, content: p.content, done: p.done }; })));
                state.formFiles.forEach(function (file) { data.append('files[]', file); });
                postForm(ROUTES.store, data, function (res) {
                    clearPending(state.formFiles);
                    $('#wt-i-files').empty();
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('wt-item-modal')).hide();
                    toast(res.message);
                    load();
                });
            });

            $('#wt-i-point-add').on('click', addFormPoint);
            $('#wt-i-point-input').on('keydown', function (e) {
                if (e.key !== 'Enter') return;
                e.preventDefault();
                addFormPoint();
            });
            $('#wt-i-points').on('click', '.wt-point-toggle', function () {
                const $point = $(this).closest('.wt-point');
                const point = state.formPoints[Number($point.data('index'))];
                point.done = !point.done;
                setPointDone($point, point.done);
                pointsProgress('#wt-i-points', state.formPoints);
            }).on('click', '.wt-fp-edit', function () {
                const $point = $(this).closest('.wt-point');
                const point = state.formPoints[Number($point.data('index'))];
                inlineEditPoint($point, point.content, function (value) {
                    if (value) point.content = value;
                    renderFormPoints();
                });
            }).on('click', '.wt-fp-del', function () {
                state.formPoints.splice(Number($(this).closest('.wt-point').data('index')), 1);
                renderFormPoints();
            });

            $('#wt-item-delete').on('click', function () {
                const id = document.getElementById('wt-item-form').elements.id.value;
                if (guardDemo()) return;
                Swal.fire({ icon: 'warning', title: 'Hapus kerjaan ini?', showCancelButton: true, confirmButtonText: 'Hapus', cancelButtonText: 'Batal', confirmButtonColor: '#dc3545' }).then(function (r) {
                    if (!r.isConfirmed) return;
                    post(ROUTES.remove, { id: id }, function (res) {
                        bootstrap.Modal.getOrCreateInstance(document.getElementById('wt-item-modal')).hide();
                        toast(res.message);
                        load();
                    });
                });
            });

            $('#wt-range').on('input', function () { $('#wt-range-value').text(this.value); });

            $('#wt-progress-form').on('submit', function (e) {
                e.preventDefault();
                const item = state.current;
                const data = new FormData();
                data.append('id', item.id);
                data.append('progress', $('#wt-range').val());
                data.append('note', $('#wt-note').val());
                state.progressFiles.forEach(function (file) { data.append('files[]', file); });
                postForm(ROUTES.progress, data, function (res) {
                    clearPending(state.progressFiles);
                    $('#wt-progress-files').empty();
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('wt-detail-modal')).hide();
                    toast(res.message);
                    if (res.just_finished) celebrate(item);
                    load();
                });
            });

            $('#wt-point-form').on('submit', function (e) {
                e.preventDefault();
                const content = $('#wt-point-input').val().trim();
                if (!content || !state.current) return;
                post(ROUTES.pointStore, { work_item_id: state.current.id, content: content }, function () {
                    $('#wt-point-input').val('').trigger('focus');
                    refreshDetail();
                });
            });

            $('#wt-points').on('click', '.wt-point-toggle', function () {
                if (guardDemo() || !state.detail) return;
                const $point = $(this).closest('.wt-point');
                const id = Number($point.data('point'));
                const point = state.detail.points.find(function (p) { return Number(p.id) === id; });
                if (!point) return;
                const done = !$point.hasClass('done');
                const button = this;

                // Langsung tercentang di layar, lalu disimpan. Kalau gagal dikembalikan.
                point.is_done = done ? 1 : 0;
                setPointDone($point, done);
                pointsProgress('#wt-points', state.detail.points.map(function (p) { return { done: !!Number(p.is_done) }; }));

                // Dikunci sampai selesai supaya klik dobel tidak membatalkan centang
                button.disabled = true;
                post(ROUTES.pointToggle, { id: id, done: done ? 1 : 0 }, function () {
                    // Ditunda sebentar supaya animasi centang selesai sebelum daftar digambar ulang
                    setTimeout(refreshDetail, 450);
                }).fail(function () {
                    point.is_done = done ? 0 : 1;
                    renderDetail();
                }).always(function () { button.disabled = false; });
            }).on('dragenter dragover', '.wt-point', function (e) {
                if (!hasFiles(e)) return;
                e.preventDefault();
                $(this).addClass('drop-over');
            }).on('dragleave drop', '.wt-point', function (e) {
                if (!hasFiles(e)) return;
                e.preventDefault();
                if (e.type === 'dragleave' && $.contains(this, e.relatedTarget)) return;
                $(this).removeClass('drop-over');
                if (e.type === 'drop') uploadAttachments(e.originalEvent.dataTransfer.files, Number(this.dataset.point));
            }).on('click', '.wt-point-edit', function () {
                const $point = $(this).closest('.wt-point');
                const id = Number($point.data('point'));
                const point = state.detail.points.find(function (p) { return Number(p.id) === id; });
                if (!point) return;
                inlineEditPoint($point, point.content, function (value) {
                    if (!value || !post(ROUTES.pointStore, { id: id, content: value }, refreshDetail)) renderDetail();
                });
            }).on('click', '.wt-point-del', function () {
                const id = Number($(this).closest('.wt-point').data('point'));
                Swal.fire({ icon: 'warning', title: 'Hapus poin ini?', text: 'Lampiran di poin ini ikut terhapus.', showCancelButton: true, confirmButtonText: 'Hapus', cancelButtonText: 'Batal', confirmButtonColor: '#dc3545' }).then(function (r) {
                    if (r.isConfirmed) post(ROUTES.pointDelete, { id: id }, refreshDetail);
                });
            }).on('click', '.wt-point-attach', function () {
                state.attachPoint = Number($(this).closest('.wt-point').data('point'));
                $('#wt-attach-input').val('').trigger('click');
            });

            $('#wt-attach-input').on('change', function () {
                uploadAttachments(this.files, state.attachPoint);
                this.value = '';
            });

            // Kotak drop: lampiran kerjaan di detail langsung diunggah, yang di form disimpan saat klik Simpan
            bindDrop($('#wt-d-drop'), function (files) { uploadAttachments(files, null); });
            bindDrop($('#wt-i-drop'), function (files) {
                addPending(state.formFiles, files);
                $('#wt-i-files').html(pendingHtml(state.formFiles));
            });
            bindDrop($('#wt-progress-drop'), function (files) {
                addPending(state.progressFiles, files);
                $('#wt-progress-files').html(pendingHtml(state.progressFiles));
            });
            $('#wt-i-files').on('click', '[data-pending]', function () {
                removePending(state.formFiles, Number(this.dataset.pending));
                $('#wt-i-files').html(pendingHtml(state.formFiles));
            });
            $('#wt-progress-files').on('click', '[data-pending]', function () {
                removePending(state.progressFiles, Number(this.dataset.pending));
                $('#wt-progress-files').html(pendingHtml(state.progressFiles));
            });
            $(document).on('click', 'a[data-lightbox]', function (e) {
                e.preventDefault();
                openLightbox(this.href, this.title);
            });
            // File yang terlepas di luar kotak drop jangan sampai membuka file itu di tab
            $(document).on('dragover drop', function (e) {
                if (hasFiles(e)) e.preventDefault();
            });

            $('#wt-detail-modal').on('click', '.wt-file-del[data-attachment]', function () {
                const id = Number(this.dataset.attachment);
                Swal.fire({ icon: 'warning', title: 'Hapus lampiran ini?', showCancelButton: true, confirmButtonText: 'Hapus', cancelButtonText: 'Batal', confirmButtonColor: '#dc3545' }).then(function (r) {
                    if (r.isConfirmed) post(ROUTES.attachmentDelete, { id: id }, refreshDetail);
                });
            });

            $('#wt-d-edit').on('click', function () {
                const item = state.current;
                bootstrap.Modal.getOrCreateInstance(document.getElementById('wt-detail-modal')).hide();
                openItemForm(item);
            });

            $('#wt-p-colors').on('click', 'button', function () {
                state.color = this.dataset.color;
                $('#wt-p-colors button').removeClass('active');
                $(this).addClass('active');
            });

            $('#wt-programmer-form').on('submit', function (e) {
                e.preventDefault();
                const userId = $('#wt-p-user').val();
                if (!state.demo && !userId) {
                    toast('Pilih user dulu', 'warning');
                    return;
                }
                post(ROUTES.programmerStore, { user_id: userId, color: state.color, division: $('#wt-p-division').val() }, function (res) {
                    toast(res.message);
                    load().done(function () { renderProgrammerList(); loadUsers(); });
                });
            });

            $('#wt-programmer-list').on('change', '.wt-member-select', function () {
                if (this.value === this.dataset.current) return;
                if (guardDemo()) {
                    renderProgrammerList();
                    return;
                }
                post(ROUTES.programmerStore, { user_id: this.dataset.user, color: this.dataset.color, division: this.value }, function (res) {
                    toast(res.message);
                    load().done(renderProgrammerList);
                });
            });

            $('#wt-programmer-list').on('click', '[data-colors-toggle]', function () {
                const $palette = $('#wt-programmer-list [data-colors-for="' + this.dataset.colorsToggle + '"]');
                const show = $palette.hasClass('d-none');
                $('#wt-programmer-list .wt-member-colors').addClass('d-none');
                $palette.toggleClass('d-none', !show);
            });

            function saveMemberColor(el, color) {
                const row = $(el).closest('.wt-member-row')[0];
                if (!row || String(color).toLowerCase() === String(row.dataset.color).toLowerCase()) return;
                if (guardDemo()) return;
                post(ROUTES.programmerStore, { user_id: row.dataset.user, color: color, division: row.dataset.division }, function () {
                    toast('Warna anggota diganti');
                    load().done(renderProgrammerList);
                });
            }
            $('#wt-programmer-list').on('click', '[data-member-color]', function () {
                saveMemberColor(this, this.dataset.memberColor);
            });
            // Warna bebas disimpan setelah pemilih warna ditutup, bukan setiap kali digeser
            $('#wt-programmer-list').on('change', '[data-member-color-custom]', function () {
                saveMemberColor(this, this.value);
            });

            $('#wt-programmer-list').on('click', '[data-remove-programmer]', function () {
                const id = this.dataset.removeProgrammer;
                if (guardDemo()) return;
                Swal.fire({ icon: 'warning', title: 'Nonaktifkan anggota ini?', text: 'Kerjaan yang masih aktif akan kembali ke Belum ditugaskan.', showCancelButton: true, confirmButtonText: 'Nonaktifkan', cancelButtonText: 'Batal', confirmButtonColor: '#dc3545' }).then(function (r) {
                    if (!r.isConfirmed) return;
                    post(ROUTES.programmerDelete, { id: id }, function (res) {
                        toast(res.message);
                        load().done(function () { renderProgrammerList(); loadUsers(); });
                    });
                });
            });

            // Dashboard membuka kerjaan tertentu lewat ?item=ID
            const itemFromLink = Number(new URLSearchParams(window.location.search).get('item'));
            load().done(function () {
                if (itemFromLink) openDetail(itemFromLink);
            });
        })();
    </script>
@endsection
