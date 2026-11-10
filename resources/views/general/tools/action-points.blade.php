@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <style>
        /* Warna aksen diisi JS dari palet tema; mode terang/gelap lewat atribut data-ap-mode di <html> */
        :root {
            --ap-a: #2563eb; --ap-b: #06b6d4; --ap-a-rgb: 37, 99, 235;
            --ap-bg: #f2f5fb; --ap-card: #ffffff; --ap-ink: #18223a; --ap-muted: #6b778c; --ap-line: #e3e8f1; --ap-soft: #f5f7fc; --ap-input: #ffffff;
            --ap-shadow: 0 6px 22px rgba(15, 30, 70, .07); --ap-shadow-hover: 0 16px 34px rgba(15, 30, 70, .15);
            --s-open: #3b82f6; --s-progress: #f59e0b; --s-done: #10b981; --s-batal: #94a3b8; --s-late: #ef4444;
        }
        html[data-ap-mode="dark"] {
            --ap-bg: #0b1122; --ap-card: #141c31; --ap-ink: #e5eaf5; --ap-muted: #95a2bb; --ap-line: #243049; --ap-soft: #1a2440; --ap-input: #10182c;
            --ap-shadow: 0 6px 22px rgba(0, 0, 0, .35); --ap-shadow-hover: 0 16px 34px rgba(0, 0, 0, .5);
        }
        html[data-ap-mode] .content-wrapper { background: var(--ap-bg); transition: background .5s ease; }
        html[data-ap-mode="dark"] .main-footer { background: var(--ap-card); color: var(--ap-muted); border-color: var(--ap-line); }
        html[data-ap-mode="dark"] .main-footer a { color: var(--ap-ink) !important; }

        .ap { color: var(--ap-ink); padding-bottom: 40px; }
        .ap-card { background: var(--ap-card); border: 1px solid var(--ap-line); border-radius: 18px; box-shadow: var(--ap-shadow); transition: background .5s, border-color .5s; }
        .ap-muted { color: var(--ap-muted); }

        /* Hero */
        .ap-hero { position: relative; overflow: hidden; border-radius: 22px; padding: 24px 28px; color: #fff; margin-bottom: 14px;
            background: linear-gradient(120deg, var(--ap-a), var(--ap-b), var(--ap-a)); background-size: 220% 220%; animation: ap-gradient 14s ease infinite;
            box-shadow: 0 14px 34px rgba(var(--ap-a-rgb), .32); }
        @keyframes ap-gradient { 0%, 100% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } }
        .ap-blob { position: absolute; border-radius: 50%; background: rgba(255, 255, 255, .12); filter: blur(2px); animation: ap-blob 16s ease-in-out infinite; }
        .ap-blob.b1 { width: 260px; height: 260px; right: -70px; top: -110px; }
        .ap-blob.b2 { width: 160px; height: 160px; right: 22%; bottom: -90px; animation-delay: -5s; animation-duration: 19s; }
        .ap-blob.b3 { width: 90px; height: 90px; left: 38%; top: -40px; animation-delay: -9s; animation-duration: 13s; }
        @keyframes ap-blob { 0%, 100% { transform: translate(0, 0) scale(1); } 33% { transform: translate(-24px, 18px) scale(1.08); } 66% { transform: translate(18px, -12px) scale(.94); } }
        .ap-float { position: absolute; color: rgba(255, 255, 255, .22); font-size: var(--s, 22px); left: var(--x); top: var(--y); animation: ap-float var(--d, 7s) ease-in-out infinite; animation-delay: var(--delay, 0s); pointer-events: none; }
        @keyframes ap-float { 0%, 100% { transform: translateY(0) rotate(0); } 50% { transform: translateY(-14px) rotate(10deg); } }
        .ap-hero-inner { position: relative; z-index: 1; display: flex; flex-wrap: wrap; justify-content: space-between; gap: 18px; }
        .ap-hero-main { display: flex; align-items: center; gap: 22px; min-width: 0; }
        .ap-ring { position: relative; flex: none; width: 122px; height: 122px; }
        .ap-ring svg { width: 100%; height: 100%; transform: rotate(-90deg); }
        .ap-ring circle { fill: none; stroke-width: 10; }
        .ap-ring .track { stroke: rgba(255, 255, 255, .22); }
        .ap-ring .value { stroke: #fff; stroke-linecap: round; stroke-dasharray: 100; stroke-dashoffset: 100; transition: stroke-dashoffset 1.6s cubic-bezier(.22, .8, .3, 1); filter: drop-shadow(0 0 6px rgba(255, 255, 255, .6)); }
        .ap-ring-text { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; line-height: 1.05; }
        .ap-ring-text b { font-size: 28px; font-weight: 800; font-variant-numeric: tabular-nums; }
        .ap-ring-text span { font-size: 11px; font-weight: 600; opacity: .9; }
        .ap-greet { font-size: 13px; opacity: .9; }
        .ap-hero h1 { margin: 2px 0 0; font-size: 27px; font-weight: 800; letter-spacing: -.01em; }
        .ap-hero h1 i { animation: ap-wiggle 3.5s ease-in-out infinite; display: inline-block; }
        @keyframes ap-wiggle { 0%, 86%, 100% { transform: rotate(0); } 90% { transform: rotate(-14deg); } 95% { transform: rotate(12deg); } }
        .ap-hero-msg { display: inline-flex; align-items: center; gap: 9px; margin-top: 10px; padding: 8px 15px; border-radius: 13px; background: rgba(255, 255, 255, .96); color: #18223a; font-weight: 700; font-size: 14.5px; box-shadow: 0 6px 16px rgba(0, 0, 0, .16); animation: ap-pop .5s cubic-bezier(.2, 1.4, .4, 1) both; }
        .ap-hero-msg.good i { color: #0f9d6b; } .ap-hero-msg.warn i { color: #c27803; } .ap-hero-msg.bad i { color: #dc2626; }
        .ap-hero-actions { display: flex; flex-wrap: wrap; align-items: flex-start; gap: 8px; }
        .ap-btn-ghost { border: 1px solid rgba(255, 255, 255, .45); background: rgba(255, 255, 255, .1); color: #fff; font-weight: 600; border-radius: 11px; padding: 6px 13px; font-size: 13.5px; backdrop-filter: blur(4px); transition: background .15s, transform .15s; }
        .ap-btn-ghost:hover { background: rgba(255, 255, 255, .24); color: #fff; transform: translateY(-1px); }
        .ap-btn-light { border: 0; background: #fff; color: var(--ap-a); font-weight: 700; border-radius: 11px; padding: 6px 15px; font-size: 13.5px; box-shadow: 0 6px 16px rgba(0, 0, 0, .15); transition: transform .15s, box-shadow .15s; }
        .ap-btn-light:hover { transform: translateY(-2px) scale(1.02); box-shadow: 0 10px 22px rgba(0, 0, 0, .2); }
        .ap-btn-light i { transition: transform .3s; } .ap-btn-light:hover i { transform: rotate(90deg); }

        /* Animasi tambahan hero */
        .ap-shine { position: absolute; top: 0; bottom: 0; left: -40%; width: 30%; background: linear-gradient(100deg, transparent, rgba(255, 255, 255, .22), transparent); transform: skewX(-20deg); animation: ap-shine 7s ease-in-out infinite; pointer-events: none; }
        @keyframes ap-shine { 0%, 60% { left: -40%; } 100% { left: 130%; } }
        .ap-bubbles { position: absolute; inset: 0; overflow: hidden; pointer-events: none; }
        .ap-bubbles span { position: absolute; bottom: -20px; left: var(--l); width: var(--z); height: var(--z); border-radius: 50%; background: rgba(255, 255, 255, .28); box-shadow: inset 0 0 4px rgba(255, 255, 255, .6); animation: ap-bubble var(--t) linear infinite; animation-delay: var(--dl); }
        @keyframes ap-bubble { 0% { transform: translate(0, 0) scale(.6); opacity: 0; } 10% { opacity: 1; } 50% { transform: translate(var(--sw), -120px) scale(1); } 100% { transform: translate(0, -260px) scale(.8); opacity: 0; } }
        .ap-float { translate: calc(var(--mx, 0) * var(--depth, 14px)) calc(var(--my, 0) * var(--depth, 14px)); transition: translate .4s ease-out; }
        .ap-float.twinkle { animation: ap-twinkle var(--d) ease-in-out infinite; animation-delay: var(--delay, 0s); color: rgba(255, 255, 255, .7); }
        @keyframes ap-twinkle { 0%, 100% { opacity: .15; transform: scale(.6) rotate(0); } 50% { opacity: 1; transform: scale(1.2) rotate(72deg); } }
        .ap-blob { translate: calc(var(--mx, 0) * -22px) calc(var(--my, 0) * -22px); transition: translate .6s ease-out; }
        .ap-wave { position: absolute; left: 0; bottom: 0; width: 200%; height: 46px; pointer-events: none; }
        .ap-wave path { fill: rgba(255, 255, 255, .09); }
        .ap-wave .w1 { animation: ap-wave 12s linear infinite; }
        .ap-wave .w2 { fill: rgba(255, 255, 255, .07); animation: ap-wave 18s linear infinite reverse; }
        @keyframes ap-wave { from { transform: translateX(0); } to { transform: translateX(-50%); } }

        .ap-ring-orbit { position: absolute; inset: -9px; border-radius: 50%; border: 2px dashed rgba(255, 255, 255, .35); animation: fa-spin 22s linear infinite; }
        .ap-ring-orbit i { position: absolute; top: -5px; left: 50%; width: 9px; height: 9px; margin-left: -4.5px; border-radius: 50%; background: #fff; box-shadow: 0 0 10px 3px rgba(255, 255, 255, .8); animation: fa-spin 22s linear infinite reverse; }
        .ap-ring { animation: ap-ring-breathe 3.2s ease-in-out infinite; }
        @keyframes ap-ring-breathe { 0%, 100% { filter: drop-shadow(0 0 0 rgba(255, 255, 255, 0)); } 50% { filter: drop-shadow(0 0 14px rgba(255, 255, 255, .45)); } }
        .ap-ring.pop .ap-ring-text b { animation: ap-num-pop .6s cubic-bezier(.2, 1.6, .4, 1); }
        @keyframes ap-num-pop { 40% { transform: scale(1.35); } }

        .ap-clock { display: inline-block; margin-left: 4px; padding: 1px 8px; border-radius: 8px; background: rgba(255, 255, 255, .18); font-weight: 700; font-variant-numeric: tabular-nums; }
        .ap-clock .colon { animation: ap-blink 1s steps(1) infinite; }
        .ap-title-text .ch { display: inline-block; animation: ap-letter .7s cubic-bezier(.2, 1.5, .4, 1) backwards; animation-delay: calc(var(--i) * 45ms + .15s); }
        .ap-title-text .ch.sp { width: .28em; }
        @keyframes ap-letter { from { opacity: 0; transform: translateY(26px) rotate(12deg) scale(.6); } }
        #ap-title:hover .ch { animation: ap-letter-wave .6s ease; animation-delay: calc(var(--i) * 35ms); }
        @keyframes ap-letter-wave { 40% { transform: translateY(-8px); color: rgba(255, 255, 255, .85); } }

        .ap-hero-msg { position: relative; overflow: hidden; perspective: 400px; min-height: 40px; }
        .ap-msg-body { display: inline-flex; align-items: center; gap: 9px; transition: transform .3s ease, opacity .3s ease; transform-origin: center bottom; }
        .ap-hero-msg.flip .ap-msg-body { transform: rotateX(90deg) translateY(-6px); opacity: 0; }
        .ap-msg-bar { position: absolute; left: 0; bottom: 0; height: 3px; width: 0; background: linear-gradient(90deg, var(--ap-a), var(--ap-b)); }
        .ap-msg-bar.run { animation: ap-msg-bar var(--msg-dur, 6s) linear forwards; }
        @keyframes ap-msg-bar { to { width: 100%; } }
        .ap-hero-msg.bad .ap-msg-body i { animation: ap-ring-bell 1.6s ease-in-out infinite; }

        .ap-btn-light, .ap-btn-ghost, .ap-btn-main, .ap-tabs button, .ap-tile { position: relative; overflow: hidden; }
        .ap-btn-light::after { content: ""; position: absolute; top: 0; bottom: 0; left: -60%; width: 40%; background: linear-gradient(100deg, transparent, rgba(var(--ap-a-rgb), .25), transparent); transform: skewX(-20deg); animation: ap-shine 3.5s ease-in-out infinite; }
        .ap-btn-light.nudge { animation: ap-nudge 2.4s ease-in-out infinite; }
        @keyframes ap-nudge { 0%, 70%, 100% { transform: none; } 76% { transform: translateY(-5px) scale(1.05); } 82% { transform: none; } 88% { transform: translateY(-3px); } }
        .ap-ripple { position: absolute; border-radius: 50%; background: rgba(255, 255, 255, .55); transform: scale(0); animation: ap-ripple .6s ease-out forwards; pointer-events: none; }
        .ap-tile .ap-ripple, .ap-tabs .ap-ripple { background: rgba(var(--ap-a-rgb), .25); }
        @keyframes ap-ripple { to { transform: scale(4); opacity: 0; } }

        /* Pilihan tema */
        .ap-theme-wrap { position: relative; }
        .ap-theme-pop { position: absolute; right: 0; top: calc(100% + 8px); z-index: 30; width: 250px; padding: 14px; border-radius: 16px; background: var(--ap-card); color: var(--ap-ink); border: 1px solid var(--ap-line); box-shadow: 0 18px 40px rgba(0, 0, 0, .25); transform-origin: top right; animation: ap-pop .25s ease both; }
        .ap-theme-pop h6 { font-size: 11.5px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--ap-muted); margin: 0 0 8px; }
        .ap-modes { display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px; margin-bottom: 14px; }
        .ap-modes button { border: 1px solid var(--ap-line); background: var(--ap-soft); color: var(--ap-ink); border-radius: 10px; padding: 7px 4px; font-size: 12px; font-weight: 600; }
        .ap-modes button i { display: block; font-size: 15px; margin-bottom: 3px; }
        .ap-modes button.active { border-color: var(--ap-a); background: rgba(var(--ap-a-rgb), .12); color: var(--ap-a); }
        .ap-palettes { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
        .ap-palettes button { border: 2px solid transparent; background: none; border-radius: 12px; padding: 4px; font-size: 11.5px; font-weight: 600; color: var(--ap-ink); transition: transform .15s; }
        .ap-palettes button:hover { transform: translateY(-2px); }
        .ap-palettes button span { display: block; height: 30px; border-radius: 9px; margin-bottom: 3px; }
        .ap-palettes button.active { border-color: var(--ap-a); }
        .ap-theme-note { margin-top: 10px; font-size: 11.5px; color: var(--ap-muted); }

        /* Ticker poin lewat target */
        .ap-ticker { display: flex; align-items: stretch; overflow: hidden; margin-bottom: 14px; border-radius: 14px; background: linear-gradient(90deg, #b91c1c, #ef4444); color: #fff; box-shadow: 0 8px 20px rgba(239, 68, 68, .28); animation: ap-rise .5s ease backwards; }
        .ap-ticker-label { flex: none; display: flex; align-items: center; gap: 8px; padding: 9px 16px; font-weight: 800; font-size: 13px; background: rgba(0, 0, 0, .18); white-space: nowrap; }
        .ap-ticker-label i { animation: ap-ring-bell 1.6s ease-in-out infinite; }
        @keyframes ap-ring-bell { 0%, 50%, 100% { transform: rotate(0); } 10%, 30% { transform: rotate(-18deg); } 20%, 40% { transform: rotate(18deg); } }
        .ap-ticker-track { flex: 1; overflow: hidden; display: flex; align-items: center; mask-image: linear-gradient(90deg, transparent, #000 4%, #000 96%, transparent); -webkit-mask-image: linear-gradient(90deg, transparent, #000 4%, #000 96%, transparent); }
        .ap-ticker-move { display: inline-flex; gap: 34px; white-space: nowrap; padding-left: 20px; animation: ap-marquee var(--dur, 30s) linear infinite; }
        .ap-ticker:hover .ap-ticker-move { animation-play-state: paused; }
        .ap-ticker-move button { border: 0; background: none; color: #fff; font-size: 13px; padding: 0; }
        .ap-ticker-move button b { margin-right: 4px; }
        .ap-ticker-move button:hover { text-decoration: underline; }
        @keyframes ap-marquee { from { transform: translateX(0); } to { transform: translateX(-50%); } }

        /* Tiles */
        .ap-tiles { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; margin-bottom: 14px; }
        .ap-tile { position: relative; overflow: hidden; text-align: left; padding: 14px 15px 12px; border: 1px solid var(--ap-line); border-radius: 18px; background: var(--ap-card); color: var(--ap-ink); box-shadow: var(--ap-shadow); cursor: pointer; animation: ap-rise .5s ease backwards; animation-delay: calc(var(--i) * 70ms); transition: transform .18s, box-shadow .18s, border-color .18s, background .5s; }
        .ap-tile::before { content: ""; position: absolute; inset: 0; background: radial-gradient(circle at 100% 0%, var(--c), transparent 62%); opacity: .14; transition: opacity .2s; }
        .ap-tile:hover { transform: translateY(-4px); box-shadow: var(--ap-shadow-hover); }
        .ap-tile:hover::before { opacity: .26; }
        .ap-tile.active { border-color: var(--c); box-shadow: 0 0 0 3px color-mix(in srgb, var(--c) 25%, transparent), var(--ap-shadow-hover); }
        .ap-tile > * { position: relative; }
        .ap-tile-top { display: flex; align-items: center; justify-content: space-between; }
        .ap-tile-icon { width: 42px; height: 42px; border-radius: 13px; display: grid; place-items: center; color: #fff; font-size: 17px; background: var(--c); box-shadow: 0 6px 14px color-mix(in srgb, var(--c) 45%, transparent); }
        .ap-tile:hover .ap-tile-icon { animation: ap-jelly .6s ease; }
        @keyframes ap-jelly { 30% { transform: scale(1.18, .86); } 50% { transform: scale(.9, 1.1); } 70% { transform: scale(1.05, .96); } }
        .ap-tile-value { font-size: 30px; font-weight: 800; line-height: 1; font-variant-numeric: tabular-nums; }
        .ap-tile-label { margin-top: 8px; font-size: 12.5px; font-weight: 600; color: var(--ap-muted); }
        .ap-tile-bar { height: 6px; border-radius: 6px; margin-top: 9px; background: var(--ap-soft); overflow: hidden; }
        .ap-tile-bar span { display: block; height: 100%; width: 0; border-radius: 6px; background: var(--c); transition: width 1.2s cubic-bezier(.22, .8, .3, 1); }
        .ap-tile.alert { animation: ap-rise .5s ease backwards, ap-glow 1.8s ease-in-out infinite; }
        @keyframes ap-glow { 0%, 100% { box-shadow: var(--ap-shadow), 0 0 0 0 rgba(239, 68, 68, .45); } 50% { box-shadow: var(--ap-shadow), 0 0 0 8px rgba(239, 68, 68, 0); } }

        /* Panel + tab */
        .ap-panel { padding: 16px 18px 20px; }
        .ap-panel-head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; }
        .ap-tabs { position: relative; display: inline-flex; padding: 5px; border-radius: 15px; background: var(--ap-soft); border: 1px solid var(--ap-line); }
        .ap-tabs button { position: relative; z-index: 1; border: 0; background: none; padding: 9px 18px; border-radius: 11px; font-weight: 700; font-size: 14px; color: var(--ap-muted); transition: color .25s; }
        .ap-tabs button.active { color: #fff; }
        .ap-tab-ind { position: absolute; z-index: 0; top: 5px; bottom: 5px; left: 0; width: 0; border-radius: 11px; background: linear-gradient(120deg, var(--ap-a), var(--ap-b)); box-shadow: 0 6px 16px rgba(var(--ap-a-rgb), .4); transition: transform .45s cubic-bezier(.3, 1.3, .5, 1), width .45s cubic-bezier(.3, 1.3, .5, 1); }
        .ap-tab-count { display: inline-block; min-width: 22px; margin-left: 4px; padding: 1px 7px; border-radius: 10px; font-size: 11.5px; background: rgba(var(--ap-a-rgb), .14); color: var(--ap-a); transition: background .25s, color .25s; }
        .ap-tabs button.active .ap-tab-count { background: rgba(255, 255, 255, .25); color: #fff; }
        .ap-tab-count.bump { animation: ap-bump .45s ease; }
        @keyframes ap-bump { 40% { transform: scale(1.35); } }
        .ap-switch { font-size: 13px; color: var(--ap-muted); }
        .ap-switch .form-check-input:checked { background-color: var(--ap-a); border-color: var(--ap-a); }

        .ap-filters { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 10px; margin: 14px 0 16px; }
        .ap-filters label, .ap-form label { display: block; margin-bottom: 4px; font-size: 12px; font-weight: 600; color: var(--ap-muted); }
        .ap .form-control, .ap-modal .form-control { background: var(--ap-input); color: var(--ap-ink); border-color: var(--ap-line); border-radius: 10px; }
        .ap .form-control:focus, .ap-modal .form-control:focus { border-color: var(--ap-a); box-shadow: 0 0 0 3px rgba(var(--ap-a-rgb), .18); }
        html[data-ap-mode="dark"] input[type="date"] { color-scheme: dark; }
        .ap-search { position: relative; }
        .ap-search i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--ap-muted); font-size: 13px; }
        .ap-search input { padding-left: 33px; }

        /* select2 mengikuti tema */
        .ap .select2-container--bootstrap4 .select2-selection, .ap-modal .select2-container--bootstrap4 .select2-selection { background: var(--ap-input); border-color: var(--ap-line); border-radius: 10px; }
        .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered { color: var(--ap-ink); }
        html[data-ap-mode="dark"] .select2-dropdown { background: var(--ap-card); border-color: var(--ap-line); color: var(--ap-ink); }
        html[data-ap-mode="dark"] .select2-search__field { background: var(--ap-input); color: var(--ap-ink); border-color: var(--ap-line) !important; }
        html[data-ap-mode="dark"] .select2-results__option[aria-selected="true"] { background: var(--ap-soft); }
        .select2-container--bootstrap4 .select2-results__option--highlighted, .select2-container--bootstrap4 .select2-results__option--highlighted.select2-results__option[aria-selected="true"] { background: var(--ap-a) !important; }

        .ap-view { animation: ap-fade-slide .4s ease both; }
        @keyframes ap-fade-slide { from { opacity: 0; transform: translateX(var(--from, 14px)); } to { opacity: 1; transform: none; } }
        @keyframes ap-rise { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: none; } }
        @keyframes ap-pop { from { opacity: 0; transform: scale(.85); } to { opacity: 1; transform: none; } }

        /* Chip umum */
        .ap-chip { display: inline-flex; align-items: center; gap: 5px; padding: 2px 9px; border-radius: 20px; font-size: 11.5px; font-weight: 600; background: var(--ap-soft); color: var(--ap-muted); border: 1px solid var(--ap-line); white-space: nowrap; }
        .ap-status { --c: var(--s-open); display: inline-flex; align-items: center; gap: 5px; padding: 2px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 700; color: var(--c); background: color-mix(in srgb, var(--c) 14%, transparent); white-space: nowrap; }
        .s-progress .ap-status, .ap-status.s-progress { --c: var(--s-progress); }
        .s-done .ap-status, .ap-status.s-done { --c: var(--s-done); }
        .s-batal .ap-status, .ap-status.s-batal { --c: var(--s-batal); }
        .ap-status.s-progress i { animation: fa-spin 2.4s linear infinite; }
        .ap-prio { display: inline-flex; align-items: center; gap: 5px; font-size: 11.5px; font-weight: 700; }
        .ap-prio i { font-size: 8px; }
        .ap-prio.p-tinggi { color: #ef4444; } .ap-prio.p-sedang { color: #f59e0b; } .ap-prio.p-rendah { color: #10b981; }
        .ap-prio.p-tinggi i { animation: ap-blink 1.2s ease-in-out infinite; }
        @keyframes ap-blink { 50% { opacity: .25; } }
        .ap-due { display: inline-flex; align-items: center; gap: 5px; padding: 2px 9px; border-radius: 20px; font-size: 11.5px; font-weight: 700; white-space: nowrap; }
        .ap-due.calm { background: var(--ap-soft); color: var(--ap-muted); }
        .ap-due.warn { background: rgba(245, 158, 11, .15); color: #c27803; }
        .ap-due.late { background: rgba(239, 68, 68, .14); color: #dc2626; animation: ap-late-pulse 1.6s ease-in-out infinite; }
        .ap-due.ok { background: rgba(16, 185, 129, .14); color: #059669; }
        .ap-due.mute { background: var(--ap-soft); color: var(--ap-muted); }
        @keyframes ap-late-pulse { 50% { background: rgba(239, 68, 68, .28); } }
        html[data-ap-mode="dark"] .ap-due.warn { color: #fbbf24; } html[data-ap-mode="dark"] .ap-due.late { color: #f87171; } html[data-ap-mode="dark"] .ap-due.ok { color: #34d399; }
        .ap-avatar { flex: none; display: inline-grid; place-items: center; width: 28px; height: 28px; border-radius: 50%; color: #fff; font-size: 11px; font-weight: 800; background: var(--av, #64748b); border: 2px solid var(--ap-card); }
        .ap-avatars { display: inline-flex; }
        .ap-avatars .ap-avatar + .ap-avatar { margin-left: -8px; }
        .ap-avatars .ap-avatar { transition: transform .15s; }
        .ap-avatars .ap-avatar:hover { transform: translateY(-3px); z-index: 2; }
        .ap-thumbs { display: inline-flex; gap: 4px; }
        .ap-thumbs img, .ap-thumbs span { width: 34px; height: 34px; border-radius: 8px; object-fit: cover; cursor: zoom-in; border: 1px solid var(--ap-line); transition: transform .15s; }
        .ap-thumbs img:hover { transform: scale(1.12); }
        .ap-thumbs span { display: grid; place-items: center; font-size: 11px; font-weight: 700; background: var(--ap-soft); color: var(--ap-muted); }

        /* Hasil meeting: linimasa */
        .ap-tl { position: relative; padding-left: 74px; }
        .ap-tl::before { content: ""; position: absolute; left: 31px; top: 8px; bottom: 8px; width: 3px; border-radius: 3px; background: linear-gradient(var(--ap-a), var(--ap-b), transparent); opacity: .35; }
        .ap-meet { position: relative; margin-bottom: 16px; animation: ap-rise .5s ease backwards; animation-delay: calc(var(--i) * 80ms); }
        .ap-meet-date { position: absolute; left: -74px; top: 4px; width: 64px; text-align: center; padding: 7px 0 6px; border-radius: 15px; color: #fff; background: linear-gradient(150deg, var(--ap-a), var(--ap-b)); box-shadow: 0 8px 18px rgba(var(--ap-a-rgb), .35); line-height: 1.05; }
        .ap-meet-date b { display: block; font-size: 22px; font-weight: 800; }
        .ap-meet-date span { display: block; font-size: 11.5px; font-weight: 700; text-transform: uppercase; }
        .ap-meet-date small { display: block; font-size: 10.5px; opacity: .85; }
        .ap-meet-card { border: 1px solid var(--ap-line); border-radius: 18px; background: var(--ap-card); box-shadow: var(--ap-shadow); overflow: hidden; transition: box-shadow .2s, border-color .2s; }
        .ap-meet-card:hover { box-shadow: var(--ap-shadow-hover); border-color: rgba(var(--ap-a-rgb), .35); }
        .ap-meet-head { display: flex; align-items: center; gap: 14px; padding: 13px 16px; cursor: pointer; background: linear-gradient(90deg, rgba(var(--ap-a-rgb), .07), transparent 60%); }
        .ap-meet-title { font-weight: 800; font-size: 15.5px; }
        .ap-meet-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin-top: 5px; font-size: 12px; color: var(--ap-muted); }
        .ap-mini-ring { position: relative; flex: none; width: 50px; height: 50px; }
        .ap-mini-ring svg { width: 100%; height: 100%; transform: rotate(-90deg); }
        .ap-mini-ring circle { fill: none; stroke-width: 5; }
        .ap-mini-ring .track { stroke: var(--ap-soft); }
        .ap-mini-ring .value { stroke: var(--s-done); stroke-linecap: round; stroke-dasharray: 100; transition: stroke-dashoffset 1.2s cubic-bezier(.22, .8, .3, 1); }
        .ap-mini-ring b { position: absolute; inset: 0; display: grid; place-items: center; font-size: 11.5px; font-weight: 800; }
        .ap-icon-btn { flex: none; width: 34px; height: 34px; border-radius: 10px; border: 1px solid var(--ap-line); background: var(--ap-card); color: var(--ap-a); transition: transform .15s, background .15s; }
        .ap-icon-btn:hover { background: var(--ap-a); color: #fff; transform: rotate(90deg); }
        .ap-chev { color: var(--ap-muted); transition: transform .3s; }
        .ap-meet.collapsed .ap-chev { transform: rotate(-90deg); }
        .ap-meet-body { display: grid; grid-template-rows: 1fr; transition: grid-template-rows .35s ease; }
        .ap-meet.collapsed .ap-meet-body { grid-template-rows: 0fr; }
        .ap-meet-body > div { overflow: hidden; }
        .ap-carry { font-size: 11px; font-weight: 700; color: #c27803; background: rgba(245, 158, 11, .15); padding: 2px 8px; border-radius: 10px; margin-left: 6px; vertical-align: middle; }

        .ap-point { position: relative; display: flex; align-items: flex-start; gap: 12px; padding: 12px 16px; border-top: 1px solid var(--ap-line); cursor: pointer; transition: background .15s; animation: ap-rise .4s ease backwards; animation-delay: calc(var(--i) * 40ms); }
        .ap-point::before { content: ""; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background: var(--s-open); transform: scaleY(.4); opacity: 0; transition: transform .2s, opacity .2s; }
        .ap-point:hover { background: var(--ap-soft); }
        .ap-point:hover::before { transform: scaleY(1); opacity: 1; }
        .ap-point.s-progress::before { background: var(--s-progress); } .ap-point.s-done::before { background: var(--s-done); } .ap-point.s-batal::before { background: var(--s-batal); }
        .ap-point.is-late::before { background: var(--s-late); opacity: 1; transform: none; }
        .ap-check { flex: none; width: 30px; height: 30px; margin-top: 1px; border-radius: 50%; border: 2px solid var(--ap-line); background: var(--ap-card); color: transparent; font-size: 13px; transition: all .2s; }
        .ap-check:hover { border-color: var(--s-done); color: var(--s-done); transform: scale(1.12); }
        .s-progress .ap-check { border-color: var(--s-progress); border-style: dashed; animation: fa-spin 6s linear infinite; }
        .s-progress .ap-check:hover { animation: none; border-style: solid; }
        .s-done .ap-check { border-color: var(--s-done); background: var(--s-done); color: #fff; }
        .s-batal .ap-check { border-color: var(--s-batal); background: var(--s-batal); color: #fff; }
        .ap-point-body { flex: 1; min-width: 0; }
        .ap-point-text { font-size: 14px; font-weight: 600; line-height: 1.4; white-space: pre-line; word-break: break-word; }
        .s-done .ap-point-text, .s-batal .ap-point-text { color: var(--ap-muted); text-decoration: line-through; text-decoration-color: rgba(127, 127, 127, .5); }
        .ap-point-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 6px 10px; margin-top: 6px; }
        .ap-followup { display: flex; gap: 6px; margin-top: 7px; padding: 6px 10px; border-radius: 10px; background: var(--ap-soft); font-size: 12.5px; color: var(--ap-muted); }
        .ap-followup i { color: var(--ap-a); margin-top: 3px; }
        .ap-point-side { flex: none; display: flex; flex-direction: column; align-items: flex-end; gap: 6px; max-width: 220px; }
        .ap-pic { display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 600; }
        .ap-pic.none { color: var(--ap-muted); font-weight: 500; font-style: italic; }
        .ap-logs { font-size: 11.5px; color: var(--ap-muted); }

        /* Temuan: kartu */
        .ap-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(255px, 1fr)); gap: 16px; }
        .ap-find { position: relative; display: flex; flex-direction: column; border-radius: 20px; background: var(--ap-card); border: 1px solid var(--ap-line); box-shadow: var(--ap-shadow); overflow: hidden; cursor: pointer; animation: ap-rise .5s ease backwards; animation-delay: calc(var(--i) * 60ms); transition: box-shadow .2s, transform .2s ease-out; transform-style: preserve-3d; will-change: transform; }
        .ap-find:hover { box-shadow: var(--ap-shadow-hover); }
        .ap-find.is-late { border-color: rgba(239, 68, 68, .55); animation: ap-rise .5s ease backwards, ap-glow 1.8s ease-in-out infinite; }
        .ap-find-cover { position: relative; height: 165px; overflow: hidden; background: linear-gradient(135deg, rgba(var(--ap-a-rgb), .9), var(--ap-b)); }
        .ap-find-cover img { width: 100%; height: 100%; object-fit: cover; transition: transform .6s ease; }
        .ap-find:hover .ap-find-cover img { transform: scale(1.08); }
        .ap-find-cover::after { content: ""; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(0, 0, 0, .28), transparent 40%, rgba(0, 0, 0, .35)); pointer-events: none; }
        .ap-find-ph { width: 100%; height: 100%; display: grid; place-items: center; color: rgba(255, 255, 255, .85); font-size: 46px; }
        .ap-find-ph i { animation: ap-float 5s ease-in-out infinite; }
        .ap-find-ph small { display: block; margin-top: 4px; font-size: 12px; font-weight: 600; text-align: center; }
        .ap-find-badges { position: absolute; z-index: 1; left: 10px; right: 10px; top: 10px; display: flex; justify-content: space-between; gap: 6px; }
        .ap-find-badges .ap-status { background: rgba(255, 255, 255, .94); }
        .ap-find-prio { padding: 2px 10px; border-radius: 20px; font-size: 11px; font-weight: 800; color: #fff; text-transform: uppercase; letter-spacing: .04em; }
        .ap-find-prio.p-tinggi { background: #ef4444; } .ap-find-prio.p-sedang { background: #f59e0b; } .ap-find-prio.p-rendah { background: #10b981; }
        .ap-find-count { position: absolute; z-index: 1; right: 10px; bottom: 10px; padding: 2px 9px; border-radius: 20px; background: rgba(0, 0, 0, .55); color: #fff; font-size: 11.5px; font-weight: 700; }
        .ap-find-date { position: absolute; z-index: 1; left: 10px; bottom: 10px; color: #fff; font-size: 12px; font-weight: 700; text-shadow: 0 1px 3px rgba(0, 0, 0, .5); }
        .ap-find-body { display: flex; flex-direction: column; gap: 8px; flex: 1; padding: 12px 14px 14px; }
        .ap-find-loc { font-size: 12px; font-weight: 700; color: var(--ap-a); }
        .ap-find-text { font-size: 14px; font-weight: 600; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; word-break: break-word; }
        .ap-find-foot { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-top: auto; }

        /* Stepper Open → Progress → Selesai */
        .ap-steps { position: relative; display: flex; justify-content: space-between; margin: 4px 6px; }
        .ap-steps::before, .ap-steps::after { content: ""; position: absolute; left: 8px; top: 7px; height: 3px; border-radius: 3px; }
        .ap-steps::before { right: 8px; background: var(--ap-line); }
        .ap-steps::after { width: 0; background: linear-gradient(90deg, var(--s-open), var(--s-progress), var(--s-done)); transition: width .9s ease; }
        .ap-steps.s-progress::after { width: calc(50% - 8px); }
        .ap-steps.s-done::after { width: calc(100% - 16px); }
        .ap-steps span { position: relative; z-index: 1; display: flex; flex-direction: column; align-items: center; gap: 3px; font-size: 10.5px; font-weight: 700; color: var(--ap-muted); }
        .ap-steps span::before { content: ""; width: 17px; height: 17px; border-radius: 50%; background: var(--ap-card); border: 3px solid var(--ap-line); transition: all .3s; }
        .ap-steps span.on { color: var(--ap-ink); }
        .ap-steps span.on::before { border-color: var(--c); background: var(--c); box-shadow: 0 0 0 4px color-mix(in srgb, var(--c) 22%, transparent); }
        .ap-steps span.now::before { animation: ap-step-pulse 1.6s ease-in-out infinite; }
        @keyframes ap-step-pulse { 50% { box-shadow: 0 0 0 7px color-mix(in srgb, var(--c) 10%, transparent); } }
        .ap-steps.s-batal span::before { border-color: var(--s-batal); }

        /* Kosong + skeleton */
        .ap-empty { text-align: center; padding: 50px 16px; color: var(--ap-muted); }
        .ap-empty .ic { display: inline-grid; place-items: center; width: 92px; height: 92px; border-radius: 28px; margin-bottom: 14px; font-size: 38px; color: #fff; background: linear-gradient(140deg, var(--ap-a), var(--ap-b)); box-shadow: 0 14px 30px rgba(var(--ap-a-rgb), .35); animation: ap-float 4s ease-in-out infinite; }
        .ap-empty h5 { color: var(--ap-ink); font-weight: 800; }
        .ap-skel { border-radius: 16px; height: 110px; margin-bottom: 12px; background: linear-gradient(90deg, var(--ap-soft) 25%, var(--ap-line) 37%, var(--ap-soft) 63%); background-size: 400% 100%; animation: ap-shimmer 1.3s ease infinite; }
        @keyframes ap-shimmer { from { background-position: 100% 50%; } to { background-position: 0 50%; } }

        /* Modal */
        .ap-modal .modal-content { background: var(--ap-card); color: var(--ap-ink); border: 0; border-radius: 22px; overflow: hidden; box-shadow: 0 30px 70px rgba(0, 0, 0, .3); }
        .ap-modal .modal-header, .ap-modal .modal-footer { border-color: var(--ap-line); }
        .ap-modal.fade .modal-dialog { transform: scale(.9) translateY(20px); transition: transform .35s cubic-bezier(.2, 1.3, .4, 1); }
        .ap-modal.show .modal-dialog { transform: none; }
        html[data-ap-mode="dark"] .ap-modal .btn-close { filter: invert(1); }
        .ap-head-grad { color: #fff; background: linear-gradient(120deg, var(--ap-a), var(--ap-b)); border: 0; }
        .ap-head-grad .btn-close { filter: invert(1); }
        .ap-btn-main { border: 0; color: #fff; font-weight: 700; border-radius: 11px; padding: 7px 18px; background: linear-gradient(120deg, var(--ap-a), var(--ap-b)); box-shadow: 0 6px 16px rgba(var(--ap-a-rgb), .35); transition: transform .15s, box-shadow .15s; }
        .ap-btn-main:hover { color: #fff; transform: translateY(-1px); box-shadow: 0 10px 22px rgba(var(--ap-a-rgb), .45); }
        .ap-btn-main:disabled { opacity: .65; transform: none; }
        .ap-btn-soft { border: 1px solid var(--ap-line); background: var(--ap-soft); color: var(--ap-ink); font-weight: 600; border-radius: 11px; padding: 7px 15px; }
        .ap-btn-soft:hover { border-color: var(--ap-a); color: var(--ap-a); }

        .ap-seg { display: inline-flex; gap: 6px; padding: 5px; border-radius: 14px; background: var(--ap-soft); border: 1px solid var(--ap-line); }
        .ap-seg button { border: 0; background: none; padding: 8px 16px; border-radius: 10px; font-weight: 700; font-size: 13.5px; color: var(--ap-muted); transition: all .2s; }
        .ap-seg button.active { background: var(--ap-card); color: var(--ap-a); box-shadow: 0 4px 12px rgba(0, 0, 0, .1); }
        .ap-fpoint { position: relative; padding: 14px 15px; margin-bottom: 12px; border-radius: 16px; border: 1px solid var(--ap-line); background: var(--ap-soft); animation: ap-pop .3s ease both; }
        .ap-fpoint.removing { animation: ap-out .25s ease forwards; }
        @keyframes ap-out { to { opacity: 0; transform: scale(.92); } }
        .ap-fpoint-head { display: flex; align-items: center; gap: 9px; margin-bottom: 9px; font-weight: 800; }
        .ap-fpoint-no { display: inline-grid; place-items: center; width: 26px; height: 26px; border-radius: 9px; color: #fff; font-size: 12.5px; background: linear-gradient(140deg, var(--ap-a), var(--ap-b)); }
        .ap-fpoint-del { margin-left: auto; border: 0; background: none; color: var(--ap-muted); }
        .ap-fpoint-del:hover { color: #ef4444; }
        .ap-prio-pick { display: flex; gap: 5px; }
        .ap-prio-pick label { flex: 1; margin: 0; }
        .ap-prio-pick input { display: none; }
        .ap-prio-pick span { display: block; text-align: center; padding: 6px 4px; border-radius: 9px; border: 1px solid var(--ap-line); background: var(--ap-input); font-size: 12.5px; font-weight: 700; color: var(--ap-muted); cursor: pointer; transition: all .15s; }
        .ap-prio-pick input:checked + span.p-tinggi { background: #ef4444; border-color: #ef4444; color: #fff; }
        .ap-prio-pick input:checked + span.p-sedang { background: #f59e0b; border-color: #f59e0b; color: #fff; }
        .ap-prio-pick input:checked + span.p-rendah { background: #10b981; border-color: #10b981; color: #fff; }
        .ap-drop { display: inline-flex !important; align-items: center; gap: 7px; margin: 0 !important; padding: 8px 14px; border-radius: 11px; border: 2px dashed var(--ap-line); background: var(--ap-input); color: var(--ap-a) !important; font-size: 13px !important; font-weight: 700 !important; cursor: pointer; transition: all .15s; }
        .ap-drop:hover, .ap-drop.over { border-color: var(--ap-a); background: rgba(var(--ap-a-rgb), .08); }
        .ap-drop input { display: none; }
        .ap-previews { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 9px; }
        .ap-preview { position: relative; width: 74px; height: 74px; border-radius: 12px; overflow: hidden; animation: ap-pop .3s ease both; box-shadow: 0 3px 8px rgba(0, 0, 0, .12); }
        .ap-preview img { width: 100%; height: 100%; object-fit: cover; cursor: zoom-in; }
        .ap-preview button { position: absolute; top: 3px; right: 3px; width: 22px; height: 22px; border: 0; border-radius: 50%; background: rgba(0, 0, 0, .6); color: #fff; font-size: 11px; line-height: 1; }
        .ap-preview button:hover { background: #ef4444; }
        .ap-add-point { width: 100%; padding: 11px; border-radius: 14px; border: 2px dashed rgba(var(--ap-a-rgb), .45); background: rgba(var(--ap-a-rgb), .05); color: var(--ap-a); font-weight: 700; transition: all .15s; }
        .ap-add-point:hover { background: rgba(var(--ap-a-rgb), .12); transform: translateY(-1px); }

        /* Detail */
        .ap-detail-head { position: relative; overflow: hidden; padding: 20px 22px 18px; color: #fff; background: linear-gradient(120deg, var(--hc1), var(--hc2)); }
        .ap-detail-head.s-open { --hc1: #2563eb; --hc2: #38bdf8; } .ap-detail-head.s-progress { --hc1: #d97706; --hc2: #fbbf24; }
        .ap-detail-head.s-done { --hc1: #059669; --hc2: #34d399; } .ap-detail-head.s-batal { --hc1: #64748b; --hc2: #94a3b8; }
        .ap-detail-head.is-late { --hc1: #b91c1c; --hc2: #f87171; }
        .ap-detail-head::after { content: ""; position: absolute; right: -40px; top: -60px; width: 200px; height: 200px; border-radius: 50%; background: rgba(255, 255, 255, .12); }
        .ap-detail-head .btn-close { position: absolute; right: 16px; top: 16px; z-index: 2; filter: invert(1); }
        .ap-detail-head h4 { margin: 6px 0 0; font-weight: 800; font-size: 19px; }
        .ap-detail-head .tag { display: inline-block; padding: 2px 10px; border-radius: 20px; background: rgba(255, 255, 255, .22); font-size: 11.5px; font-weight: 700; }
        .ap-gallery-main { position: relative; height: 240px; border-radius: 16px; overflow: hidden; background: var(--ap-soft); cursor: zoom-in; }
        .ap-gallery-main img { width: 100%; height: 100%; object-fit: cover; animation: ap-pop .3s ease both; }
        .ap-gallery-thumbs { display: flex; gap: 6px; margin-top: 8px; flex-wrap: wrap; }
        .ap-gallery-thumbs img { width: 52px; height: 52px; border-radius: 10px; object-fit: cover; cursor: pointer; opacity: .6; border: 2px solid transparent; transition: all .15s; }
        .ap-gallery-thumbs img.active, .ap-gallery-thumbs img:hover { opacity: 1; border-color: var(--ap-a); }
        .ap-info { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px 14px; margin-top: 12px; }
        .ap-info div small { display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--ap-muted); }
        .ap-info div b { font-size: 13.5px; font-weight: 600; word-break: break-word; }
        .ap-detail-poin { font-size: 16px; font-weight: 700; line-height: 1.45; white-space: pre-line; word-break: break-word; }
        .ap-box { padding: 14px 16px; border-radius: 16px; background: var(--ap-soft); border: 1px solid var(--ap-line); }
        .ap-section-title { font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: var(--ap-muted); margin-bottom: 10px; }
        .ap-status-pick { display: grid; grid-template-columns: repeat(4, 1fr); gap: 7px; }
        .ap-status-pick button { --c: var(--s-open); border: 2px solid var(--ap-line); background: var(--ap-card); color: var(--ap-muted); border-radius: 12px; padding: 8px 4px; font-weight: 700; font-size: 13px; transition: all .18s; }
        .ap-status-pick button i { display: block; font-size: 17px; margin-bottom: 3px; }
        .ap-status-pick button:hover { border-color: var(--c); color: var(--c); transform: translateY(-2px); }
        .ap-status-pick button.active { border-color: var(--c); background: var(--c); color: #fff; box-shadow: 0 6px 14px color-mix(in srgb, var(--c) 40%, transparent); }
        .ap-status-pick [data-status="progress"] { --c: var(--s-progress); } .ap-status-pick [data-status="done"] { --c: var(--s-done); } .ap-status-pick [data-status="batal"] { --c: var(--s-batal); }

        .ap-history { position: relative; padding-left: 26px; }
        .ap-history::before { content: ""; position: absolute; left: 8px; top: 6px; bottom: 6px; width: 2px; background: var(--ap-line); }
        .ap-hist { position: relative; padding-bottom: 14px; animation: ap-rise .35s ease backwards; animation-delay: calc(var(--i) * 50ms); }
        .ap-hist::before { content: ""; position: absolute; left: -23px; top: 4px; width: 14px; height: 14px; border-radius: 50%; background: var(--ap-card); border: 3px solid var(--c, var(--ap-a)); }
        .ap-hist-top { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; font-size: 12px; color: var(--ap-muted); }
        .ap-hist-note { margin-top: 4px; font-size: 13.5px; white-space: pre-line; word-break: break-word; }

        /* Lightbox */
        .ap-lightbox { position: fixed; inset: 0; z-index: 3000; display: flex; align-items: center; justify-content: center; background: rgba(5, 10, 25, .9); animation: ap-fade .2s ease both; }
        @keyframes ap-fade { from { opacity: 0; } }
        .ap-lightbox img { max-width: 92vw; max-height: 86vh; border-radius: 12px; box-shadow: 0 20px 60px rgba(0, 0, 0, .5); animation: ap-pop .3s ease both; }
        .ap-lightbox button { position: absolute; border: 0; width: 46px; height: 46px; border-radius: 50%; background: rgba(255, 255, 255, .14); color: #fff; font-size: 18px; transition: background .15s; }
        .ap-lightbox button:hover { background: rgba(255, 255, 255, .3); }
        .ap-lb-close { top: 18px; right: 18px; } .ap-lb-prev { left: 18px; top: 50%; transform: translateY(-50%); } .ap-lb-next { right: 18px; top: 50%; transform: translateY(-50%); }
        .ap-lb-count { position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); color: #fff; font-size: 13px; font-weight: 600; opacity: .8; }

        #ap-confetti { position: fixed; inset: 0; z-index: 4000; pointer-events: none; }

        @media (max-width: 768px) {
            .ap-hero { padding: 18px; }
            .ap-hero-main { gap: 14px; }
            .ap-ring { width: 92px; height: 92px; }
            .ap-ring-text b { font-size: 22px; }
            .ap-tl { padding-left: 0; }
            .ap-tl::before { display: none; }
            .ap-meet-date { position: static; display: inline-flex; gap: 6px; align-items: baseline; width: auto; padding: 4px 12px; margin-bottom: 6px; }
            .ap-meet-date b { font-size: 15px; }
            .ap-point { flex-wrap: wrap; }
            .ap-point-side { flex-direction: row; flex-wrap: wrap; align-items: center; max-width: none; width: 100%; padding-left: 42px; }
            .ap-status-pick { grid-template-columns: repeat(2, 1fr); }
            .ap-info { grid-template-columns: 1fr; }
        }
        @media (prefers-reduced-motion: reduce) {
            .ap *, .ap-modal *, .ap-hero { animation: none !important; transition: none !important; }
        }
    </style>
@endsection

@section('content')
    <div class="ap" id="ap">
        <div class="ap-hero" id="ap-hero">
            <span class="ap-blob b1"></span><span class="ap-blob b2"></span><span class="ap-blob b3"></span>
            <span class="ap-shine" aria-hidden="true"></span>
            <div class="ap-bubbles" id="ap-bubbles" aria-hidden="true"></div>
            <i class="fa-solid fa-lightbulb ap-float" style="--x: 46%; --y: 14%; --d: 6s; --depth: 18px;" aria-hidden="true"></i>
            <i class="fa-solid fa-clipboard-check ap-float" style="--x: 60%; --y: 56%; --d: 8s; --delay: -2s; --s: 28px; --depth: 26px;" aria-hidden="true"></i>
            <i class="fa-solid fa-magnifying-glass ap-float" style="--x: 76%; --y: 18%; --d: 7s; --delay: -4s; --s: 18px; --depth: 12px;" aria-hidden="true"></i>
            <i class="fa-solid fa-comments ap-float" style="--x: 34%; --y: 72%; --d: 9s; --delay: -1s; --s: 20px; --depth: 20px;" aria-hidden="true"></i>
            <i class="fa-solid fa-star ap-float twinkle" style="--x: 88%; --y: 64%; --d: 5s; --delay: -3s; --s: 12px; --depth: 8px;" aria-hidden="true"></i>
            <i class="fa-solid fa-star ap-float twinkle" style="--x: 53%; --y: 30%; --d: 4s; --delay: -1.5s; --s: 9px; --depth: 6px;" aria-hidden="true"></i>
            <i class="fa-solid fa-thumbtack ap-float" style="--x: 70%; --y: 76%; --d: 10s; --delay: -6s; --s: 16px; --depth: 16px;" aria-hidden="true"></i>
            <svg class="ap-wave" viewBox="0 0 1200 60" preserveAspectRatio="none" aria-hidden="true">
                <path class="w1" d="M0 30 Q 75 10 150 30 T 300 30 T 450 30 T 600 30 T 750 30 T 900 30 T 1050 30 T 1200 30 V60 H0 Z"/>
                <path class="w2" d="M0 38 Q 100 20 200 38 T 400 38 T 600 38 T 800 38 T 1000 38 T 1200 38 V60 H0 Z"/>
            </svg>

            <div class="ap-hero-inner">
                <div class="ap-hero-main">
                    <div class="ap-ring" id="ap-ring-box" role="img" aria-label="Persentase poin tuntas">
                        <span class="ap-ring-orbit" aria-hidden="true"><i></i></span>
                        <svg viewBox="0 0 120 120" aria-hidden="true"><circle class="track" cx="60" cy="60" r="52"/><circle class="value" id="ap-ring" cx="60" cy="60" r="52" pathLength="100"/></svg>
                        <div class="ap-ring-text"><b id="ap-ring-pct">0%</b><span>Tuntas</span></div>
                    </div>
                    <div style="min-width: 0">
                        <div class="ap-greet"><span id="ap-greet"></span> <span class="ap-clock" id="ap-clock"></span></div>
                        <h1 id="ap-title"><i class="fa-solid fa-list-check"></i> <span class="ap-title-text">Action Points</span></h1>
                        <div class="ap-hero-msg" id="ap-hero-msg"><span class="ap-msg-body"><i class="fa-solid fa-spinner fa-spin"></i> Memuat...</span><span class="ap-msg-bar"></span></div>
                    </div>
                </div>
                <div class="ap-hero-actions">
                    <div class="ap-theme-wrap">
                        <button class="ap-btn-ghost" id="ap-theme-btn" type="button"><i class="fa-solid fa-palette"></i> Tema</button>
                        <div class="ap-theme-pop d-none" id="ap-theme-pop">
                            <h6>Mode</h6>
                            <div class="ap-modes" id="ap-modes">
                                <button type="button" data-mode="light"><i class="fa-solid fa-sun"></i>Terang</button>
                                <button type="button" data-mode="dark"><i class="fa-solid fa-moon"></i>Gelap</button>
                                <button type="button" data-mode="auto"><i class="fa-solid fa-clock"></i>Otomatis</button>
                            </div>
                            <h6>Warna</h6>
                            <div class="ap-palettes" id="ap-palettes"></div>
                            <div class="ap-theme-note">Otomatis: gelap mulai jam 18.00 sampai 06.00.</div>
                        </div>
                    </div>
                    <button class="ap-btn-ghost" id="ap-export" type="button"><i class="fa-solid fa-file-excel"></i> Export</button>
                    <button class="ap-btn-light" id="ap-add" type="button"><i class="fa-solid fa-plus"></i> Tambah Poin</button>
                </div>
            </div>
        </div>

        <div class="ap-ticker d-none" id="ap-ticker">
            <div class="ap-ticker-label"><i class="fa-solid fa-bell"></i> Lewat target</div>
            <div class="ap-ticker-track"><div class="ap-ticker-move" id="ap-ticker-move"></div></div>
        </div>

        <div class="ap-tiles" id="ap-tiles"></div>

        <div class="ap-card ap-panel">
            <div class="ap-panel-head">
                <div class="ap-tabs" id="ap-tabs" role="tablist">
                    <span class="ap-tab-ind" id="ap-tab-ind"></span>
                    <button type="button" data-tab="meeting"><i class="fa-solid fa-people-group"></i> Hasil Meeting <span class="ap-tab-count" id="ap-count-meeting">0</span></button>
                    <button type="button" data-tab="temuan"><i class="fa-solid fa-magnifying-glass-location"></i> Temuan <span class="ap-tab-count" id="ap-count-temuan">0</span></button>
                </div>
                <div class="form-check form-switch ap-switch mb-0">
                    <input class="form-check-input" type="checkbox" id="ap-carry" checked>
                    <label class="form-check-label" for="ap-carry">Ikutkan poin belum selesai dari sebelum periode</label>
                </div>
            </div>

            <div class="ap-filters">
                <div><label for="ap-f-from">Dari</label><input type="date" class="form-control" id="ap-f-from"></div>
                <div><label for="ap-f-to">Sampai</label><input type="date" class="form-control" id="ap-f-to"></div>
                <div><label for="ap-f-status">Status</label>
                    <select class="ap-select" id="ap-f-status">
                        <option value="">Semua status</option>
                        <option value="aktif">Belum selesai</option>
                        <option value="open">Open</option>
                        <option value="progress">Progress</option>
                        <option value="done">Selesai</option>
                        <option value="batal">Batal</option>
                        <option value="overdue">Lewat target</option>
                    </select>
                </div>
                <div><label for="ap-f-pic">PIC</label><select class="ap-select" id="ap-f-pic"><option value="">Semua PIC</option></select></div>
                <div class="ap-search"><label for="ap-f-search">Cari</label><i class="fa-solid fa-magnifying-glass" style="top: calc(50% + 11px)"></i><input type="search" class="form-control" id="ap-f-search" placeholder="Poin, judul, peserta..."></div>
            </div>

            <div id="ap-view"></div>
        </div>
    </div>

    {{-- Form tambah / ubah --}}
    <div class="modal fade ap-modal" id="ap-form-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form class="modal-content ap-form" id="ap-form" novalidate>
                <div class="modal-header ap-head-grad">
                    <h5 class="modal-title fw-bold" id="ap-form-title">Tambah Poin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="ap-seg mb-3" id="ap-jenis">
                        <button type="button" data-jenis="meeting"><i class="fa-solid fa-people-group"></i> Hasil Meeting</button>
                        <button type="button" data-jenis="temuan"><i class="fa-solid fa-magnifying-glass-location"></i> Temuan</button>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-3"><label for="ap-i-tanggal" id="ap-l-tanggal">Tanggal meeting</label><input type="date" class="form-control" id="ap-i-tanggal" required></div>
                        <div class="col-md-5"><label for="ap-i-judul" id="ap-l-judul">Judul meeting</label><input class="form-control" id="ap-i-judul" maxlength="150" list="ap-dl-judul" required></div>
                        <div class="col-md-4"><label for="ap-i-bagian">Bagian</label><select id="ap-i-bagian"></select></div>
                        <div class="col-12" id="ap-peserta-box"><label for="ap-i-peserta">Peserta</label><textarea class="form-control" id="ap-i-peserta" rows="2" maxlength="2000" placeholder="Pisahkan dengan koma, contoh: Budi, Sari, Andi"></textarea></div>
                    </div>
                    <datalist id="ap-dl-judul"></datalist>
                    <div id="ap-points"></div>
                    <button type="button" class="ap-add-point" id="ap-add-point"><i class="fa-solid fa-plus"></i> Tambah poin lagi</button>
                </div>
                <div class="modal-footer">
                    <span class="small ap-muted me-auto"><i class="fa-solid fa-keyboard"></i> Ctrl + Enter untuk simpan</span>
                    <button type="button" class="ap-btn-soft" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="ap-btn-main" id="ap-form-save"><i class="fa-solid fa-floppy-disk"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Detail + tindak lanjut --}}
    <div class="modal fade ap-modal" id="ap-detail-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content" id="ap-detail"></div>
        </div>
    </div>

    <canvas id="ap-confetti"></canvas>
@endsection

@section('custom-script')
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        (function () {
            const ROUTES = {
                data: @json(route('data-action-points')),
                exportExcel: @json(route('export-action-points')),
                logs: @json(url('action-points/logs')),
                store: @json(route('store-action-points')),
                update: @json(route('update-action-points')),
                followUp: @json(route('follow-up-action-points')),
                remove: @json(route('delete-action-points')),
            };
            const USER_NAME = @json(auth()->user()->name ?? '');
            const MAX_PHOTOS = {{ \App\Http\Controllers\General\ActionPointController::MAX_PHOTOS }};
            const PALETTES = {
                samudra: { a: '#2563eb', b: '#06b6d4', name: 'Samudra' },
                senja: { a: '#ea580c', b: '#e11d48', name: 'Senja' },
                hutan: { a: '#059669', b: '#65a30d', name: 'Hutan' },
                anggur: { a: '#7c3aed', b: '#db2777', name: 'Anggur' },
                nirwana: { a: '#082149', b: '#1c5bb8', name: 'Nirwana' },
                emas: { a: '#b45309', b: '#eab308', name: 'Emas' },
            };
            const STATUS = {
                open: { label: 'Open', icon: 'fa-circle-dot', color: '#3b82f6' },
                progress: { label: 'Progress', icon: 'fa-gear', color: '#f59e0b' },
                done: { label: 'Selesai', icon: 'fa-circle-check', color: '#10b981' },
                batal: { label: 'Batal', icon: 'fa-ban', color: '#94a3b8' },
            };
            const PRIO = { tinggi: 'Tinggi', sedang: 'Sedang', rendah: 'Rendah' };
            const JENIS = { meeting: 'Hasil Meeting', temuan: 'Temuan' };

            const state = {
                data: null, items: [], tab: 'meeting', loading: false, seq: 0,
                theme: { palette: 'samudra', mode: 'auto' },
                collapsed: {}, counts: {}, ringPct: 0,
                form: null, files: {}, uid: 0,
                detail: null, followStatus: null, followFiles: [],
                lightbox: null,
            };
            const $view = $('#ap-view');

            /* ---------- Util ---------- */
            function esc(value) {
                return $('<div>').text(value === null || value === undefined ? '' : value).html().replace(/"/g, '&quot;').replace(/'/g, '&#39;');
            }
            function parseDate(iso) {
                const p = String(iso).slice(0, 10).split('-').map(Number);
                return new Date(p[0], p[1] - 1, p[2]);
            }
            function iso(date) {
                return date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0');
            }
            function fmt(value, withYear) {
                if (!value) return '-';
                const options = { day: 'numeric', month: 'short' };
                if (withYear) options.year = 'numeric';
                return parseDate(value).toLocaleDateString('id-ID', options);
            }
            function fmtTime(value) {
                if (!value) return '';
                const d = new Date(String(value).replace(' ', 'T'));
                return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) + ' ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            }
            function initials(name) {
                return (name || '?').trim().split(/\s+/).slice(0, 2).map(function (w) { return w[0]; }).join('').toUpperCase();
            }
            function hue(name) {
                let h = 0;
                for (let i = 0; i < (name || '').length; i++) h = (h * 31 + name.charCodeAt(i)) % 360;
                return h;
            }
            function avatar(name, size) {
                const style = '--av: hsl(' + hue(name) + ', 62%, 48%);' + (size ? 'width:' + size + 'px;height:' + size + 'px;' : '');
                return '<span class="ap-avatar" style="' + style + '" title="' + esc(name) + '">' + esc(initials(name)) + '</span>';
            }
            function people(text) {
                return (text || '').split(/[,;\n]+/).map(function (s) { return s.trim(); }).filter(Boolean);
            }
            function store(key, value) {
                try {
                    if (value === undefined) return JSON.parse(localStorage.getItem('ap.' + key));
                    localStorage.setItem('ap.' + key, JSON.stringify(value));
                } catch (e) { return null; }
            }
            function toast(message, type) {
                iziToast[type || 'success']({ message: message, position: 'topCenter', timeout: 2600 });
            }
            function failed(xhr) {
                const json = xhr.responseJSON || {};
                const first = json.errors ? Object.values(json.errors)[0][0] : (json.message || 'Terjadi kesalahan');
                toast(first, 'error');
            }
            function hexRgb(hex) {
                const n = parseInt(hex.slice(1), 16);
                return [(n >> 16) & 255, (n >> 8) & 255, n & 255].join(', ');
            }
            function countUp(el, to) {
                const from = Number(el.dataset.v || 0);
                el.dataset.v = to;
                if (from === to) { el.textContent = to; return; }
                const start = performance.now();
                (function frame(now) {
                    const t = Math.min(1, (now - start) / 800);
                    el.textContent = Math.round(from + (to - from) * (1 - Math.pow(1 - t, 3)));
                    if (t < 1) requestAnimationFrame(frame);
                })(start);
            }

            /* ---------- Tema ---------- */
            function effectiveMode() {
                if (state.theme.mode !== 'auto') return state.theme.mode;
                const h = new Date().getHours();
                return h >= 18 || h < 6 ? 'dark' : 'light';
            }
            function applyTheme() {
                const p = PALETTES[state.theme.palette] || PALETTES.samudra;
                const root = document.documentElement;
                root.style.setProperty('--ap-a', p.a);
                root.style.setProperty('--ap-b', p.b);
                root.style.setProperty('--ap-a-rgb', hexRgb(p.a));
                root.setAttribute('data-ap-mode', effectiveMode());
                $('#ap-modes button').each(function () { $(this).toggleClass('active', this.dataset.mode === state.theme.mode); });
                $('#ap-palettes button').each(function () { $(this).toggleClass('active', this.dataset.palette === state.theme.palette); });
            }
            function buildPalettes() {
                $('#ap-palettes').html(Object.keys(PALETTES).map(function (key) {
                    const p = PALETTES[key];
                    return '<button type="button" data-palette="' + key + '"><span style="background:linear-gradient(120deg,' + p.a + ',' + p.b + ')"></span>' + p.name + '</button>';
                }).join(''));
            }

            /* ---------- Data ---------- */
            function filters() {
                return { from: $('#ap-f-from').val(), to: $('#ap-f-to').val(), carry: $('#ap-carry').is(':checked') ? 1 : 0 };
            }
            function load(quiet) {
                const params = filters();
                if (!params.from || !params.to) return;
                const seq = ++state.seq;
                if (!quiet && !state.data) $view.html('<div class="ap-skel"></div><div class="ap-skel"></div><div class="ap-skel"></div>');
                return $.get(ROUTES.data, params).done(function (res) {
                    if (seq !== state.seq) return;
                    res.items.forEach(function (i) { i.id = Number(i.id); i.log_count = Number(i.log_count); });
                    state.data = res;
                    state.items = res.items;
                    fillSuggestions();
                    renderAll(quiet);
                    if (state.detail) {
                        const fresh = state.items.find(function (i) { return i.id === state.detail.id; });
                        if (fresh) state.detail = fresh;
                    }
                }).fail(failed);
            }
            function fillSuggestions() {
                const s = state.data.suggestions;
                const current = $('#ap-f-pic').val();
                const pics = Array.from(new Set(state.items.map(function (i) { return i.pic; }).filter(Boolean))).sort();
                $('#ap-f-pic').html('<option value="">Semua PIC</option>' + pics.map(function (p) { return '<option>' + esc(p) + '</option>'; }).join(''));
                $('#ap-f-pic').val(pics.indexOf(current) >= 0 ? current : '').trigger('change.select2');
                $('#ap-dl-judul').html(s.judul.map(function (j) { return '<option value="' + esc(j) + '">'; }).join(''));
            }
            function isOverdue(item) { return !!item.overdue; }
            function matches(item) {
                const status = $('#ap-f-status').val();
                const pic = $('#ap-f-pic').val();
                const term = $('#ap-f-search').val().trim().toLowerCase();
                if (status === 'aktif' && ['open', 'progress'].indexOf(item.status) < 0) return false;
                if (status === 'overdue' && !isOverdue(item)) return false;
                if (status && ['aktif', 'overdue'].indexOf(status) < 0 && item.status !== status) return false;
                if (pic && item.pic !== pic) return false;
                if (term) {
                    const hay = [item.poin, item.judul, item.peserta, item.pic, item.kategori, item.bagian, item.tindak_lanjut].join(' ').toLowerCase();
                    if (hay.indexOf(term) < 0) return false;
                }
                return true;
            }
            function ofTab(tab) { return state.items.filter(function (i) { return i.jenis === tab; }); }

            /* ---------- Render ---------- */
            function renderAll(quiet) {
                renderHero();
                renderTicker();
                renderTiles();
                renderTabCounts();
                renderView(quiet);
            }

            function renderHero() {
                const h = new Date().getHours();
                const greet = h < 11 ? 'Selamat pagi' : h < 15 ? 'Selamat siang' : h < 18 ? 'Selamat sore' : 'Selamat malam';
                $('#ap-greet').text(greet + (USER_NAME ? ', ' + USER_NAME : '') + ' · ' + new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }));

                const counted = state.items.filter(function (i) { return i.status !== 'batal'; });
                const done = counted.filter(function (i) { return i.status === 'done'; }).length;
                const pct = counted.length ? Math.round(done / counted.length * 100) : 0;
                document.getElementById('ap-ring').style.strokeDashoffset = 100 - pct;
                const el = document.getElementById('ap-ring-pct');
                const from = state.ringPct;
                state.ringPct = pct;
                if (from !== pct) {
                    const box = document.getElementById('ap-ring-box');
                    box.classList.remove('pop');
                    setTimeout(function () { box.classList.add('pop'); }, 1300);
                }
                const start = performance.now();
                (function frame(now) {
                    const t = Math.min(1, (now - start) / 1400);
                    el.textContent = Math.round(from + (pct - from) * (1 - Math.pow(1 - t, 3))) + '%';
                    if (t < 1) requestAnimationFrame(frame);
                })(start);

                $('#ap-add').toggleClass('nudge', !state.items.length);
                setMessages(heroMessages());
            }

            // Pesan hero bergantian: kondisi utama dulu, lalu ringkasan lain
            function heroMessages() {
                const items = state.items;
                const activeOf = function (i) { return i.status === 'open' || i.status === 'progress'; };
                const late = items.filter(isOverdue).length;
                const active = items.filter(activeOf).length;
                const today = items.filter(function (i) { return activeOf(i) && i.days_left === 0; }).length;
                const week = new Date();
                week.setDate(week.getDate() - 7);
                const weekIso = iso(week);
                const doneWeek = items.filter(function (i) { return i.status === 'done' && i.done_date && i.done_date >= weekIso; }).length;
                const newWeek = items.filter(function (i) { return i.tanggal >= weekIso; }).length;
                const meetings = new Set(ofTab('meeting').map(function (i) { return i.tanggal + '|' + i.judul; })).size;
                const findings = ofTab('temuan').length;
                const next = items.filter(function (i) { return activeOf(i) && i.days_left !== null && i.days_left >= 0; })
                    .sort(function (a, b) { return a.days_left - b.days_left; })[0];
                const msgs = [];

                if (!items.length) msgs.push({ cls: 'good', icon: 'fa-seedling', text: 'Belum ada poin di periode ini. Yuk catat hasil meeting atau temuan pertama!' });
                else if (late) msgs.push({ cls: 'bad', icon: 'fa-fire', text: late + ' poin lewat target, yuk segera ditindaklanjuti' });
                else if (today) msgs.push({ cls: 'warn', icon: 'fa-hourglass-half', text: today + ' poin jatuh tempo hari ini' });
                else if (active) msgs.push({ cls: 'good', icon: 'fa-thumbs-up', text: active + ' poin berjalan, semuanya masih sesuai target' });
                else msgs.push({ cls: 'good', icon: 'fa-trophy', text: 'Semua poin sudah tuntas. Mantap!' });

                if (items.length) {
                    msgs.push({ cls: 'good', icon: 'fa-chart-pie', text: meetings + ' meeting dan ' + findings + ' temuan tercatat di periode ini' });
                    if (doneWeek) msgs.push({ cls: 'good', icon: 'fa-circle-check', text: doneWeek + ' poin diselesaikan dalam 7 hari terakhir' });
                    if (newWeek) msgs.push({ cls: 'good', icon: 'fa-pen-to-square', text: newWeek + ' poin baru dicatat dalam 7 hari terakhir' });
                    if (next) msgs.push({ cls: next.days_left <= 2 ? 'warn' : 'good', icon: 'fa-bullseye', text: 'Target terdekat: ' + (next.poin.length > 50 ? next.poin.slice(0, 50) + '…' : next.poin) + ' (' + (next.days_left === 0 ? 'hari ini' : 'H-' + next.days_left) + ')' });
                }
                msgs.push({ cls: 'good', icon: 'fa-keyboard', text: 'Tips: tekan N untuk menambah poin dengan cepat' });
                return msgs;
            }

            function setMessages(msgs) {
                const key = JSON.stringify(msgs);
                if (state.msgKey === key) return;
                const firstChanged = !state.msgs || JSON.stringify(state.msgs[0]) !== JSON.stringify(msgs[0]);
                state.msgKey = key;
                state.msgs = msgs;
                if (firstChanged || state.msgIdx >= msgs.length) showMessage(0);
            }

            function showMessage(idx) {
                clearTimeout(state.msgTimer);
                state.msgIdx = idx;
                const m = state.msgs[idx];
                const $msg = $('#ap-hero-msg');
                $msg.addClass('flip');
                setTimeout(function () {
                    $msg.attr('class', 'ap-hero-msg ' + m.cls + ' flip');
                    $msg.find('.ap-msg-body').html('<i class="fa-solid ' + m.icon + '"></i> ' + esc(m.text));
                    $msg[0].offsetHeight;
                    $msg.removeClass('flip');
                    const $bar = $msg.find('.ap-msg-bar').removeClass('run');
                    if (state.msgs.length > 1) {
                        const seconds = m.cls === 'bad' ? 9 : 6;
                        $msg[0].style.setProperty('--msg-dur', seconds + 's');
                        $bar[0].offsetWidth;
                        $bar.addClass('run');
                        state.msgTimer = setTimeout(function () { showMessage((state.msgIdx + 1) % state.msgs.length); }, seconds * 1000);
                    }
                }, 300);
            }

            function tickClock() {
                const now = new Date();
                const pad = function (n) { return String(n).padStart(2, '0'); };
                $('#ap-clock').html(pad(now.getHours()) + '<span class="colon">:</span>' + pad(now.getMinutes()) + '<span class="colon">:</span>' + pad(now.getSeconds()));
            }

            function initHeroFx() {
                // Judul muncul per huruf
                const $t = $('#ap-title .ap-title-text');
                $t.html($t.text().split('').map(function (ch, i) {
                    return '<span class="ch' + (ch === ' ' ? ' sp' : '') + '" style="--i:' + i + '">' + (ch === ' ' ? '&nbsp;' : esc(ch)) + '</span>';
                }).join(''));

                // Gelembung naik
                $('#ap-bubbles').html(Array.from({ length: 16 }, function () {
                    const t = 7 + Math.random() * 9;
                    return '<span style="--l:' + (Math.random() * 100).toFixed(1) + '%;--z:' + (4 + Math.random() * 12).toFixed(0) + 'px;--t:' + t.toFixed(1) + 's;--dl:-' + (Math.random() * t).toFixed(1) + 's;--sw:' + ((Math.random() - .5) * 50).toFixed(0) + 'px"></span>';
                }).join(''));

                // Ikon dan gelembung latar ikut bergeser mengikuti kursor
                const hero = document.getElementById('ap-hero');
                $(hero).on('mousemove', function (e) {
                    const r = hero.getBoundingClientRect();
                    hero.style.setProperty('--mx', (((e.clientX - r.left) / r.width) - .5) * 2);
                    hero.style.setProperty('--my', (((e.clientY - r.top) / r.height) - .5) * 2);
                }).on('mouseleave', function () {
                    hero.style.setProperty('--mx', 0);
                    hero.style.setProperty('--my', 0);
                });

                tickClock();
                setInterval(tickClock, 1000);
            }

            // Efek riak saat tombol diklik
            $(document).on('pointerdown', '.ap-btn-light, .ap-btn-ghost, .ap-btn-main, .ap-tabs button, .ap-tile', function (e) {
                const r = this.getBoundingClientRect();
                const size = Math.max(r.width, r.height);
                $('<span class="ap-ripple"></span>').css({ width: size, height: size, left: e.clientX - r.left - size / 2, top: e.clientY - r.top - size / 2 })
                    .appendTo(this).on('animationend', function () { $(this).remove(); });
            });

            function renderTicker() {
                const late = state.items.filter(isOverdue).sort(function (a, b) { return a.days_left - b.days_left; });
                if (!late.length) { $('#ap-ticker').addClass('d-none'); return; }
                const chunk = late.map(function (i) {
                    return '<button type="button" data-open="' + i.id + '"><b>' + esc(i.pic || 'Tanpa PIC') + '</b>' + esc(i.poin.length > 70 ? i.poin.slice(0, 70) + '…' : i.poin) + ' <span style="opacity:.8">· lewat ' + Math.abs(i.days_left) + ' hari</span></button>';
                }).join('');
                $('#ap-ticker-move').html(chunk + chunk).css('--dur', Math.max(18, late.length * 9) + 's');
                $('#ap-ticker').removeClass('d-none');
            }

            function renderTiles() {
                const items = ofTab(state.tab);
                const total = items.length;
                const count = function (fn) { return items.filter(fn).length; };
                const tiles = [
                    { key: '', label: 'Total poin', icon: 'fa-layer-group', color: 'var(--ap-a)', value: total },
                    { key: 'open', label: 'Open', icon: 'fa-circle-dot', color: STATUS.open.color, value: count(function (i) { return i.status === 'open'; }) },
                    { key: 'progress', label: 'Progress', icon: 'fa-gear', color: STATUS.progress.color, value: count(function (i) { return i.status === 'progress'; }) },
                    { key: 'done', label: 'Selesai', icon: 'fa-circle-check', color: STATUS.done.color, value: count(function (i) { return i.status === 'done'; }) },
                    { key: 'overdue', label: 'Lewat target', icon: 'fa-fire', color: '#ef4444', value: count(isOverdue) },
                ];
                const active = $('#ap-f-status').val();
                const $tiles = $('#ap-tiles');
                if (!$tiles.children().length) {
                    $tiles.html(tiles.map(function (t, i) {
                        return '<button type="button" class="ap-tile" data-key="' + t.key + '" style="--c:' + t.color + ';--i:' + i + '">' +
                            '<div class="ap-tile-top"><div class="ap-tile-value" data-v="0">0</div><div class="ap-tile-icon"><i class="fa-solid ' + t.icon + '"></i></div></div>' +
                            '<div class="ap-tile-label">' + t.label + '</div><div class="ap-tile-bar"><span></span></div></button>';
                    }).join(''));
                }
                tiles.forEach(function (t) {
                    const $t = $tiles.find('[data-key="' + t.key + '"]');
                    countUp($t.find('.ap-tile-value')[0], t.value);
                    $t.find('.ap-tile-bar span').css('width', (total ? (t.key ? t.value / total * 100 : 100) : 0) + '%');
                    $t.toggleClass('active', active === t.key && (t.key !== '' || active === ''));
                    $t.toggleClass('alert', t.key === 'overdue' && t.value > 0);
                });
            }

            function renderTabCounts() {
                ['meeting', 'temuan'].forEach(function (tab) {
                    const n = ofTab(tab).filter(function (i) { return i.status === 'open' || i.status === 'progress'; }).length;
                    const $c = $('#ap-count-' + tab);
                    if (state.counts[tab] !== undefined && state.counts[tab] !== n) {
                        $c.removeClass('bump');
                        $c[0].offsetWidth;
                        $c.addClass('bump');
                    }
                    state.counts[tab] = n;
                    $c.text(n).attr('title', n + ' poin belum selesai');
                });
            }

            function moveTabIndicator() {
                const btn = $('#ap-tabs button[data-tab="' + state.tab + '"]')[0];
                if (!btn) return;
                $('#ap-tabs button').removeClass('active');
                btn.classList.add('active');
                $('#ap-tab-ind').css({ width: btn.offsetWidth + 'px', transform: 'translateX(' + btn.offsetLeft + 'px)' });
            }

            function renderView(quiet) {
                const items = ofTab(state.tab).filter(matches);
                const html = state.tab === 'meeting' ? meetingView(items) : findingView(items);
                $view.html('<div class="ap-view" style="--from:' + (quiet ? '0' : (state.tab === 'meeting' ? '-14px' : '14px')) + '">' + html + '</div>');
                if (quiet) $view.find('.ap-meet, .ap-find, .ap-point').css('animation', 'none');
                requestAnimationFrame(function () {
                    $view.find('.ap-mini-ring .value').each(function () { this.style.strokeDashoffset = this.dataset.off; });
                });
            }

            function emptyView() {
                const meeting = state.tab === 'meeting';
                const filtered = ofTab(state.tab).length > 0;
                return '<div class="ap-empty"><div class="ic"><i class="fa-solid ' + (filtered ? 'fa-filter-circle-xmark' : (meeting ? 'fa-people-group' : 'fa-magnifying-glass-location')) + '"></i></div>' +
                    '<h5>' + (filtered ? 'Tidak ada yang cocok dengan filter' : (meeting ? 'Belum ada hasil meeting' : 'Belum ada temuan')) + '</h5>' +
                    '<p class="mb-3">' + (filtered ? 'Coba ubah status, PIC, atau kata kunci pencarian.' : 'Catat poin-poinnya di sini supaya tindak lanjutnya bisa dipantau bersama.') + '</p>' +
                    (filtered ? '<button class="ap-btn-soft" data-reset>Reset filter</button>' : '<button class="ap-btn-main" data-new><i class="fa-solid fa-plus"></i> ' + (meeting ? 'Catat hasil meeting' : 'Catat temuan') + '</button>') + '</div>';
            }

            function due(item) {
                if (item.status === 'done') return { cls: 'ok', icon: 'fa-flag-checkered', text: 'Selesai ' + fmt(item.done_date) };
                if (item.status === 'batal') return { cls: 'mute', icon: 'fa-ban', text: 'Dibatalkan' };
                if (item.days_left === null || item.days_left === undefined) return { cls: 'mute', icon: 'fa-calendar', text: 'Tanpa target' };
                if (item.days_left < 0) return { cls: 'late', icon: 'fa-fire', text: 'Lewat ' + Math.abs(item.days_left) + ' hari' };
                if (item.days_left === 0) return { cls: 'warn', icon: 'fa-hourglass-half', text: 'Hari ini' };
                if (item.days_left <= 2) return { cls: 'warn', icon: 'fa-hourglass-start', text: 'H-' + item.days_left + ' · ' + fmt(item.target_date) };
                return { cls: 'calm', icon: 'fa-calendar-day', text: 'H-' + item.days_left + ' · ' + fmt(item.target_date) };
            }
            function dueChip(item) {
                const d = due(item);
                return '<span class="ap-due ' + d.cls + '" title="Target: ' + esc(fmt(item.target_date, true)) + '"><i class="fa-solid ' + d.icon + '"></i> ' + esc(d.text) + '</span>';
            }
            function statusChip(status) {
                const s = STATUS[status];
                return '<span class="ap-status s-' + status + '"><i class="fa-solid ' + s.icon + '"></i> ' + s.label + '</span>';
            }
            function picChip(name) {
                return name ? '<span class="ap-pic">' + avatar(name, 26) + esc(name) + '</span>' : '<span class="ap-pic none"><i class="fa-regular fa-user"></i> Belum ada PIC</span>';
            }
            function thumbs(item, max) {
                const urls = item.foto_urls || [];
                if (!urls.length) return '';
                const shown = urls.slice(0, max);
                return '<span class="ap-thumbs">' + shown.map(function (u, i) {
                    return '<img src="' + esc(u) + '" loading="lazy" alt="Foto" data-lb="' + item.id + '" data-idx="' + i + '">';
                }).join('') + (urls.length > max ? '<span data-lb="' + item.id + '" data-idx="' + max + '">+' + (urls.length - max) + '</span>' : '') + '</span>';
            }

            function meetingView(items) {
                if (!items.length) return emptyView();
                const groups = [];
                const map = {};
                items.forEach(function (item) {
                    const key = item.tanggal + '|' + item.judul + '|' + (item.peserta || '');
                    if (!map[key]) { map[key] = { key: key, tanggal: item.tanggal, judul: item.judul, peserta: item.peserta, bagian: item.bagian, items: [] }; groups.push(map[key]); }
                    map[key].items.push(item);
                });
                const from = $('#ap-f-from').val();
                const all = ofTab('meeting');

                return '<div class="ap-tl">' + groups.map(function (g, gi) {
                    const whole = all.filter(function (i) { return i.tanggal + '|' + i.judul + '|' + (i.peserta || '') === g.key && i.status !== 'batal'; });
                    const done = whole.filter(function (i) { return i.status === 'done'; }).length;
                    const pct = whole.length ? Math.round(done / whole.length * 100) : 0;
                    const ppl = people(g.peserta);
                    const d = parseDate(g.tanggal);
                    const collapsed = state.collapsed[g.key];
                    const carry = g.tanggal < from;

                    return '<div class="ap-meet' + (collapsed ? ' collapsed' : '') + '" style="--i:' + Math.min(gi, 10) + '">' +
                        '<div class="ap-meet-date"><b>' + d.getDate() + '</b><span>' + d.toLocaleDateString('id-ID', { month: 'short' }) + '</span><small>' + d.toLocaleDateString('id-ID', { weekday: 'short' }) + ' ' + String(d.getFullYear()).slice(2) + '</small></div>' +
                        '<div class="ap-meet-card">' +
                            '<div class="ap-meet-head" data-group="' + esc(g.key) + '">' +
                                '<div style="flex:1;min-width:0">' +
                                    '<div class="ap-meet-title">' + esc(g.judul) + (carry ? '<span class="ap-carry">Dari periode sebelumnya</span>' : '') + '</div>' +
                                    '<div class="ap-meet-meta">' +
                                        (g.bagian ? '<span class="ap-chip"><i class="fa-solid fa-building"></i> ' + esc(g.bagian) + '</span>' : '') +
                                        (ppl.length ? '<span class="ap-avatars" title="' + esc(ppl.join(', ')) + '">' + ppl.slice(0, 6).map(function (p) { return avatar(p, 26); }).join('') + '</span><span>' + ppl.length + ' peserta</span>' : '') +
                                        '<span><i class="fa-solid fa-list-ul"></i> ' + g.items.length + ' poin</span>' +
                                    '</div>' +
                                '</div>' +
                                '<div class="ap-mini-ring" title="' + done + ' dari ' + whole.length + ' poin selesai"><svg viewBox="0 0 50 50"><circle class="track" cx="25" cy="25" r="21"/><circle class="value" cx="25" cy="25" r="21" pathLength="100" style="stroke-dashoffset:100" data-off="' + (100 - pct) + '"/></svg><b>' + pct + '%</b></div>' +
                                '<button type="button" class="ap-icon-btn" data-add-to="' + esc(g.key) + '" title="Tambah poin ke meeting ini"><i class="fa-solid fa-plus"></i></button>' +
                                '<i class="fa-solid fa-chevron-down ap-chev"></i>' +
                            '</div>' +
                            '<div class="ap-meet-body"><div>' + g.items.map(pointRow).join('') + '</div></div>' +
                        '</div>' +
                    '</div>';
                }).join('') + '</div>';
            }

            function pointRow(item, i) {
                const late = isOverdue(item);
                return '<div class="ap-point s-' + item.status + (late ? ' is-late' : '') + '" data-open="' + item.id + '" style="--i:' + Math.min(i, 12) + '">' +
                    '<button type="button" class="ap-check" data-quick="' + item.id + '" title="' + (item.status === 'done' ? 'Sudah selesai' : 'Tandai selesai') + '"><i class="fa-solid ' + (item.status === 'batal' ? 'fa-ban' : 'fa-check') + '"></i></button>' +
                    '<div class="ap-point-body">' +
                        '<div class="ap-point-text">' + esc(item.poin) + '</div>' +
                        '<div class="ap-point-meta">' +
                            statusChip(item.status) +
                            '<span class="ap-prio p-' + item.prioritas + '"><i class="fa-solid fa-circle"></i> ' + PRIO[item.prioritas] + '</span>' +
                            (item.kategori ? '<span class="ap-chip"><i class="fa-solid fa-tag"></i> ' + esc(item.kategori) + '</span>' : '') +
                            dueChip(item) +
                        '</div>' +
                        (item.tindak_lanjut ? '<div class="ap-followup"><i class="fa-solid fa-reply"></i><span>' + esc(item.tindak_lanjut.length > 160 ? item.tindak_lanjut.slice(0, 160) + '…' : item.tindak_lanjut) + '</span></div>' : '') +
                    '</div>' +
                    '<div class="ap-point-side">' +
                        picChip(item.pic) +
                        thumbs(item, 3) +
                        (item.log_count > 1 ? '<span class="ap-logs"><i class="fa-solid fa-clock-rotate-left"></i> ' + item.log_count + ' riwayat</span>' : '') +
                    '</div>' +
                '</div>';
            }

            function findingView(items) {
                if (!items.length) return emptyView();
                return '<div class="ap-grid">' + items.map(function (item, i) {
                    const urls = item.foto_urls || [];
                    const late = isOverdue(item);
                    const steps = ['open', 'progress', 'done'];
                    const reached = steps.indexOf(item.status);
                    return '<div class="ap-find s-' + item.status + (late ? ' is-late' : '') + '" data-open="' + item.id + '" data-tilt style="--i:' + Math.min(i, 14) + '">' +
                        '<div class="ap-find-cover">' +
                            (urls.length ? '<img src="' + esc(urls[0]) + '" loading="lazy" alt="Foto temuan">' : '<div class="ap-find-ph"><div><i class="fa-solid fa-camera-retro"></i><small>Tanpa foto</small></div></div>') +
                            '<div class="ap-find-badges"><span class="ap-find-prio p-' + item.prioritas + '">' + PRIO[item.prioritas] + '</span>' + statusChip(item.status) + '</div>' +
                            '<span class="ap-find-date"><i class="fa-regular fa-calendar"></i> ' + fmt(item.tanggal, true) + '</span>' +
                            (urls.length > 1 ? '<span class="ap-find-count"><i class="fa-solid fa-images"></i> ' + urls.length + '</span>' : '') +
                        '</div>' +
                        '<div class="ap-find-body">' +
                            '<div class="ap-find-loc"><i class="fa-solid fa-location-dot"></i> ' + esc(item.judul) + (item.bagian ? ' · ' + esc(item.bagian) : '') + '</div>' +
                            '<div class="ap-find-text">' + esc(item.poin) + '</div>' +
                            (item.status === 'batal' ? '' : '<div class="ap-steps s-' + item.status + '">' + steps.map(function (s, si) {
                                return '<span class="' + (si <= reached ? 'on' : '') + (si === reached && s !== 'done' ? ' now' : '') + '" style="--c:' + STATUS[s].color + '">' + STATUS[s].label + '</span>';
                            }).join('') + '</div>') +
                            '<div class="ap-find-foot">' + picChip(item.pic) + dueChip(item) + '</div>' +
                        '</div>' +
                    '</div>';
                }).join('') + '</div>';
            }

            /* ---------- Detail + tindak lanjut ---------- */
            function openDetail(id) {
                const item = state.items.find(function (i) { return i.id === Number(id); });
                if (!item) return;
                state.detail = item;
                state.followStatus = item.status;
                clearFollowFiles();
                renderDetail();
                bootstrap.Modal.getOrCreateInstance(document.getElementById('ap-detail-modal')).show();
                loadLogs(item.id);
            }

            function renderDetail() {
                const item = state.detail;
                const urls = item.foto_urls || [];
                const late = isOverdue(item);
                const ppl = people(item.peserta);
                const steps = ['open', 'progress', 'done'];
                const reached = steps.indexOf(item.status);

                $('#ap-detail').html(
                    '<div class="ap-detail-head s-' + item.status + (late ? ' is-late' : '') + '">' +
                        '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>' +
                        '<div class="d-flex flex-wrap gap-2 position-relative" style="z-index:1">' +
                            '<span class="tag"><i class="fa-solid ' + (item.jenis === 'meeting' ? 'fa-people-group' : 'fa-magnifying-glass-location') + '"></i> ' + JENIS[item.jenis] + '</span>' +
                            '<span class="tag"><i class="fa-solid ' + STATUS[item.status].icon + '"></i> ' + STATUS[item.status].label + '</span>' +
                            (late ? '<span class="tag"><i class="fa-solid fa-fire"></i> Lewat ' + Math.abs(item.days_left) + ' hari</span>' : '') +
                        '</div>' +
                        '<h4 class="position-relative" style="z-index:1">' + esc(item.judul) + '</h4>' +
                        '<div class="small position-relative" style="z-index:1;opacity:.9"><i class="fa-regular fa-calendar"></i> ' + fmt(item.tanggal, true) + (item.bagian ? ' · ' + esc(item.bagian) : '') + '</div>' +
                    '</div>' +
                    '<div class="modal-body">' +
                        '<div class="row g-4">' +
                            '<div class="col-lg-5">' +
                                (urls.length
                                    ? '<div class="ap-gallery-main" data-lb="' + item.id + '" data-idx="0"><img src="' + esc(urls[0]) + '" alt="Foto"></div>' +
                                      (urls.length > 1 ? '<div class="ap-gallery-thumbs">' + urls.map(function (u, i) { return '<img src="' + esc(u) + '" data-gidx="' + i + '" class="' + (i === 0 ? 'active' : '') + '" alt="Foto ' + (i + 1) + '">'; }).join('') + '</div>' : '')
                                    : '<div class="ap-gallery-main d-grid" style="place-items:center;cursor:default"><div class="text-center ap-muted"><i class="fa-solid fa-image fa-3x mb-2" style="opacity:.4"></i><div class="small">Belum ada foto</div></div></div>') +
                                (item.status !== 'batal' ? '<div class="ap-steps s-' + item.status + ' mt-4 mb-2">' + steps.map(function (s, si) {
                                    return '<span class="' + (si <= reached ? 'on' : '') + (si === reached && s !== 'done' ? ' now' : '') + '" style="--c:' + STATUS[s].color + '">' + STATUS[s].label + '</span>';
                                }).join('') + '</div>' : '') +
                            '</div>' +
                            '<div class="col-lg-7">' +
                                '<div class="ap-detail-poin">' + esc(item.poin) + '</div>' +
                                '<div class="d-flex flex-wrap gap-2 mt-2">' +
                                    '<span class="ap-prio p-' + item.prioritas + '"><i class="fa-solid fa-circle"></i> Prioritas ' + PRIO[item.prioritas] + '</span>' +
                                    (item.kategori ? '<span class="ap-chip"><i class="fa-solid fa-tag"></i> ' + esc(item.kategori) + '</span>' : '') +
                                    dueChip(item) +
                                '</div>' +
                                '<div class="ap-info">' +
                                    '<div><small>PIC</small><b>' + picChip(item.pic) + '</b></div>' +
                                    '<div><small>Target</small><b>' + fmt(item.target_date, true) + '</b></div>' +
                                    (item.jenis === 'meeting' ? '<div style="grid-column:1/-1"><small>Peserta</small><b>' + (ppl.length ? '<span class="ap-avatars me-2">' + ppl.slice(0, 8).map(function (p) { return avatar(p, 26); }).join('') + '</span>' + esc(ppl.join(', ')) : '-') + '</b></div>' : '') +
                                    '<div><small>Dicatat oleh</small><b>' + esc(item.created_by || '-') + '</b></div>' +
                                    '<div><small>Selesai</small><b>' + (item.done_date ? fmt(item.done_date, true) : '-') + '</b></div>' +
                                '</div>' +
                                (item.tindak_lanjut ? '<div class="ap-followup mt-3"><i class="fa-solid fa-reply"></i><span><b>Tindak lanjut terakhir:</b> ' + esc(item.tindak_lanjut) + '</span></div>' : '') +
                            '</div>' +
                        '</div>' +

                        '<div class="row g-4 mt-1">' +
                            '<div class="col-lg-6">' +
                                '<div class="ap-box">' +
                                    '<div class="ap-section-title"><i class="fa-solid fa-person-walking-arrow-right"></i> Update tindak lanjut</div>' +
                                    '<div class="ap-status-pick mb-3" id="ap-status-pick">' + Object.keys(STATUS).map(function (key) {
                                        return '<button type="button" data-status="' + key + '" class="' + (state.followStatus === key ? 'active' : '') + '"><i class="fa-solid ' + STATUS[key].icon + '"></i>' + STATUS[key].label + '</button>';
                                    }).join('') + '</div>' +
                                    '<textarea class="form-control mb-2" id="ap-follow-note" rows="3" maxlength="3000" placeholder="Apa yang sudah dilakukan? (wajib kalau dibatalkan)"></textarea>' +
                                    '<label class="ap-drop" id="ap-follow-drop"><input type="file" accept="image/*" multiple id="ap-follow-file"><i class="fa-solid fa-camera"></i> Foto tindak lanjut</label>' +
                                    '<div class="ap-previews" id="ap-follow-previews"></div>' +
                                    '<div class="text-end mt-3"><button type="button" class="ap-btn-main" id="ap-follow-save"><i class="fa-solid fa-paper-plane"></i> Simpan tindak lanjut</button></div>' +
                                '</div>' +
                            '</div>' +
                            '<div class="col-lg-6">' +
                                '<div class="ap-section-title"><i class="fa-solid fa-clock-rotate-left"></i> Riwayat</div>' +
                                '<div class="ap-history" id="ap-history"><div class="ap-skel" style="height:60px"></div><div class="ap-skel" style="height:60px"></div></div>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="modal-footer">' +
                        '<button type="button" class="btn btn-outline-danger btn-sm me-auto" id="ap-delete" style="border-radius:10px"><i class="fa-solid fa-trash"></i> Hapus</button>' +
                        '<button type="button" class="ap-btn-soft" id="ap-edit"><i class="fa-solid fa-pen"></i> Ubah poin</button>' +
                    '</div>'
                );
            }

            function loadLogs(id) {
                $.get(ROUTES.logs + '/' + id).done(function (logs) {
                    if (!state.detail || state.detail.id !== id) return;
                    if (!logs.length) { $('#ap-history').html('<div class="ap-muted small">Belum ada riwayat.</div>'); return; }
                    $('#ap-history').html(logs.map(function (log, i) {
                        const to = STATUS[log.status_to];
                        const changed = log.status_from !== log.status_to && to;
                        const urls = log.foto_urls || [];
                        return '<div class="ap-hist" style="--i:' + Math.min(i, 10) + ';--c:' + (to ? to.color : 'var(--ap-a)') + '">' +
                            '<div class="ap-hist-top">' + avatar(log.created_name || log.created_by || '?', 22) + '<b style="color:var(--ap-ink)">' + esc(log.created_name || log.created_by || '-') + '</b><span>' + fmtTime(log.created_at) + '</span>' +
                                (changed ? (log.status_from ? statusChip(log.status_from) + '<i class="fa-solid fa-arrow-right"></i>' : '') + statusChip(log.status_to) : '') + '</div>' +
                            (log.catatan ? '<div class="ap-hist-note">' + esc(log.catatan) + '</div>' : '') +
                            (urls.length ? '<div class="ap-thumbs mt-2">' + urls.map(function (u, ui) { return '<img src="' + esc(u) + '" alt="Foto" data-lb-urls="' + esc(JSON.stringify(urls)) + '" data-idx="' + ui + '">'; }).join('') + '</div>' : '') +
                        '</div>';
                    }).join(''));
                }).fail(failed);
            }

            function clearFollowFiles() {
                state.followFiles.forEach(function (f) { URL.revokeObjectURL(f.url); });
                state.followFiles = [];
            }
            function renderFollowPreviews() {
                $('#ap-follow-previews').html(state.followFiles.map(function (f, i) {
                    return '<div class="ap-preview"><img src="' + f.url + '" data-lb-urls="' + esc(JSON.stringify(state.followFiles.map(function (x) { return x.url; }))) + '" data-idx="' + i + '" alt="Foto"><button type="button" data-follow-rm="' + i + '" aria-label="Hapus foto">&times;</button></div>';
                }).join(''));
            }

            function sendFollowUp(id, status, note, files) {
                const fd = new FormData();
                fd.append('id', id);
                fd.append('status', status);
                if (note) fd.append('catatan', note);
                return compressAll(files).then(function (blobs) {
                    blobs.forEach(function (b) { fd.append('foto[]', b, b.name); });
                    return $.ajax({ url: ROUTES.followUp, type: 'POST', data: fd, processData: false, contentType: false });
                }).then(function (res) {
                    toast(res.message);
                    if (res.just_done) confetti();
                    return load(true);
                });
            }

            function quickDone(id) {
                const item = state.items.find(function (i) { return i.id === Number(id); });
                if (!item) return;
                if (item.status === 'done' || item.status === 'batal') { openDetail(id); return; }
                Swal.fire({
                    title: 'Tandai selesai?',
                    html: '<div class="text-start small mb-2" style="opacity:.8">' + esc(item.poin.length > 140 ? item.poin.slice(0, 140) + '…' : item.poin) + '</div>',
                    input: 'textarea',
                    inputPlaceholder: 'Catatan tindak lanjut (opsional)',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fa-solid fa-check"></i> Selesai',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#10b981',
                    background: getComputedStyle(document.documentElement).getPropertyValue('--ap-card'),
                    color: getComputedStyle(document.documentElement).getPropertyValue('--ap-ink'),
                }).then(function (r) {
                    if (!r.isConfirmed) return;
                    sendFollowUp(item.id, 'done', (r.value || '').trim(), []).catch(failed);
                });
            }

            /* ---------- Form tambah / ubah ---------- */
            function setJenis(jenis) {
                state.form.jenis = jenis;
                $('#ap-jenis button').each(function () { $(this).toggleClass('active', this.dataset.jenis === jenis); });
                const meeting = jenis === 'meeting';
                $('#ap-l-tanggal').text(meeting ? 'Tanggal meeting' : 'Tanggal ditemukan');
                $('#ap-l-judul').text(meeting ? 'Judul meeting' : 'Lokasi / area temuan');
                $('#ap-i-judul').attr('placeholder', meeting ? 'Contoh: Meeting Produksi Mingguan' : 'Contoh: Gudang Cutting');
                $('#ap-peserta-box').toggleClass('d-none', !meeting);
                $('#ap-points textarea[data-f="poin"]').attr('placeholder', meeting ? 'Isi poin / keputusan meeting' : 'Apa yang ditemukan?');
            }

            function tagSelect($el, list, placeholder, parent) {
                if ($el.hasClass('select2-hidden-accessible')) $el.select2('destroy');
                $el.html('<option value=""></option>' + list.map(function (v) { return '<option>' + esc(v) + '</option>'; }).join(''));
                $el.select2({ theme: 'bootstrap4', width: '100%', tags: true, allowClear: true, placeholder: placeholder, dropdownParent: parent });
            }
            function setTag($el, value) {
                if (value && !$el.find('option').filter(function () { return this.value === value; }).length) $el.append(new Option(value, value));
                $el.val(value || '').trigger('change');
            }

            function addPointCard(data) {
                const uid = ++state.uid;
                const meeting = state.form.jenis === 'meeting';
                state.files[uid] = [];
                const $card = $(
                    '<div class="ap-fpoint" data-uid="' + uid + '">' +
                        '<div class="ap-fpoint-head"><span class="ap-fpoint-no"></span> Poin' + (state.form.mode === 'create' ? '<button type="button" class="ap-fpoint-del" title="Hapus poin ini"><i class="fa-solid fa-xmark"></i></button>' : '') + '</div>' +
                        '<textarea class="form-control mb-2" data-f="poin" rows="2" maxlength="5000" placeholder="' + (meeting ? 'Isi poin / keputusan meeting' : 'Apa yang ditemukan?') + '"></textarea>' +
                        '<div class="row g-2">' +
                            '<div class="col-md-4"><label>PIC</label><select data-f="pic"></select></div>' +
                            '<div class="col-md-3"><label>Target</label><input type="date" class="form-control" data-f="target_date"></div>' +
                            '<div class="col-md-5"><label>Prioritas</label><div class="ap-prio-pick">' + Object.keys(PRIO).map(function (p) {
                                return '<label><input type="radio" name="prio-' + uid + '" value="' + p + '"' + (p === 'sedang' ? ' checked' : '') + '><span class="p-' + p + '">' + PRIO[p] + '</span></label>';
                            }).join('') + '</div></div>' +
                            '<div class="col-md-4"><label>Kategori</label><select data-f="kategori"></select></div>' +
                            '<div class="col-md-8 d-flex align-items-end"><label class="ap-drop"><input type="file" accept="image/*" multiple data-files="' + uid + '"><i class="fa-solid fa-camera"></i> Tambah foto <span class="ap-muted fw-normal">(maks ' + MAX_PHOTOS + ')</span></label></div>' +
                        '</div>' +
                        '<div class="ap-previews" data-previews="' + uid + '"></div>' +
                    '</div>'
                );
                $('#ap-points').append($card);
                const parent = $('#ap-form-modal');
                const s = state.data ? state.data.suggestions : { pic: [], kategori: [] };
                tagSelect($card.find('[data-f="pic"]'), s.pic, 'Ketik nama PIC', parent);
                tagSelect($card.find('[data-f="kategori"]'), s.kategori, 'Contoh: Kualitas', parent);
                if (data) {
                    $card.find('[data-f="poin"]').val(data.poin);
                    $card.find('[data-f="target_date"]').val(data.target_date || '');
                    $card.find('input[value="' + data.prioritas + '"]').prop('checked', true);
                    setTag($card.find('[data-f="pic"]'), data.pic);
                    setTag($card.find('[data-f="kategori"]'), data.kategori);
                }
                numberCards();
                return $card;
            }
            function numberCards() {
                const $cards = $('#ap-points .ap-fpoint');
                $cards.each(function (i) { $(this).find('.ap-fpoint-no').text(i + 1); });
                $cards.find('.ap-fpoint-del').toggle($cards.length > 1);
            }
            function renderPreviews(uid) {
                const keep = state.form.mode === 'edit' ? state.form.keep : [];
                const files = state.files[uid];
                const all = keep.map(function (k) { return k.url; }).concat(files.map(function (f) { return f.url; }));
                const urlsAttr = esc(JSON.stringify(all));
                $('[data-previews="' + uid + '"]').html(
                    keep.map(function (k, i) { return '<div class="ap-preview"><img src="' + esc(k.url) + '" data-lb-urls="' + urlsAttr + '" data-idx="' + i + '" alt="Foto"><button type="button" data-keep-rm="' + i + '" aria-label="Hapus foto">&times;</button></div>'; }).join('') +
                    files.map(function (f, i) { return '<div class="ap-preview"><img src="' + f.url + '" data-lb-urls="' + urlsAttr + '" data-idx="' + (keep.length + i) + '" alt="Foto"><button type="button" data-file-rm="' + uid + ':' + i + '" aria-label="Hapus foto">&times;</button></div>'; }).join('')
                );
            }
            function addFiles(uid, fileList) {
                const keep = state.form.mode === 'edit' ? state.form.keep.length : 0;
                const room = MAX_PHOTOS - keep - state.files[uid].length;
                const images = Array.from(fileList).filter(function (f) { return /^image\//.test(f.type); });
                if (images.length > room) toast('Maksimal ' + MAX_PHOTOS + ' foto per poin', 'warning');
                images.slice(0, Math.max(0, room)).forEach(function (f) { state.files[uid].push({ file: f, url: URL.createObjectURL(f) }); });
                renderPreviews(uid);
            }

            function resetForm() {
                $('#ap-points .ap-fpoint select').each(function () { if ($(this).hasClass('select2-hidden-accessible')) $(this).select2('destroy'); });
                Object.keys(state.files).forEach(function (uid) { state.files[uid].forEach(function (f) { URL.revokeObjectURL(f.url); }); });
                state.files = {};
                $('#ap-points').empty();
            }

            function openForm(mode, preset) {
                resetForm();
                state.form = { mode: mode, jenis: (preset && preset.jenis) || state.tab, id: preset && preset.id, keep: [] };
                $('#ap-form-title').html(mode === 'edit' ? '<i class="fa-solid fa-pen"></i> Ubah Poin' : '<i class="fa-solid fa-plus"></i> Tambah Poin');
                $('#ap-i-tanggal').val((preset && preset.tanggal) || iso(new Date()));
                $('#ap-i-judul').val((preset && preset.judul) || '');
                $('#ap-i-peserta').val((preset && preset.peserta) || '');
                tagSelect($('#ap-i-bagian'), state.data ? state.data.suggestions.bagian : [], 'Contoh: Produksi', $('#ap-form-modal'));
                setTag($('#ap-i-bagian'), preset && preset.bagian);
                $('#ap-jenis button').prop('disabled', false);
                setJenis(state.form.jenis);
                $('#ap-add-point').toggle(mode === 'create');

                if (mode === 'edit') {
                    state.form.keep = (preset.foto_list || []).map(function (p, i) { return { path: p, url: preset.foto_urls[i] }; });
                    const $card = addPointCard(preset);
                    renderPreviews($card.data('uid'));
                } else {
                    addPointCard();
                }
                bootstrap.Modal.getOrCreateInstance(document.getElementById('ap-form-modal')).show();
            }

            function compress(file) {
                return new Promise(function (resolve) {
                    if (!/^image\/(jpeg|png|webp)$/.test(file.type) || file.size < 350 * 1024) return resolve(file);
                    const img = new Image();
                    const url = URL.createObjectURL(file);
                    img.onload = function () {
                        const scale = Math.min(1, 1600 / Math.max(img.width, img.height));
                        const canvas = document.createElement('canvas');
                        canvas.width = Math.round(img.width * scale);
                        canvas.height = Math.round(img.height * scale);
                        canvas.getContext('2d').drawImage(img, 0, 0, canvas.width, canvas.height);
                        URL.revokeObjectURL(url);
                        canvas.toBlob(function (blob) {
                            if (!blob || blob.size >= file.size) return resolve(file);
                            resolve(new File([blob], file.name.replace(/\.\w+$/, '') + '.jpg', { type: 'image/jpeg' }));
                        }, 'image/jpeg', 0.82);
                    };
                    img.onerror = function () { URL.revokeObjectURL(url); resolve(file); };
                    img.src = url;
                });
            }
            function compressAll(entries) {
                return Promise.all(entries.map(function (e) { return compress(e.file); }));
            }

            function submitForm() {
                const f = state.form;
                const header = {
                    jenis: f.jenis,
                    tanggal: $('#ap-i-tanggal').val(),
                    judul: $('#ap-i-judul').val().trim(),
                    peserta: f.jenis === 'meeting' ? $('#ap-i-peserta').val().trim() : '',
                    bagian: $('#ap-i-bagian').val() || '',
                };
                if (!header.tanggal) return toast('Tanggal wajib diisi', 'warning');
                if (!header.judul) { $('#ap-i-judul').focus(); return toast(f.jenis === 'meeting' ? 'Judul meeting wajib diisi' : 'Lokasi temuan wajib diisi', 'warning'); }

                const cards = $('#ap-points .ap-fpoint').toArray().map(function (el) {
                    const $c = $(el);
                    return {
                        uid: $c.data('uid'),
                        poin: $c.find('[data-f="poin"]').val().trim(),
                        pic: $c.find('[data-f="pic"]').val() || '',
                        kategori: $c.find('[data-f="kategori"]').val() || '',
                        target_date: $c.find('[data-f="target_date"]').val(),
                        prioritas: $c.find('input[type="radio"]:checked').val() || 'sedang',
                    };
                });
                const empty = cards.findIndex(function (c) { return !c.poin; });
                if (empty >= 0) { $('#ap-points .ap-fpoint').eq(empty).find('[data-f="poin"]').focus(); return toast('Isi poin ke-' + (empty + 1) + ' masih kosong', 'warning'); }

                const fd = new FormData();
                Object.keys(header).forEach(function (k) { fd.append(k, header[k]); });
                const $btn = $('#ap-form-save').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...');

                Promise.all(cards.map(function (c) { return compressAll(state.files[c.uid]); })).then(function (blobsPerCard) {
                    if (f.mode === 'edit') {
                        const c = cards[0];
                        fd.append('id', f.id);
                        ['poin', 'pic', 'kategori', 'target_date', 'prioritas'].forEach(function (k) { fd.append(k, c[k]); });
                        f.keep.forEach(function (k) { fd.append('foto_keep[]', k.path); });
                        blobsPerCard[0].forEach(function (b) { fd.append('foto[]', b, b.name); });
                    } else {
                        cards.forEach(function (c, i) {
                            ['poin', 'pic', 'kategori', 'target_date', 'prioritas'].forEach(function (k) { fd.append('points[' + i + '][' + k + ']', c[k]); });
                            blobsPerCard[i].forEach(function (b) { fd.append('points[' + i + '][foto][]', b, b.name); });
                        });
                    }
                    return $.ajax({ url: f.mode === 'edit' ? ROUTES.update : ROUTES.store, type: 'POST', data: fd, processData: false, contentType: false });
                }).then(function (res) {
                    toast(res.message);
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('ap-form-modal')).hide();
                    if (f.mode === 'create' && header.jenis !== state.tab) switchTab(header.jenis);
                    if (f.mode === 'create' && (header.tanggal < $('#ap-f-from').val() || header.tanggal > $('#ap-f-to').val())) {
                        if (header.tanggal < $('#ap-f-from').val()) $('#ap-f-from').val(header.tanggal);
                        if (header.tanggal > $('#ap-f-to').val()) $('#ap-f-to').val(header.tanggal);
                    }
                    return load(true);
                }).catch(function (xhr) {
                    if (xhr && xhr.status !== undefined) failed(xhr); else toast('Gagal menyiapkan foto', 'error');
                }).then(function () {
                    $btn.prop('disabled', false).html('<i class="fa-solid fa-floppy-disk"></i> Simpan');
                });
            }

            /* ---------- Lightbox ---------- */
            function openLightbox(urls, idx) {
                if (!urls || !urls.length) return;
                state.lightbox = { urls: urls, idx: idx || 0 };
                $('.ap-lightbox').remove();
                $('body').append('<div class="ap-lightbox" role="dialog" aria-label="Foto"><img alt="Foto"><button type="button" class="ap-lb-close" aria-label="Tutup"><i class="fa-solid fa-xmark"></i></button>' +
                    (urls.length > 1 ? '<button type="button" class="ap-lb-prev" aria-label="Sebelumnya"><i class="fa-solid fa-chevron-left"></i></button><button type="button" class="ap-lb-next" aria-label="Berikutnya"><i class="fa-solid fa-chevron-right"></i></button>' : '') +
                    '<div class="ap-lb-count"></div></div>');
                showLightbox();
            }
            function showLightbox() {
                const lb = state.lightbox;
                const $img = $('.ap-lightbox img');
                $img.attr('src', lb.urls[lb.idx]).css('animation', 'none')[0].offsetHeight;
                $img.css('animation', '');
                $('.ap-lb-count').text(lb.urls.length > 1 ? (lb.idx + 1) + ' / ' + lb.urls.length : '');
            }
            function stepLightbox(d) {
                const lb = state.lightbox;
                lb.idx = (lb.idx + d + lb.urls.length) % lb.urls.length;
                showLightbox();
            }
            function closeLightbox() { state.lightbox = null; $('.ap-lightbox').remove(); }

            /* ---------- Confetti ---------- */
            function confetti() {
                if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
                const canvas = document.getElementById('ap-confetti');
                const ctx = canvas.getContext('2d');
                canvas.width = innerWidth; canvas.height = innerHeight;
                const p = PALETTES[state.theme.palette];
                const colors = [p.a, p.b, '#10b981', '#f59e0b', '#ef4444', '#ffffff'];
                const parts = Array.from({ length: 160 }, function () {
                    return { x: innerWidth / 2, y: innerHeight / 2.4, vx: (Math.random() - .5) * 18, vy: Math.random() * -16 - 4, r: Math.random() * 7 + 4, c: colors[Math.floor(Math.random() * colors.length)], a: Math.random() * Math.PI, va: (Math.random() - .5) * .3 };
                });
                const start = performance.now();
                (function frame(now) {
                    const t = now - start;
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    parts.forEach(function (q) {
                        q.vy += .45; q.vx *= .985; q.x += q.vx; q.y += q.vy; q.a += q.va;
                        ctx.save(); ctx.translate(q.x, q.y); ctx.rotate(q.a);
                        ctx.globalAlpha = Math.max(0, 1 - t / 2600);
                        ctx.fillStyle = q.c; ctx.fillRect(-q.r / 2, -q.r / 4, q.r, q.r / 2);
                        ctx.restore();
                    });
                    if (t < 2600) requestAnimationFrame(frame); else ctx.clearRect(0, 0, canvas.width, canvas.height);
                })(start);
            }

            /* ---------- Tab ---------- */
            function switchTab(tab) {
                if (state.tab === tab) return;
                state.tab = tab;
                store('tab', tab);
                moveTabIndicator();
                renderTiles();
                renderView();
            }

            /* ---------- Event ---------- */
            $('#ap-tabs').on('click', 'button', function () { switchTab(this.dataset.tab); });
            $('#ap-tiles').on('click', '.ap-tile', function () {
                const key = this.dataset.key;
                $('#ap-f-status').val($('#ap-f-status').val() === key ? '' : key).trigger('change');
            });
            $('#ap-f-from, #ap-f-to, #ap-carry').on('change', function () {
                if ($('#ap-f-from').val() > $('#ap-f-to').val()) return toast('Tanggal sampai tidak boleh sebelum tanggal dari', 'warning');
                store('carry', $('#ap-carry').is(':checked'));
                load();
            });
            let searchTimer;
            $('#ap-f-search').on('input', function () { clearTimeout(searchTimer); searchTimer = setTimeout(function () { renderView(); }, 200); });

            $view.on('click', '[data-group]', function (e) {
                if ($(e.target).closest('[data-add-to]').length) return;
                const key = this.dataset.group;
                state.collapsed[key] = !state.collapsed[key];
                $(this).closest('.ap-meet').toggleClass('collapsed', !!state.collapsed[key]);
            });
            $view.on('click', '[data-add-to]', function (e) {
                e.stopPropagation();
                const sample = state.items.find(function (i) { return i.tanggal + '|' + i.judul + '|' + (i.peserta || '') === this.dataset.addTo; }, this);
                if (sample) openForm('create', { jenis: 'meeting', tanggal: sample.tanggal, judul: sample.judul, peserta: sample.peserta, bagian: sample.bagian });
            });
            $view.on('click', '[data-quick]', function (e) { e.stopPropagation(); quickDone(this.dataset.quick); });
            $(document).on('click', '[data-lb]', function (e) {
                e.stopPropagation();
                const item = state.items.find(function (i) { return i.id === Number(this.dataset.lb); }, this);
                if (item) openLightbox(item.foto_urls, Number(this.dataset.idx));
            });
            $(document).on('click', '[data-lb-urls]', function (e) {
                e.stopPropagation();
                openLightbox(JSON.parse(this.dataset.lbUrls), Number(this.dataset.idx));
            });
            $view.on('click', '[data-open]', function (e) {
                if ($(e.target).closest('[data-lb], [data-lb-urls], [data-quick]').length) return;
                openDetail(this.dataset.open);
            });
            $('#ap-ticker').on('click', '[data-open]', function () { openDetail(this.dataset.open); });
            $view.on('click', '[data-new]', function () { openForm('create'); });
            $view.on('click', '[data-reset]', function () {
                $('#ap-f-search').val('');
                $('#ap-f-status').val('').trigger('change.select2');
                $('#ap-f-pic').val('').trigger('change.select2');
                renderTiles();
                renderView();
            });

            // Kartu temuan sedikit miring mengikuti kursor
            $view.on('mousemove', '[data-tilt]', function (e) {
                const r = this.getBoundingClientRect();
                const x = (e.clientX - r.left) / r.width - .5;
                const y = (e.clientY - r.top) / r.height - .5;
                this.style.transform = 'perspective(800px) rotateY(' + (x * 8) + 'deg) rotateX(' + (-y * 8) + 'deg) translateY(-4px)';
            });
            $view.on('mouseleave', '[data-tilt]', function () { this.style.transform = ''; });

            $('#ap-add').on('click', function () { openForm('create'); });
            $('#ap-export').on('click', function () {
                const params = $.extend(filters(), {
                    jenis: state.tab,
                    status: $('#ap-f-status').val() || '',
                    pic: $('#ap-f-pic').val() || '',
                    search: $('#ap-f-search').val().trim(),
                });
                window.location = ROUTES.exportExcel + '?' + $.param(params);
            });

            // Tema
            $('#ap-theme-btn').on('click', function (e) { e.stopPropagation(); $('#ap-theme-pop').toggleClass('d-none'); });
            $('#ap-theme-pop').on('click', function (e) { e.stopPropagation(); });
            $(document).on('click', function () { $('#ap-theme-pop').addClass('d-none'); });
            $('#ap-modes').on('click', 'button', function () { state.theme.mode = this.dataset.mode; store('theme', state.theme); applyTheme(); });
            $('#ap-palettes').on('click', 'button', function () { state.theme.palette = this.dataset.palette; store('theme', state.theme); applyTheme(); });

            // Form
            $('#ap-jenis').on('click', 'button', function () { setJenis(this.dataset.jenis); });
            $('#ap-add-point').on('click', function () {
                const $card = addPointCard();
                $card[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                $card.find('[data-f="poin"]').focus();
            });
            $('#ap-points').on('click', '.ap-fpoint-del', function () {
                const $card = $(this).closest('.ap-fpoint');
                const uid = $card.data('uid');
                $card.addClass('removing');
                setTimeout(function () {
                    $card.find('select').select2('destroy');
                    (state.files[uid] || []).forEach(function (f) { URL.revokeObjectURL(f.url); });
                    delete state.files[uid];
                    $card.remove();
                    numberCards();
                }, 240);
            });
            $('#ap-points').on('change', 'input[data-files]', function () { addFiles(this.dataset.files, this.files); this.value = ''; });
            $('#ap-points').on('click', '[data-file-rm]', function () {
                const parts = this.dataset.fileRm.split(':');
                const removed = state.files[parts[0]].splice(Number(parts[1]), 1)[0];
                URL.revokeObjectURL(removed.url);
                renderPreviews(parts[0]);
            });
            $('#ap-points').on('click', '[data-keep-rm]', function () {
                state.form.keep.splice(Number(this.dataset.keepRm), 1);
                renderPreviews($(this).closest('.ap-fpoint').data('uid'));
            });
            $('#ap-points').on('dragover', '.ap-fpoint', function (e) { e.preventDefault(); $(this).find('.ap-drop').addClass('over'); });
            $('#ap-points').on('dragleave drop', '.ap-fpoint', function () { $(this).find('.ap-drop').removeClass('over'); });
            $('#ap-points').on('drop', '.ap-fpoint', function (e) { e.preventDefault(); addFiles($(this).data('uid'), e.originalEvent.dataTransfer.files); });
            $('#ap-form').on('submit', function (e) { e.preventDefault(); submitForm(); });
            $('#ap-form').on('keydown', function (e) { if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) { e.preventDefault(); submitForm(); } });
            $('#ap-form-modal').on('hidden.bs.modal', resetForm);

            // Detail
            $('#ap-detail').on('click', '[data-gidx]', function () {
                const i = Number(this.dataset.gidx);
                $('#ap-detail .ap-gallery-thumbs img').removeClass('active').eq(i).addClass('active');
                $('#ap-detail .ap-gallery-main').attr('data-idx', i).html('<img src="' + esc(state.detail.foto_urls[i]) + '" alt="Foto">');
            });
            $('#ap-detail').on('click', '#ap-status-pick button', function () {
                state.followStatus = this.dataset.status;
                $('#ap-status-pick button').removeClass('active');
                this.classList.add('active');
            });
            $('#ap-detail').on('change', '#ap-follow-file', function () {
                const room = MAX_PHOTOS - state.followFiles.length;
                const images = Array.from(this.files).filter(function (f) { return /^image\//.test(f.type); });
                if (images.length > room) toast('Maksimal ' + MAX_PHOTOS + ' foto', 'warning');
                images.slice(0, Math.max(0, room)).forEach(function (f) { state.followFiles.push({ file: f, url: URL.createObjectURL(f) }); });
                this.value = '';
                renderFollowPreviews();
            });
            $('#ap-detail').on('click', '[data-follow-rm]', function () {
                const removed = state.followFiles.splice(Number(this.dataset.followRm), 1)[0];
                URL.revokeObjectURL(removed.url);
                renderFollowPreviews();
            });
            $('#ap-detail').on('click', '#ap-follow-save', function () {
                const item = state.detail;
                const note = $('#ap-follow-note').val().trim();
                if (state.followStatus === item.status && !note && !state.followFiles.length) return toast('Isi catatan, lampirkan foto, atau ubah statusnya', 'warning');
                if (state.followStatus === 'batal' && !note) { $('#ap-follow-note').focus(); return toast('Alasan batal wajib diisi', 'warning'); }
                const $btn = $(this).prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...');
                sendFollowUp(item.id, state.followStatus, note, state.followFiles).then(function () {
                    clearFollowFiles();
                    renderDetail();
                    loadLogs(item.id);
                }).catch(function (xhr) {
                    if (xhr && xhr.status !== undefined) failed(xhr);
                    $btn.prop('disabled', false).html('<i class="fa-solid fa-paper-plane"></i> Simpan tindak lanjut');
                });
            });
            $('#ap-detail').on('click', '#ap-edit', function () {
                const item = state.detail;
                bootstrap.Modal.getOrCreateInstance(document.getElementById('ap-detail-modal')).hide();
                openForm('edit', item);
            });
            $('#ap-detail').on('click', '#ap-delete', function () {
                const item = state.detail;
                Swal.fire({ icon: 'warning', title: 'Hapus poin ini?', text: item.poin.length > 120 ? item.poin.slice(0, 120) + '…' : item.poin, showCancelButton: true, confirmButtonText: 'Hapus', cancelButtonText: 'Batal', confirmButtonColor: '#dc3545' }).then(function (r) {
                    if (!r.isConfirmed) return;
                    $.post(ROUTES.remove, { id: item.id }).done(function (res) {
                        toast(res.message);
                        bootstrap.Modal.getOrCreateInstance(document.getElementById('ap-detail-modal')).hide();
                        load(true);
                    }).fail(failed);
                });
            });
            $('#ap-detail-modal').on('hidden.bs.modal', function () { state.detail = null; clearFollowFiles(); });

            // Keyboard
            $(document).on('keydown', function (e) {
                if (state.lightbox) {
                    if (e.key === 'Escape') closeLightbox();
                    if (e.key === 'ArrowRight') stepLightbox(1);
                    if (e.key === 'ArrowLeft') stepLightbox(-1);
                    return;
                }
                const typing = /input|textarea|select/i.test(e.target.tagName) || e.target.isContentEditable;
                if (!typing && !$('.modal.show').length && (e.key === 'n' || e.key === 'N')) { e.preventDefault(); openForm('create'); }
            });
            $(document).on('click', '.ap-lb-close', closeLightbox);
            $(document).on('click', '.ap-lb-prev', function (e) { e.stopPropagation(); stepLightbox(-1); });
            $(document).on('click', '.ap-lb-next', function (e) { e.stopPropagation(); stepLightbox(1); });
            $(document).on('click', '.ap-lightbox', function (e) { if (e.target === this) closeLightbox(); });

            $(window).on('resize', moveTabIndicator);

            /* ---------- Mulai ---------- */
            const savedTheme = store('theme');
            if (savedTheme && PALETTES[savedTheme.palette]) state.theme = savedTheme;
            const savedTab = store('tab');
            if (savedTab === 'meeting' || savedTab === 'temuan') state.tab = savedTab;
            const savedCarry = store('carry');
            if (savedCarry === false) $('#ap-carry').prop('checked', false);

            buildPalettes();
            applyTheme();
            initHeroFx();

            const today = new Date();
            const from = new Date(today);
            from.setDate(from.getDate() - 30);
            $('#ap-f-from').val(iso(from));
            $('#ap-f-to').val(iso(today));

            $('.ap-select').select2({ theme: 'bootstrap4', width: '100%', minimumResultsForSearch: 8 }).on('change', function () { renderTiles(); renderView(); });

            moveTabIndicator();
            setTimeout(moveTabIndicator, 300);
            load();

            // Muat ulang diam-diam tiap 2 menit, dan mode otomatis dicek tiap menit
            setInterval(function () {
                if (document.hidden || $('.modal.show').length || state.lightbox) return;
                load(true);
            }, 120000);
            setInterval(function () { if (state.theme.mode === 'auto') applyTheme(); renderHero(); }, 60000);
        })();
    </script>
@endsection
