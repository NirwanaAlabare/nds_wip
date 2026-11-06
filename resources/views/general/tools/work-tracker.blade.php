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
        .wt-hero { position: relative; overflow: hidden; border-radius: 16px; padding: 22px 26px; color: #fff; background: linear-gradient(120deg, var(--wt-navy), var(--wt-navy-2) 70%, #1c5bb8); box-shadow: 0 10px 28px rgba(8, 33, 73, .22); }
        .wt-hero.mood-good { background: linear-gradient(120deg, #0b3a78, #0f6f8f 55%, #0c8f74); }
        .wt-hero.mood-warn { background: linear-gradient(120deg, #7c3505, #a8530a 55%, #bd6a0c); }
        .wt-hero.mood-bad { background: linear-gradient(120deg, #6d1421, #981f30 55%, #bb2d3d); }
        .wt-hero-main { display: flex; align-items: center; gap: 20px; min-width: 0; }
        .wt-ring { position: relative; flex: none; width: 118px; height: 118px; }
        .wt-ring svg { width: 100%; height: 100%; transform: rotate(-90deg); }
        .wt-ring circle { fill: none; stroke-width: 11; }
        .wt-ring-track { stroke: rgba(255, 255, 255, .22); }
        .wt-ring-value { stroke: #fff; stroke-linecap: round; stroke-dasharray: 100; stroke-dashoffset: 100; transition: stroke-dashoffset 1.4s cubic-bezier(.22, .8, .3, 1); }
        .wt-ring-text { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; line-height: 1.05; }
        .wt-ring-text b { font-size: 27px; font-weight: 800; }
        .wt-ring-text span { font-size: 11px; font-weight: 600; opacity: .88; }
        .wt-hero-eta { margin-top: 7px; font-size: 12.5px; color: rgba(255, 255, 255, .88); }
        .wt-hero::after { content: ""; position: absolute; right: -60px; top: -90px; width: 280px; height: 280px; border-radius: 50%; background: rgba(255, 255, 255, .06); }
        .wt-hero > * { position: relative; z-index: 1; }
        .wt-hero > .wt-hero-runner { position: absolute; z-index: 0; bottom: 8px; left: -40px; font-size: 26px; color: rgba(255, 255, 255, .28); animation: wt-hero-run 14s linear infinite; }
        .wt-hero > .wt-twinkle { position: absolute; z-index: 0; color: #fff; font-size: 9px; animation: wt-twinkle 2.6s ease-in-out infinite; }
        @keyframes wt-hero-run { to { left: 102%; } }
        @keyframes wt-twinkle { 0%, 100% { opacity: .1; transform: scale(.6); } 50% { opacity: .75; transform: scale(1.15); } }
        .wt-alert { display: flex; flex-wrap: wrap; align-items: center; gap: 8px 12px; padding: 10px 16px; margin-bottom: 14px; border-radius: 12px; color: #fff; font-weight: 600; font-size: 13.5px; background: linear-gradient(90deg, #c21f30, #e8505f); animation: wt-alert-glow 1.4s ease-in-out infinite; }
        .wt-alert button { padding: 2px 10px; border: 1px solid rgba(255, 255, 255, .6); border-radius: 14px; background: rgba(255, 255, 255, .14); color: #fff; font-size: 12.5px; font-weight: 600; }
        .wt-alert button:hover { background: rgba(255, 255, 255, .3); }
        @keyframes wt-alert-glow { 0%, 100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, .55); } 50% { box-shadow: 0 0 0 7px rgba(220, 53, 69, 0); } }
        .wt-hero h1 { font-size: 24px; font-weight: 700; margin: 0; }
        .wt-hero-date { color: rgba(255, 255, 255, .85); font-size: 13px; }
        .wt-verdict { display: inline-flex; align-items: center; gap: 10px; margin-top: 10px; padding: 9px 16px; border-radius: 12px; font-weight: 700; font-size: 16.5px; line-height: 1.25; background: #fff; color: var(--wt-ink); box-shadow: 0 6px 16px rgba(0, 0, 0, .18); animation: wt-rise .5s ease both; }
        .wt-verdict i { font-size: 18px; }
        .wt-verdict.good i { color: #0c7f3e; } .wt-verdict.warn i { color: #a86700; } .wt-verdict.bad i { color: #c21f30; }
        .wt-hero .btn-light { color: var(--wt-navy); font-weight: 600; }
        .wt-hero .btn-ghost { color: #fff; border: 1px solid rgba(255, 255, 255, .4); font-weight: 600; }
        .wt-hero .btn-ghost:hover { background: rgba(255, 255, 255, .12); color: #fff; }
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
        .wt-tl-scroll { overflow-x: auto; }
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
        .wt-tl-tip { position: fixed; z-index: 2000; display: none; padding: 4px 10px; border-radius: 8px; background: var(--wt-navy); color: #fff; font-size: 12px; font-weight: 600; white-space: nowrap; pointer-events: none; }

        /* Modal */
        .wt-form .form-control { font-size: 13px; }
        .wt-color { display: flex; gap: 7px; flex-wrap: wrap; }
        .wt-color button { width: 28px; height: 28px; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 1px var(--wt-line); }
        .wt-color button.active { box-shadow: 0 0 0 2px var(--wt-navy); }
        .wt-log { border-left: 2px solid var(--wt-line); padding: 0 0 12px 14px; position: relative; font-size: 12.5px; }
        .wt-log::before { content: ""; position: absolute; left: -6px; top: 3px; width: 10px; height: 10px; border-radius: 50%; background: var(--wt-blue); }
        .wt-range { width: 100%; accent-color: var(--wt-blue); }
        .wt-range-value { font-size: 28px; font-weight: 700; color: var(--wt-navy); }
        .select2-container { width: 100% !important; }

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
            .wt-hero { padding: 18px; }
            .wt-ring { width: 92px; height: 92px; }
            .wt-ring-text b { font-size: 21px; }
            .wt-verdict { font-size: 14.5px; }
        }
    </style>
@endsection

@section('content')
    <div class="wt">
        <div class="wt-hero mb-3">
            <i class="fa-solid fa-person-running wt-hero-runner" aria-hidden="true"></i>
            <i class="fa-solid fa-star wt-twinkle" style="top: 18%; left: 44%;" aria-hidden="true"></i>
            <i class="fa-solid fa-star wt-twinkle" style="top: 62%; left: 58%; animation-delay: .8s;" aria-hidden="true"></i>
            <i class="fa-solid fa-star wt-twinkle" style="top: 30%; left: 70%; animation-delay: 1.5s;" aria-hidden="true"></i>
            <i class="fa-solid fa-star wt-twinkle" style="top: 72%; left: 34%; animation-delay: 2s;" aria-hidden="true"></i>
            <div class="d-flex flex-wrap justify-content-between gap-3">
                <div class="wt-hero-main">
                    <div class="wt-ring" id="wt-ring" role="img" aria-label="Progress tim">
                        <svg viewBox="0 0 120 120" aria-hidden="true"><circle class="wt-ring-track" cx="60" cy="60" r="52"/><circle class="wt-ring-value" id="wt-ring-value" cx="60" cy="60" r="52" pathLength="100"/></svg>
                        <div class="wt-ring-text"><b id="wt-ring-pct">0%</b><span>Progress tim</span></div>
                    </div>
                    <div style="min-width:0">
                        <h1><i class="fa-solid fa-person-running"></i> Work Tracker</h1>
                        <div class="wt-hero-date" id="wt-date"></div>
                        <div class="wt-verdict" id="wt-verdict"><i class="fa-solid fa-spinner fa-spin"></i> Memuat...</div>
                        <div class="wt-hero-eta" id="wt-eta"></div>
                    </div>
                </div>
                <div class="d-flex flex-wrap align-items-start gap-2">
                    <a class="btn btn-ghost btn-sm" href="{{ route('work-tracker-dashboard') }}"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
                    <button class="btn btn-ghost btn-sm" id="wt-btn-guide" data-bs-toggle="modal" data-bs-target="#wt-guide-modal"><i class="fa-solid fa-circle-question"></i> Panduan</button>
                    <button class="btn btn-ghost btn-sm" id="wt-btn-programmer"><i class="fa-solid fa-users"></i> Anggota</button>
                    <button class="btn btn-light btn-sm" id="wt-btn-add"><i class="fa-solid fa-plus"></i> Tambah Kerjaan</button>
                </div>
            </div>
        </div>

        <div class="wt-alert d-none" id="wt-urgent" role="alert"></div>

        <div class="wt-demo mb-3 d-none" id="wt-demo-banner">
            <i class="fa-solid fa-eye"></i> <b>Mode contoh:</b> data ini hanya untuk melihat tampilan dan tidak tersimpan.
            <a href="#" id="wt-demo-exit" class="fw-bold ms-1">Kembali ke data asli</a>
        </div>

        <div class="wt-tiles" id="wt-tiles"></div>

        <div class="wt-card wt-race">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                <h2 class="wt-section-title"><i class="fa-solid fa-flag-checkered"></i> Arena Tim<span class="wt-scene" id="wt-scene"></span></h2>
                <div class="wt-legend">
                    <span><i class="fa-solid fa-person-running text-success"></i> Ngebut: aman</span>
                    <span><i class="fa-solid fa-person-running text-warning"></i> Jogging: mepet</span>
                    <span><i class="fa-solid fa-person-walking text-danger"></i> Jalan: tertinggal</span>
                    <span><i class="fa-solid fa-flag"></i> Posisi seharusnya hari ini</span>
                    <span><i class="fa-solid fa-circle-check text-success"></i> Pos = kerjaan</span>
                </div>
            </div>
            <div class="wt-division-chips" id="wt-division-chips"></div>
            <div id="wt-lanes"></div>
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

            <div class="wt-filters">
                <div class="wt-report-only d-none"><label>Dari</label><input type="date" class="form-control" id="wt-f-from"></div>
                <div class="wt-report-only d-none"><label>Sampai</label><input type="date" class="form-control" id="wt-f-to"></div>
                <div><label>Cari</label><input type="search" class="form-control" id="wt-f-search" placeholder="Judul, modul, peminta..."></div>
                <div id="wt-f-division-box"><label>Divisi</label><select class="wt-select" id="wt-f-division"></select></div>
                <div id="wt-f-programmer-box"><label>Anggota</label><select class="wt-select" id="wt-f-programmer"></select></div>
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
        <div class="modal-dialog modal-lg">
            <form class="modal-content wt-form" id="wt-item-form">
                <div class="modal-header">
                    <h5 class="modal-title" id="wt-item-title">Tambah Kerjaan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id">
                    <div class="row g-3">
                        <div class="col-md-4"><label>Jenis</label>
                            <select class="wt-select-modal" name="type" id="wt-i-type"><option value="plan">Plan</option><option value="bug">Masalah</option><option value="request">Request</option></select>
                        </div>
                        <div class="col-md-8"><label>Judul kerjaan</label><input class="form-control" name="title" maxlength="200" required></div>
                        <div class="col-md-4"><label>Modul</label><input class="form-control" name="module" maxlength="100" placeholder="Contoh: Cutting"></div>
                        <div class="col-md-4"><label>Anggota</label><select class="wt-select-modal" name="programmer_id" id="wt-i-programmer"></select></div>
                        <div class="col-md-4 wt-not-plan"><label>Level</label>
                            <select class="wt-select-modal" name="level" id="wt-i-level"><option value="">Belum ditentukan</option><option value="major">Major</option><option value="minor">Minor</option><option value="tidak">Tidak dikerjakan</option></select>
                        </div>
                        <div class="col-md-6"><label>Peminta / pelapor</label><input class="form-control" name="requester" maxlength="100"></div>
                        <div class="col-md-6"><label>Departemen</label><input class="form-control" name="requester_dept" maxlength="100" placeholder="Contoh: Marketing"></div>
                        <div class="col-12 d-none" id="wt-i-reason-box"><label>Alasan tidak dikerjakan</label><input class="form-control" name="reject_reason" maxlength="500"></div>
                        <div class="col-md-4"><label>Mulai</label><input type="date" class="form-control" name="start_date"></div>
                        <div class="col-md-4"><label>Target selesai</label><input type="date" class="form-control" name="target_date"></div>
                        <div class="col-md-4"><label>Estimasi (hari)</label><input type="number" class="form-control" name="estimate_days" min="0.5" max="999" step="0.5"><div class="wt-sub mt-1">Otomatis dari tanggal, ubah kalau pengerjaannya lebih singkat.</div></div>
                        <div class="col-12"><label>Catatan</label><textarea class="form-control" name="description" rows="3" maxlength="5000"></textarea></div>
                    </div>
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
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <div class="wt-sub" id="wt-d-code"></div>
                        <h5 class="modal-title" id="wt-d-title"></h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div id="wt-d-info" class="mb-3"></div>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <form id="wt-progress-form" class="wt-form">
                                <div class="d-flex justify-content-between align-items-end">
                                    <label class="mb-0">Update progress</label>
                                    <span class="wt-range-value"><span id="wt-range-value">0</span>%</span>
                                </div>
                                <input type="range" class="wt-range" id="wt-range" min="0" max="100" step="5">
                                <label class="mt-2">Catatan hari ini</label>
                                <textarea class="form-control mb-2" id="wt-note" rows="3" maxlength="2000" placeholder="Hari ini mengerjakan apa?"></textarea>
                                <button class="btn btn-sb btn-sm w-100" type="submit"><i class="fa-solid fa-save"></i> Simpan Progress</button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <label class="small fw-bold text-muted mb-2">Riwayat</label>
                            <div id="wt-logs" style="max-height: 240px; overflow-y: auto;"></div>
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

            const state = { timelineOpen: {}, drag: null, justDragged: false, division: '', board: null, demo: false, view: 'list', selected: null, color: COLORS[0], current: null, pos: {}, jumpers: [], urgentIds: [], reportItems: [], reportTimer: null, reportSeq: 0, statusBeforeReport: null, estimateManual: false, tileValues: {}, ringPct: 0 };
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
                $('#wt-date').text(parseDate(b.today).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }));
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
                $('#wt-verdict').attr('class', 'wt-verdict ' + kind).html('<i class="fa-solid ' + icon + '"></i> ' + esc(text));
                $('.wt-hero').removeClass('mood-good mood-warn mood-bad').addClass('mood-' + kind);
                $('#wt-eta').text(s.active && s.latest_predicted ? 'Perkiraan semua kerjaan aktif selesai: ' + fmt(s.latest_predicted, true) : '');

                $('#wt-ring').attr('aria-label', 'Progress tim ' + s.progress + '%');
                countUp(document.getElementById('wt-ring-pct'), state.ringPct, s.progress, function (v) { return Math.round(v) + '%'; });
                state.ringPct = s.progress;
                requestAnimationFrame(function () {
                    requestAnimationFrame(function () {
                        document.getElementById('wt-ring-value').style.strokeDashoffset = 100 - s.progress;
                    });
                });
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
                $('#wt-f-division-box, #wt-f-programmer-box, #wt-f-health-box').toggleClass('d-none', view === 'queue');
                $('#wt-f-status-box').toggleClass('d-none', view === 'queue' || view === 'kanban');
                $('.wt-report-only').toggleClass('d-none', view !== 'report');

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
                    from: $('#wt-f-from').val(), to: $('#wt-f-to').val(), division: $('#wt-f-division').val(), programmer: $('#wt-f-programmer').val(), type: $('#wt-f-type').val(),
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

            function renderDivisionChips() {
                const divisions = memberDivisions();
                if (divisions.length < 2) {
                    $('#wt-division-chips').empty();
                    return;
                }
                $('#wt-division-chips').html([''].concat(divisions).map(function (d) {
                    return '<button type="button" data-division="' + esc(d) + '" class="' + (state.division === d ? 'active' : '') + '">' + esc(d || 'Semua') + '</button>';
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
                $('#wt-lanes').html(memberDivisions().filter(function (d) {
                    return !state.division || state.division === d;
                }).map(function (d) {
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
                    if (!queueView && state.division) {
                        const owner = programmerOf(i);
                        if (!owner || owner.division !== state.division) return false;
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

            function renderView() {
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
                const rows = sorted.map(function (i) {
                    const sub = [i.code, i.module, i.requester ? 'dari ' + i.requester + (i.requester_dept ? ' (' + i.requester_dept + ')' : '') : null].filter(Boolean).join(' · ');
                    return '<tr data-id="' + i.id + '">' +
                        '<td><div class="wt-title">' + esc(i.title) + '</div><div class="wt-sub">' + esc(sub) + '</div></td>' +
                        '<td>' + typeChip(i) + ' ' + levelChip(i) + '</td>' +
                        '<td>' + (['baru', 'dikerjakan'].includes(i.status) ? assignSelect(i, 'Belum ditugaskan') : avatar(i)) + '</td>' +
                        '<td><div class="d-flex align-items-center gap-2"><div class="wt-bar flex-grow-1"><span class="fill-' + esc(i.health) + '" style="width:' + Number(i.progress) + '%"></span></div><b>' + Number(i.progress) + '%</b></div>' +
                        '<div class="wt-sub">' + (i.pace ? num(i.pace) + '% per hari' : esc(STATUS_LABEL[i.status])) + '</div></td>' +
                        '<td>' + fmt(i.target_date) + '</td>' +
                        '<td>' + fmt(i.predicted_date) + '</td>' +
                        '<td>' + healthChip(i) + '<div class="wt-sub mt-1">' + esc(i.message) + '</div></td></tr>';
                }).join('');
                $view.html('<div class="table-responsive"><table class="wt-table"><thead><tr><th>Kerjaan</th><th>Jenis</th><th>Anggota</th><th>Progress</th><th>Target</th><th>Prediksi</th><th>Kondisi</th></tr></thead><tbody>' + rows + '</tbody></table></div>');
                initAssignSelects($view);
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
                            '<div class="d-flex justify-content-between align-items-center wt-sub"><span>' + Number(i.progress) + '% · ' + fmt(i.target_date) + '</span>' + (programmerOf(i) ? '<span class="wt-avatar wt-mini-avatar" title="' + esc(programmerOf(i).name) + '" style="background:' + esc(programmerOf(i).color) + '">' + esc(initials(programmerOf(i).name)) + '</span>' : '') + '</div></div>';
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
                const shiftIso = function (iso, n) {
                    const date = parseDate(iso);
                    date.setDate(date.getDate() + n);
                    return date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0');
                };

                const bars = items.filter(function (i) { return i.start_date || i.target_date || i.predicted_date; }).map(function (i) {
                    const start = i.start_date || i.target_date || i.predicted_date;
                    const planEnd = i.target_date || i.predicted_date || start;
                    const end = planEnd < start ? start : planEnd;
                    const overrun = i.health === 'telat' && i.predicted_date && i.predicted_date > end ? i.predicted_date : null;
                    return { item: i, start: start, end: end, overrun: overrun, visualEnd: overrun || end };
                });

                // Rentang waktu diberi ruang kosong supaya batang bisa digeser melewati tanggal terakhir
                let min = shiftIso(today, -1), max = shiftIso(today, 13);
                bars.forEach(function (b) {
                    if (b.start < min) min = b.start;
                    if (b.visualEnd > max) max = b.visualEnd;
                });
                max = shiftIso(max, 3);
                const span = days(min, max) + 1;
                const dayW = Math.max(38, ($view.width() - SIDE_WIDTH) / span);
                const areaWidth = span * dayW;
                const rowStyle = 'grid-template-columns:' + SIDE_WIDTH + 'px ' + areaWidth + 'px;--day:' + dayW + 'px';
                const todayLeft = days(min, today) * dayW;
                const todayMark = '<div class="wt-tl-todaycol" style="left:' + todayLeft + 'px;width:' + dayW + 'px"></div>';

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
                        const left = days(min, b.start) * dayW + 1;
                        const width = (days(b.start, b.end) + 1) * dayW - 2;
                        const canDrag = ['baru', 'dikerjakan'].includes(i.status);
                        const owner = programmerOf(i);
                        const tip = i.code + ' ' + i.title + ' | ' + fmt(b.start) + ' sampai ' + fmt(b.end) + ' | ' + i.progress + '% | ' + HEALTH_LABEL[i.health];
                        const over = b.overrun ? '<div class="wt-tl-over" style="top:' + top + 'px;left:' + (left + width) + 'px;width:' + (days(b.end, b.overrun) * dayW) + 'px"></div>' : '';
                        return over + '<button type="button" class="wt-tl-bar tl-' + esc(i.health) + (canDrag ? ' can-drag' : '') + '" data-id="' + i.id + '" data-start="' + b.start + '" data-end="' + b.end + '" data-owner="' + (owner ? owner.id : '') + '" title="' + esc(tip) + '" style="top:' + top + 'px;left:' + left + 'px;width:' + width + 'px">' +
                            '<span class="wt-tl-fill" style="width:' + Number(i.progress) + '%"></span>' +
                            '<span class="wt-tl-text">' + (owner ? '<span class="wt-avatar wt-tl-avatar" style="background:' + esc(owner.color) + '">' + esc(initials(owner.name)) + '</span>' : '') + (i.urgent ? '<i class="fa-solid fa-road-barrier"></i> ' : '') + esc(i.title) + '</span>' +
                            (canDrag ? '<span class="wt-tl-handle" title="Tarik untuk mengubah target"></span>' : '') + '</button>';
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
                    return (!state.division || state.division === d) && programmerFilter !== 'none';
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
                const showUnassigned = !state.division && (!programmerFilter || programmerFilter === 'none') && (unassigned.length || state.board.programmers.length);
                const bottom = showUnassigned ? '<div class="wt-tl-division">Belum ditugaskan</div>' + rowHtml('none', 'Belum ditugaskan', '#9aa6b8', unassigned) : '';

                if (!body && !bottom) {
                    $view.html('<div class="wt-empty"><i class="fa-solid fa-chart-gantt"></i>Belum ada kerjaan dengan tanggal.</div>');
                    return;
                }

                $view.html('<div class="wt-sub mb-2"><i class="fa-solid fa-hand-pointer"></i> Klik batang untuk membuka detail. Geser untuk memindah jadwal atau anggota. Tarik garis di ujung kanan untuk mengubah target.</div>' +
                    '<div class="wt-tl-scroll"><div class="wt-tl" data-day-width="' + dayW + '">' +
                    '<div class="wt-tl-row wt-tl-head" style="' + rowStyle + '"><div class="wt-tl-side"></div><div class="wt-tl-area">' + todayMark +
                    '<span class="wt-tl-todaylabel" style="left:' + (todayLeft + 4) + 'px">Hari ini</span>' + ticks + '</div></div>' + body + bottom + '</div></div>' +
                    '<div class="wt-tl-tip" id="wt-tl-tip"></div>');
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
                const divisionValue = $('#wt-f-division').val();
                $('#wt-f-division').html('<option value="">Semua divisi</option>' + memberDivisions().map(function (d) { return '<option value="' + esc(d) + '">' + esc(d) + '</option>'; }).join('')).val(divisionValue || '').trigger('change.select2');
                state.division = $('#wt-f-division').val() || '';
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
                bootstrap.Modal.getOrCreateInstance(document.getElementById('wt-item-modal')).show();
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

                $('#wt-range').val(item.progress).prop('disabled', item.status === 'ditolak');
                $('#wt-range-value').text(item.progress);
                $('#wt-note').val('');
                $('#wt-progress-form button').prop('disabled', item.status === 'ditolak');

                $('#wt-logs').html('<div class="wt-sub">Memuat...</div>');
                if (state.demo) {
                    $('#wt-logs').html('<div class="wt-sub">Riwayat tampil di data asli.</div>');
                } else {
                    $.get(ROUTES.logs + '/' + id).done(function (logs) {
                        $('#wt-logs').html(logs.length ? logs.map(function (l) {
                            const change = l.progress_before !== null && l.progress_before !== l.progress_after ? l.progress_before + '% → ' + l.progress_after + '%' : (STATUS_LABEL[l.status] || '');
                            return '<div class="wt-log"><b>' + esc(change) + '</b> <span class="wt-sub">' + esc((l.created_at || '').slice(0, 16)) + ' · ' + esc(l.created_by) + '</span>' + (l.note ? '<div style="white-space:pre-wrap">' + esc(l.note) + '</div>' : '') + '</div>';
                        }).join('') : '<div class="wt-sub">Belum ada riwayat.</div>');
                    }).fail(failed);
                }

                bootstrap.Modal.getOrCreateInstance(document.getElementById('wt-detail-modal')).show();
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
                $('#wt-programmer-list').html(state.board.programmers.length ? state.board.programmers.map(function (p) {
                    return '<div class="d-flex align-items-center gap-2 py-1"><span class="wt-avatar wt-mini-avatar" style="background:' + esc(p.color) + '">' + esc(initials(p.name)) + '</span><span class="flex-grow-1 small fw-bold">' + esc(p.name) + '</span>' +
                        '<div class="wt-member-division"><select class="wt-member-select" aria-label="Divisi ' + esc(p.name) + '" data-user="' + p.user_id + '" data-color="' + esc(p.color) + '" data-current="' + esc(p.division) + '">' + divisionOptions(p.division) + '</select></div>' +
                        '<button type="button" class="btn btn-sm btn-link text-danger p-0" data-remove-programmer="' + p.id + '" aria-label="Nonaktifkan ' + esc(p.name) + '"><i class="fa-solid fa-user-minus"></i></button></div>';
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
            $('.wt-select').select2({ theme: 'bootstrap4', width: '100%', minimumResultsForSearch: 8 }).on('change', renderView);
            $('.wt-select-modal').select2({ theme: 'bootstrap4', width: '100%', dropdownParent: $('#wt-item-modal') });
            initDivisionSelect($('#wt-p-division'));
            $('#wt-p-user').select2({ theme: 'bootstrap4', width: '100%', dropdownParent: $('#wt-programmer-modal'), placeholder: 'Pilih user' });
            $('#wt-f-search').on('input', renderView);
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
                $('#wt-f-division').val(this.dataset.division).trigger('change');
            });

            $('#wt-f-division').on('change', function () {
                state.division = this.value || '';
                const owner = state.board.programmers.find(function (p) { return p.id === state.selected; });
                if (state.division && owner && owner.division !== state.division) {
                    state.selected = null;
                    $('#wt-f-programmer').val('').trigger('change.select2');
                }
                renderLanes();
                renderView();
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

            $view.on('click', 'tbody tr, .wt-kcard, .wt-tl-bar', function (e) {
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
            setInterval(updateSay, 4000);
            // Suasana Arena dicek tiap menit, lintasan hanya digambar ulang kalau suasananya berganti
            setInterval(function () {
                if (state.board && state.ambience && ambienceKey(ambienceNow()) !== ambienceKey(state.ambience)) renderLanes();
            }, 60000);

            $('#wt-i-type').on('change', function () { $('.wt-not-plan').toggleClass('d-none', this.value === 'plan'); });
            $('#wt-i-level').on('change', function () { $('#wt-i-reason-box').toggleClass('d-none', this.value !== 'tidak'); });

            $('#wt-item-form').on('submit', function (e) {
                e.preventDefault();
                post(ROUTES.store, $(this).serialize(), function (res) {
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('wt-item-modal')).hide();
                    toast(res.message);
                    load();
                });
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
                post(ROUTES.progress, { id: item.id, progress: $('#wt-range').val(), note: $('#wt-note').val() }, function (res) {
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('wt-detail-modal')).hide();
                    toast(res.message);
                    if (res.just_finished) celebrate(item);
                    load();
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
