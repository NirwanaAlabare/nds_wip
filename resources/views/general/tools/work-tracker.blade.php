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
        .wt-hero { position: relative; overflow: hidden; border-radius: 16px; padding: 22px 26px; color: #fff; background: linear-gradient(120deg, var(--wt-navy), var(--wt-navy-2) 70%, #1c5bb8); }
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
        .wt-hero-date { color: #bcd0ee; font-size: 13px; }
        .wt-verdict { display: inline-flex; align-items: center; gap: 10px; margin-top: 12px; padding: 9px 14px; border-radius: 10px; font-weight: 600; font-size: 14px; background: rgba(255, 255, 255, .12); }
        .wt-verdict.good i { color: #5fe39a; } .wt-verdict.warn i { color: #ffc65c; } .wt-verdict.bad i { color: #ff8d98; }
        .wt-hero .btn-light { color: var(--wt-navy); font-weight: 600; }
        .wt-hero .btn-ghost { color: #fff; border: 1px solid rgba(255, 255, 255, .4); font-weight: 600; }
        .wt-hero .btn-ghost:hover { background: rgba(255, 255, 255, .12); color: #fff; }
        .wt-demo { background: #fff5dc; border: 1px solid #f3d995; color: #755300; border-radius: 10px; padding: 8px 14px; font-size: 13px; }

        /* Tiles */
        .wt-tiles { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; margin: 14px 0; }
        .wt-tile { padding: 14px 16px; display: flex; gap: 12px; align-items: center; animation: wt-rise .45s ease both; }
        .wt-tile-icon { width: 40px; height: 40px; border-radius: 11px; display: grid; place-items: center; font-size: 16px; }
        .wt-tile-value { font-size: 23px; font-weight: 700; line-height: 1.1; animation: wt-pop .5s ease both; }
        .wt-tile:hover .wt-tile-icon { animation: wt-wiggle .5s ease; }
        @keyframes wt-pop { 0% { transform: scale(.6); opacity: 0; } 70% { transform: scale(1.12); } 100% { transform: none; opacity: 1; } }
        @keyframes wt-wiggle { 25% { transform: rotate(-12deg); } 75% { transform: rotate(12deg); } }
        .wt-tile-label { font-size: 12px; color: var(--wt-muted); }
        @keyframes wt-rise { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }

        /* Antrean */
        .wt-tab-count { display: inline-block; min-width: 20px; margin-left: 3px; padding: 0 6px; border-radius: 10px; background: #fff2d6; color: #a86700; font-size: 11.5px; font-weight: 700; }
        .wt-tab-count.empty { background: #eceff4; color: #5b6577; }
        .wt-tile-link { width: 100%; text-align: left; cursor: pointer; transition: transform .12s, box-shadow .12s; }
        .wt-tile-link:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(8, 33, 73, .12); }
        .wt-queue-row { display: grid; grid-template-columns: minmax(0, 1fr) 170px 120px 280px; gap: 12px; align-items: center; padding: 9px 0; border-top: 1px solid var(--wt-line); font-size: 13px; }
        .wt-queue-row:first-child { border-top: 0; }
        .wt-queue-title { display: block; padding: 0; border: 0; background: none; color: inherit; text-align: left; font-weight: 600; }
        .wt-queue-title:hover { text-decoration: underline; }
        .wt-queue-overdue { color: var(--wt-red); font-weight: 700; }
        .wt-assign { min-width: 190px; }

        /* Race */
        .wt-section-title { font-size: 15px; font-weight: 700; margin: 0; }
        .wt-race { padding: 16px 18px 8px; margin-bottom: 14px; }
        .wt-legend { display: flex; flex-wrap: wrap; gap: 14px; font-size: 12px; color: var(--wt-muted); }
        .wt-lane { display: grid; grid-template-columns: 190px 1fr 190px; gap: 14px; align-items: center; padding: 8px 6px; border-radius: 10px; cursor: pointer; transition: background .15s; }
        .wt-lane:hover { background: var(--wt-soft); }
        .wt-lane.selected { background: #e9f1fe; }
        .wt-lane + .wt-lane { margin-top: 2px; }
        .wt-person { display: flex; align-items: center; gap: 10px; min-width: 0; }
        .wt-avatar { flex: none; width: 36px; height: 36px; border-radius: 50%; color: #fff; display: grid; place-items: center; font-size: 13px; font-weight: 700; }
        .wt-person-name { font-weight: 600; font-size: 13.5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .wt-person-sub { font-size: 11.5px; color: var(--wt-muted); }
        .wt-track { position: relative; height: 104px; border-radius: 10px; overflow: hidden; background: linear-gradient(#eaf2ff 0 calc(100% - 28px), #dfe7f2 0); }
        .wt-track-road { position: absolute; left: 0; right: 0; bottom: 11px; height: 2px; background-image: linear-gradient(90deg, #fff 50%, transparent 50%); background-size: 28px 2px; animation: wt-road var(--road, 0s) linear infinite; }
        @keyframes wt-road { to { background-position-x: -28px; } }
        .wt-finish { position: absolute; z-index: 1; top: 0; bottom: 0; right: 0; width: 16px; background: repeating-conic-gradient(#1b2536 0 25%, #fff 0 50%) 0 0 / 8px 8px; }
        .wt-pacer { position: absolute; top: 25px; bottom: 0; width: 0; border-left: 2px dashed rgba(8, 33, 73, .4); transition: left 1.2s ease; }
        .wt-pacer::before { content: "\f024"; font-family: "Font Awesome 6 Free"; font-weight: 900; position: absolute; top: -3px; left: 1px; font-size: 10px; color: var(--wt-navy); }
        .wt-runner-box { position: absolute; z-index: 2; pointer-events: none; bottom: 3px; width: 54px; height: 56px; left: 0; transition: left 1.3s cubic-bezier(.22, .8, .3, 1); }
        .wt-pct { position: absolute; bottom: 57px; left: 27px; transform: translateX(-50%); padding: 0 6px; border-radius: 9px; background: #fff; box-shadow: 0 1px 3px rgba(8, 33, 73, .2); font-size: 11.5px; font-weight: 700; color: var(--wt-navy); white-space: nowrap; }
        .wt-cp-label { position: absolute; top: 4px; height: 18px; padding: 0 2px 0 6px; border: 0; background: none; font-size: 11.5px; font-weight: 600; line-height: 18px; text-align: right; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #5b6577; }
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
        .wt-sun { position: absolute; top: 25px; right: 28px; color: #f2c94c; font-size: 17px; animation: wt-spin 12s linear infinite; }
        @keyframes wt-spin { to { transform: rotate(360deg); } }
        .wt-cloud { position: absolute; left: 100%; color: #fff; font-size: 21px; opacity: .95; animation: wt-drift var(--dur, 20s) linear infinite; }
        .wt-cloud.mini { font-size: 13px; opacity: .8; }
        @keyframes wt-drift { to { left: -40px; } }
        .wt-bird { position: absolute; top: 34px; left: -24px; color: #6d84a8; font-size: 11px; animation: wt-fly 21s linear infinite; }
        @keyframes wt-fly { 0% { left: -24px; transform: translateY(0); } 25% { transform: translateY(-6px); } 50% { transform: translateY(4px); } 75% { transform: translateY(-5px); } 100% { left: 102%; transform: translateY(0); } }
        .wt-crowd { position: absolute; right: 20px; bottom: 27px; display: flex; gap: 1px; font-size: 13px; opacity: .85; }
        .wt-crowd i { animation: wt-crowd 1.6s ease-in-out infinite; }
        .wt-crowd.cheer i { animation-duration: .45s; }
        @keyframes wt-crowd { 50% { transform: translateY(-5px); } }
        .wt-balloon { position: absolute; bottom: -18px; width: 13px; height: 16px; border-radius: 50% 50% 48% 48%; animation: wt-balloon 4.4s ease-in infinite; }
        .wt-balloon::after { content: ""; position: absolute; left: 6px; top: 16px; height: 9px; border-left: 1px solid rgba(8, 33, 73, .35); }
        @keyframes wt-balloon { 0% { bottom: -18px; transform: translateX(0); opacity: 1; } 50% { transform: translateX(7px); } 100% { bottom: 110px; transform: translateX(-4px); opacity: .6; } }
        .wt-spark { position: absolute; color: #f2c94c; font-size: 11px; animation: wt-twinkle 1.2s ease-in-out infinite; }
        .wt-crown { color: #e0a400; }
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
        .wt-col { background: var(--wt-soft); border-radius: 12px; padding: 10px; min-height: 240px; transition: outline .1s; }
        .wt-col.over { outline: 2px dashed var(--wt-blue); }
        .wt-col-head { display: flex; justify-content: space-between; font-weight: 700; font-size: 13px; padding: 2px 4px 9px; }
        .wt-kcard { background: #fff; border-radius: 10px; border-left: 4px solid var(--wt-gray); padding: 9px 11px; margin-bottom: 9px; font-size: 12.5px; box-shadow: 0 1px 4px rgba(8, 33, 73, .08); cursor: grab; }
        .wt-kcard:hover { box-shadow: 0 4px 12px rgba(8, 33, 73, .14); }
        .wt-kcard.b-aman, .wt-kcard.b-selesai { border-left-color: var(--wt-green); } .wt-kcard.b-mepet { border-left-color: var(--wt-amber); } .wt-kcard.b-telat, .wt-kcard.b-selesai_telat { border-left-color: var(--wt-red); }

        /* Timeline */
        .wt-gantt { overflow-x: auto; }
        .wt-gantt-inner { position: relative; min-width: 980px; }
        .wt-gantt-row { display: grid; grid-template-columns: 220px 1fr; align-items: center; min-height: 34px; border-bottom: 1px solid var(--wt-line); }
        .wt-gantt-group { font-weight: 700; font-size: 12.5px; background: var(--wt-soft); padding: 6px 10px; border-radius: 6px; margin-top: 8px; }
        .wt-gantt-label { font-size: 12px; padding: 4px 10px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; cursor: pointer; }
        .wt-gantt-area { position: relative; height: 32px; }
        .wt-gantt-head { position: relative; height: 40px; }
        .wt-gantt-tick { position: absolute; top: 20px; transform: translateX(-50%); font-size: 10.5px; color: var(--wt-muted); white-space: nowrap; }
        .wt-gantt-bar { position: absolute; top: 8px; height: 16px; border-radius: 6px; background: #d5dfee; overflow: hidden; cursor: pointer; }
        .wt-gantt-bar span { display: block; height: 100%; }
        .wt-gantt-over { position: absolute; top: 8px; height: 16px; border-radius: 0 6px 6px 0; background: repeating-linear-gradient(45deg, #ffb3bb 0 5px, #ffe3e6 5px 10px); }
        .wt-gantt-today { position: absolute; top: 0; bottom: 0; width: 2px; background: var(--wt-red); z-index: 2; pointer-events: none; }
        .wt-gantt-today::before { content: "Hari ini"; position: absolute; top: -1px; left: 4px; font-size: 10px; color: var(--wt-red); font-weight: 700; white-space: nowrap; }

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
        .wt-confetti { position: fixed; top: -12px; width: 9px; height: 13px; z-index: 2000; pointer-events: none; animation: wt-fall var(--dur) ease-in forwards; }
        @keyframes wt-fall { to { transform: translate(var(--drift), 105vh) rotate(760deg); opacity: .8; } }

        @media (max-width: 991.98px) {
            .wt-lane { grid-template-columns: 1fr; gap: 6px; }
            .wt-queue-row { grid-template-columns: 1fr; gap: 6px; }
            .wt-hero { padding: 18px; }
        }
        @media (prefers-reduced-motion: reduce) {
            .wt *, .wt-confetti { animation: none !important; transition: none !important; }
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
                <div>
                    <h1><i class="fa-solid fa-person-running"></i> Work Tracker</h1>
                    <div class="wt-hero-date" id="wt-date"></div>
                    <div class="wt-verdict" id="wt-verdict"><i class="fa-solid fa-spinner fa-spin"></i> Memuat...</div>
                </div>
                <div class="d-flex flex-wrap align-items-start gap-2">
                    <button class="btn btn-ghost btn-sm" id="wt-btn-guide" data-bs-toggle="modal" data-bs-target="#wt-guide-modal"><i class="fa-solid fa-circle-question"></i> Panduan</button>
                    <button class="btn btn-ghost btn-sm" id="wt-btn-programmer"><i class="fa-solid fa-users"></i> Programmer</button>
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
                <h2 class="wt-section-title"><i class="fa-solid fa-flag-checkered"></i> Arena Programmer</h2>
                <div class="wt-legend">
                    <span><i class="fa-solid fa-person-running text-success"></i> Ngebut: aman</span>
                    <span><i class="fa-solid fa-person-running text-warning"></i> Jogging: mepet</span>
                    <span><i class="fa-solid fa-person-walking text-danger"></i> Jalan: tertinggal</span>
                    <span><i class="fa-solid fa-flag"></i> Posisi seharusnya hari ini</span>
                    <span><i class="fa-solid fa-circle-check text-success"></i> Pos = kerjaan</span>
                </div>
            </div>
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
                <div id="wt-f-programmer-box"><label>Programmer</label><select class="wt-select" id="wt-f-programmer"></select></div>
                <div><label>Jenis</label>
                    <select class="wt-select" id="wt-f-type">
                        <option value="">Semua jenis</option><option value="plan">Plan</option><option value="bug">Bug</option><option value="request">Request</option>
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
                            <select class="wt-select-modal" name="type" id="wt-i-type"><option value="plan">Plan</option><option value="bug">Bug</option><option value="request">Request</option></select>
                        </div>
                        <div class="col-md-8"><label>Judul kerjaan</label><input class="form-control" name="title" maxlength="200" required></div>
                        <div class="col-md-4"><label>Modul</label><input class="form-control" name="module" maxlength="100" placeholder="Contoh: Cutting"></div>
                        <div class="col-md-4"><label>Programmer</label><select class="wt-select-modal" name="programmer_id" id="wt-i-programmer"></select></div>
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
                    <p>Work Tracker adalah papan untuk mencatat semua kerjaan programmer dan melihat apakah kerjaan itu akan selesai tepat waktu. Bayangkan tiap programmer sedang lomba lari: makin banyak kerjaan yang beres, makin dekat ke garis finish.</p>

                    <div class="accordion" id="wt-guide-list">
                        <div class="accordion-item">
                            <h2 class="accordion-header"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#wt-g-start"><i class="fa-solid fa-rocket"></i> Mulai dalam 4 langkah</button></h2>
                            <div id="wt-g-start" class="accordion-collapse collapse show" data-bs-parent="#wt-guide-list">
                                <div class="accordion-body">
                                    <ol>
                                        <li><b>Daftarkan programmer.</b> Klik tombol <b>Programmer</b> di atas, pilih orangnya, pilih warna, lalu klik <b>Tambahkan</b>.</li>
                                        <li><b>Catat kerjaan.</b> Klik <b>Tambah Kerjaan</b>, isi judul, tanggal mulai, dan target selesai. Estimasi hari terisi sendiri.</li>
                                        <li><b>Tugaskan.</b> Pilih programmer di form. Kalau belum tahu siapa yang mengerjakan, kosongkan saja. Kerjaan akan menunggu di tab <b>Antrean</b>.</li>
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
                                    <p>Tiap baris adalah satu programmer. Posisi pelari menunjukkan progress gabungan semua kerjaannya.</p>
                                    <table class="table table-sm align-middle">
                                        <thead><tr><th>Yang terlihat</th><th>Artinya</th></tr></thead>
                                        <tbody>
                                            <tr><td class="text-nowrap"><i class="fa-solid fa-person-running text-success"></i> <b>Ngebut</b></td><td>Waktunya masih longgar. Aman.</td></tr>
                                            <tr><td class="text-nowrap"><i class="fa-solid fa-person-running text-warning"></i> <b>Jaga pace</b></td><td>Masih bisa keburu, tetapi mepet atau mulai tertinggal.</td></tr>
                                            <tr><td class="text-nowrap"><i class="fa-solid fa-person-walking text-danger"></i> <b>Ayo kejar</b></td><td>Waktunya kurang. Perlu dikejar, dibantu, atau targetnya dibicarakan ulang.</td></tr>
                                            <tr><td class="text-nowrap"><i class="fa-solid fa-mug-hot text-secondary"></i> <b>Santai</b></td><td>Belum ada kerjaan. Bisa diberi tugas baru.</td></tr>
                                            <tr><td class="text-nowrap"><i class="fa-solid fa-trophy text-warning"></i> <b>Semua beres</b></td><td>Semua kerjaan aktifnya sudah selesai.</td></tr>
                                            <tr><td class="text-nowrap"><i class="fa-solid fa-crown text-warning"></i> <b>Mahkota</b></td><td>Progress tertinggi saat ini.</td></tr>
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
                                    <p class="mt-2 mb-0 small text-muted">Pelari bisa berada di depan lingkaran yang belum selesai kalau programmer mengerjakan beberapa hal sekaligus. Warna lingkaran tetap menunjukkan status sebenarnya.</p>
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
                                    <p>Kalau ada <b>Bug</b> berlevel <b>Major</b> yang belum selesai dan targetnya hari ini atau sudah lewat, peringatan merah muncul di atas halaman dan rintangan muncul di lintasan programmernya.</p>
                                    <ul class="mb-0">
                                        <li><b>Pelari panik:</b> ada bug mendesak yang belum mulai dikerjakan.</li>
                                        <li><b>Pelari membetulkan rintangan:</b> bug sedang dikerjakan.</li>
                                        <li><b>Pelari melompat:</b> bug sudah selesai, dan rintangannya hilang.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#wt-g-tabs"><i class="fa-solid fa-table-columns"></i> Kegunaan tiap tab</button></h2>
                            <div id="wt-g-tabs" class="accordion-collapse collapse" data-bs-parent="#wt-guide-list">
                                <div class="accordion-body">
                                    <ul class="mb-0">
                                        <li><b>Daftar:</b> semua kerjaan, yang paling bermasalah di atas. Programmer bisa langsung diganti dari sini.</li>
                                        <li><b>Kanban:</b> kartu kerjaan per status. Tarik kartu ke kolom lain untuk mengubah statusnya.</li>
                                        <li><b>Timeline:</b> jadwal kerjaan per programmer. Arsiran merah menunjukkan perkiraan keterlambatan.</li>
                                        <li><b>Antrean:</b> kerjaan yang belum punya programmer. Pilih orangnya di dropdown, dan kerjaan langsung tersimpan.</li>
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
                                        <dt>Bug</dt><dd>Masalah atau error di sistem.</dd>
                                        <dt>Request</dt><dd>Permintaan dari user, misalnya tambah kolom di laporan.</dd>
                                        <dt>Major / Minor</dt><dd>Besar atau kecilnya dampak bug atau request.</dd>
                                        <dt>Tidak dikerjakan</dt><dd>Bug atau request yang diputuskan tidak dikerjakan. Alasannya wajib diisi.</dd>
                                        <dt>Estimasi</dt><dd>Perkiraan lama pengerjaan dalam hari. Terisi otomatis dari tanggal, dan sebaiknya diubah kalau pengerjaan sebenarnya lebih singkat.</dd>
                                        <dt>Pace</dt><dd>Kecepatan kerja, yaitu rata-rata persen progress per hari.</dd>
                                        <dt>Sisa beban</dt><dd>Perkiraan jumlah hari kerja yang masih dibutuhkan.</dd>
                                        <dt>Tersedia</dt><dd>Jumlah hari dari hari ini sampai target terjauh programmer itu.</dd>
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
                                        <dt>Bagaimana cara memindahkan kerjaan ke programmer lain?</dt><dd>Di tab <b>Daftar</b>, ganti nama pada kolom Programmer.</dd>
                                        <dt>Bagaimana cara menandai kerjaan selesai?</dt><dd>Geser progress ke 100%, atau tarik kartunya ke kolom <b>Selesai</b> di tab Kanban.</dd>
                                        <dt>Bagaimana cara menolak request?</dt><dd>Di form Edit, pilih level <b>Tidak dikerjakan</b> dan isi alasannya. Bisa juga dengan menarik kartunya ke kolom <b>Ditolak</b> di tab Kanban.</dd>
                                        <dt>Kerjaan lama tidak muncul, ke mana?</dt><dd>Kerjaan yang selesai atau ditolak lebih dari 60 hari lalu masuk arsip. Nyalakan <b>Tampilkan arsip</b>, atau cari lewat tab <b>Laporan</b>.</dd>
                                        <dt>Kenapa pelari saya berjalan pelan padahal kerjaan terkendali?</dt><dd>Periksa progress dan estimasinya. Kalau beberapa kerjaan dikerjakan bersamaan, estimasi otomatis bisa terlalu besar. Ubah angkanya sesuai lama pengerjaan sebenarnya.</dd>
                                        <dt>Bagaimana melihat kerjaan satu programmer saja?</dt><dd>Klik barisnya di Arena. Klik sekali lagi untuk kembali melihat semua.</dd>
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
                    <h5 class="modal-title">Programmer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div id="wt-programmer-list" class="mb-3"></div>
                    <label>Tambah dari user</label>
                    <select id="wt-p-user" class="mb-2"></select>
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
                remove: @json(route('delete-work-tracker')),
                programmerStore: @json(route('store-programmer-work-tracker')),
                programmerDelete: @json(route('delete-programmer-work-tracker')),
            };
            const COLORS = ['#2f80ed', '#9b51e0', '#f2994a', '#27ae60', '#eb5757', '#00a3a3', '#d6336c', '#5c6ac4'];
            const TYPE_LABEL = { plan: 'Plan', bug: 'Bug', request: 'Request' };
            const TYPE_ICON = { plan: 'fa-compass-drafting', bug: 'fa-bug', request: 'fa-comment-dots' };
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

            const state = { board: null, demo: false, view: 'list', selected: null, color: COLORS[0], current: null, pos: {}, jumpers: [], urgentIds: [], reportItems: [], reportTimer: null, reportSeq: 0, statusBeforeReport: null, estimateManual: false };
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
            }

            function assignOptionText(p) {
                if (!p.active) return p.name + ' (belum ada kerjaan)';
                const load = 'sisa ' + num(p.effort_left) + ' hari';
                return p.name + ' (' + (p.available_days === null ? load : load + ', tersedia ' + p.available_days) + ')';
            }

            function assignSelect(item, emptyText) {
                const current = item.programmer_id === null ? '' : Number(item.programmer_id);
                const options = state.board.programmers.map(function (p) {
                    return '<option value="' + p.id + '" data-name="' + esc(p.name) + '"' + (p.id === current ? ' selected' : '') + '>' + esc(assignOptionText(p)) + '</option>';
                }).join('');
                return '<div class="wt-assign"><select class="wt-assign-select" data-id="' + item.id + '" data-current="' + current + '" aria-label="Programmer untuk ' + esc(item.title) + '"><option value="">' + esc(emptyText) + '</option>' + options + '</select></div>';
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
                        '<div>' + (noProgrammer ? '<span class="wt-sub">Tambah programmer dulu</span>' : assignSelect(i, 'Pilih programmer')) + '</div></div>';
                }).join(''));
                initAssignSelects($view);
            }

            function setView(view) {
                state.view = view;
                $('#wt-tabs button').each(function () { $(this).toggleClass('active', this.dataset.view === view); });
                $('#wt-f-programmer-box, #wt-f-health-box').toggleClass('d-none', view === 'queue');
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
                    from: $('#wt-f-from').val(), to: $('#wt-f-to').val(), programmer: $('#wt-f-programmer').val(), type: $('#wt-f-type').val(),
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
                        '<td>' + typeChip(i) + ' ' + levelChip(i) + '</td><td>' + esc(i.programmer_name || 'Belum ditugaskan') + '</td>' +
                        '<td>' + esc(STATUS_LABEL[i.status]) + '</td><td><b>' + Number(i.progress) + '%</b></td>' +
                        '<td>' + fmt(i.start_date, true) + '</td><td>' + fmt(i.target_date, true) + '</td><td>' + fmt(i.finished_at, true) + '</td><td>' + healthChip(i) + '</td></tr>';
                }).join('');
                $view.html(header + '<div class="table-responsive"><table class="wt-table"><thead><tr><th>Kerjaan</th><th>Jenis</th><th>Programmer</th><th>Status</th><th>Progress</th><th>Mulai</th><th>Target</th><th>Selesai</th><th>Kondisi</th></tr></thead><tbody>' + rows + '</tbody></table></div>');
            }

            function assignProgrammer(id, programmerId) {
                if (guardDemo()) {
                    render();
                    return;
                }
                const request = post(ROUTES.assign, { id: id, programmer_id: programmerId }, function (res) { toast(res.message); });
                if (request) request.always(load);
            }

            function renderTiles() {
                const s = state.board.summary;
                const tiles = [
                    ['Kerjaan aktif', s.active, 'fa-layer-group', '#e7f0ff', '#1b5fc1'],
                    ['Diprediksi telat', s.late, 'fa-fire', '#ffe3e6', '#c21f30'],
                    ['Mepet target', s.tight, 'fa-hourglass-half', '#fff2d6', '#a86700'],
                    ['Bug terbuka', s.bugs_open, 'fa-bug', '#ffe9ec', '#c92a3c'],
                    ['Request baru', s.requests_new, 'fa-comment-dots', '#f1e9ff', '#7338c9'],
                    ['Sisa beban (hari)', num(s.effort_left), 'fa-stopwatch', '#e0f7ea', '#0c7f3e'],
                    ['Belum ditugaskan', s.unassigned, 'fa-inbox', '#fff2d6', '#a86700', 'queue'],
                ];
                $('#wt-tiles').html(tiles.map(function (t, i) {
                    const link = t[5] ? ' wt-tile-link" role="button" tabindex="0" title="Buka tab Antrean" data-goto="' + t[5] : '';
                    return '<div class="wt-card wt-tile' + link + '" style="animation-delay:' + (i * 50) + 'ms">' +
                        '<div class="wt-tile-icon" style="background:' + t[3] + ';color:' + t[4] + '"><i class="fa-solid ' + t[2] + '"></i></div>' +
                        '<div><div class="wt-tile-value">' + esc(t[1]) + '</div><div class="wt-tile-label">' + esc(t[0]) + '</div></div></div>';
                }).join(''));
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

            function trackDecor(p, index) {
                const speed = { sprint: 9, jog: 16, walk: 30, rest: 46, finish: 22 }[p.tier];
                let html = '<i class="fa-solid fa-sun wt-sun"></i>' +
                    '<i class="fa-solid fa-cloud wt-cloud" style="top:26px;--dur:' + speed + 's;animation-delay:-' + (index * 3 + 2) + 's"></i>' +
                    '<i class="fa-solid fa-cloud wt-cloud mini" style="top:44px;--dur:' + (speed * 1.5) + 's;animation-delay:-' + (index * 5 + 9) + 's"></i>' +
                    '<i class="fa-solid fa-dove wt-bird" style="animation-delay:-' + (index * 6 + 4) + 's"></i>';
                const cheering = p.tier === 'finish' || p.progress >= 70;
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
                    return '<button type="button" class="wt-cp-label cp-' + kind + '" data-item="' + c.id + '" title="' + title + '" style="left:' + labelLeft + ';width:' + labelWidth + '">' + esc(c.title) + '</button>' +
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

            function renderLanes() {
                const programmers = state.board.programmers;
                if (!programmers.length) {
                    $('#wt-lanes').html('<div class="wt-empty"><i class="fa-solid fa-person-running"></i>Belum ada programmer di arena.<div class="mt-3 d-flex gap-2 justify-content-center flex-wrap">' +
                        '<button class="btn btn-sb btn-sm" id="wt-empty-programmer"><i class="fa-solid fa-user-plus"></i> Tambah Programmer</button>' +
                        '<button class="btn btn-outline-secondary btn-sm" id="wt-demo-start"><i class="fa-solid fa-eye"></i> Lihat Contoh Tampilan</button></div></div>');
                    return;
                }

                const road = { sprint: '.45s', jog: '1s', walk: '2.6s', rest: '0s', finish: '0s' };
                const contenders = programmers.filter(function (p) { return p.active > 0 && p.progress > 0; });
                const leader = contenders.length > 1 ? contenders.reduce(function (best, p) { return p.progress > best.progress ? p : best; }) : null;

                $('#wt-lanes').html(programmers.map(function (p, index) {
                    const selected = state.selected === p.id ? ' selected' : '';
                    const incident = p.incident ? ' incident-' + p.incident : '';
                    const pos = 'calc((100% - 72px) * ' + (p.progress / 100) + ')';
                    const pacerLeft = 'calc((100% - 72px) * ' + (p.expected_progress / 100) + ' + 27px)';
                    const pacer = p.active ? '<div class="wt-pacer" data-left="' + pacerLeft + '" style="left:' + (state.pos[p.id] ? pacerLeft : '27px') + '" title="Posisi seharusnya hari ini: ' + p.expected_progress + '%"></div>' : '';
                    const jump = state.jumpers.includes(p.id) ? ' jump' : '';
                    const crown = leader && leader.id === p.id ? '<i class="fa-solid fa-crown wt-crown" title="Progress tertinggi"></i> ' : '';
                    const obstacle = p.incident ? '<div class="wt-obstacle" style="left:min((100% - 72px) * ' + (p.progress / 100) + ' + 60px, 100% - 56px)" title="' + esc(p.urgent[0].title) + '"><i class="fa-solid fa-road-barrier"></i></div>' : '';
                    const sayCorner = p.progress >= 70 ? ' left' : '';
                    return '<div class="wt-lane tier-' + p.tier + incident + selected + '" data-programmer="' + p.id + '" title="Klik untuk filter kerjaan programmer ini">' +
                        '<div class="wt-person"><div class="wt-avatar" style="background:' + esc(p.color) + '">' + esc(initials(p.name)) + '</div>' +
                        '<div style="min-width:0"><div class="wt-person-name">' + esc(p.name) + '</div>' +
                        '<div class="wt-person-sub">' + p.active + ' aktif' + (p.late ? ', ' + p.late + ' telat' : '') + '</div></div></div>' +
                        '<div class="wt-track">' + trackDecor(p, index) + '<div class="wt-track-road" style="--road:' + (p.incident ? '0s' : road[p.tier]) + '"></div>' + pacer +
                        checkpointsHtml(p) + obstacle + '<div class="wt-finish"></div>' +
                        '<div class="wt-runner-box' + jump + '" data-programmer-box="' + p.id + '" data-left="' + pos + '" style="left:' + (state.pos[p.id] || '0') + '">' +
                        '<div class="wt-pct">' + crown + p.progress + '%</div>' +
                        '<div class="wt-say' + sayCorner + '" data-kind="' + sayKind(p) + '">' + esc((SAY[sayKind(p)] || [''])[0]) + '</div>' + runnerSvg(p) + '</div></div>' +
                        '<div class="wt-lane-note tier-' + p.tier + (p.incident ? ' incident' : '') + '">' + laneNote(p) + '</div></div>';
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
                $view.html('<div class="table-responsive"><table class="wt-table"><thead><tr><th>Kerjaan</th><th>Jenis</th><th>Programmer</th><th>Progress</th><th>Target</th><th>Prediksi</th><th>Kondisi</th></tr></thead><tbody>' + rows + '</tbody></table></div>');
                initAssignSelects($view);
            }

            function renderKanban(items) {
                const html = Object.keys(STATUS_LABEL).map(function (status) {
                    const own = items.filter(function (i) { return i.status === status; });
                    const cards = own.map(function (i) {
                        return '<div class="wt-kcard b-' + esc(i.health) + '" draggable="true" data-id="' + i.id + '">' +
                            '<div class="d-flex justify-content-between gap-1 mb-1">' + typeChip(i) + levelChip(i) + '</div>' +
                            '<div class="wt-title mb-1">' + esc(i.title) + '</div>' +
                            '<div class="wt-bar mb-1"><span class="fill-' + esc(i.health) + '" style="width:' + Number(i.progress) + '%"></span></div>' +
                            '<div class="d-flex justify-content-between wt-sub"><span>' + Number(i.progress) + '% · ' + fmt(i.target_date) + '</span>' + (programmerOf(i) ? '<span>' + esc(initials(programmerOf(i).name)) + '</span>' : '') + '</div></div>';
                    }).join('');
                    return '<div class="wt-col" data-status="' + status + '"><div class="wt-col-head"><span>' + STATUS_LABEL[status] + '</span><span class="text-muted">' + own.length + '</span></div>' + cards + '</div>';
                }).join('');
                $view.html('<div class="wt-kanban">' + html + '</div>');
            }

            function renderTimeline(items) {
                const today = state.board.today;
                const dated = items.filter(function (i) { return i.start_date || i.target_date || i.predicted_date; });
                if (!dated.length) {
                    $view.html('<div class="wt-empty"><i class="fa-solid fa-chart-gantt"></i>Belum ada kerjaan dengan tanggal.</div>');
                    return;
                }

                let min = today, max = today;
                dated.forEach(function (i) {
                    [i.start_date, i.target_date, i.predicted_date].forEach(function (d) {
                        if (!d) return;
                        if (d < min) min = d;
                        if (d > max) max = d;
                    });
                });
                const span = Math.max(7, days(min, max) + 2);
                const percent = function (iso) { return (days(min, iso) / span) * 100; };
                const dayWidth = 100 / span;
                const step = Math.max(1, Math.ceil(span / 14));
                let ticks = '';
                for (let d = 0; d < span; d += step) {
                    const date = parseDate(min);
                    date.setDate(date.getDate() + d);
                    ticks += '<span class="wt-gantt-tick" style="left:' + (d / span * 100) + '%">' + date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }) + '</span>';
                }

                const groups = state.board.programmers.map(function (p) { return { id: p.id, name: p.name, color: p.color }; });
                groups.push({ id: null, name: 'Belum ditugaskan', color: '#9aa6b8' });

                const fills = { aman: '#12a150', selesai: '#12a150', mepet: '#e08a00', telat: '#dc3545', selesai_telat: '#dc3545', belum_dinilai: '#7f8ca1', ditolak: '#7f8ca1' };
                const body = groups.map(function (g) {
                    const own = dated.filter(function (i) { return (i.programmer_id === null ? null : Number(i.programmer_id)) === g.id; });
                    if (!own.length) return '';
                    return '<div class="wt-gantt-group" style="border-left:4px solid ' + esc(g.color) + '">' + esc(g.name) + '</div>' + own.map(function (i) {
                        const start = i.start_date || i.target_date || i.predicted_date;
                        const planEnd = i.target_date || i.predicted_date || start;
                        const end = planEnd < start ? start : planEnd;
                        let over = '';
                        if (i.health === 'telat' && i.predicted_date && i.predicted_date > end) {
                            const overStart = percent(end) + dayWidth;
                            over = '<div class="wt-gantt-over" style="left:' + overStart + '%;width:' + (days(end, i.predicted_date) * dayWidth) + '%" title="Prediksi telat sampai ' + fmt(i.predicted_date) + '"></div>';
                        }
                        return '<div class="wt-gantt-row" data-id="' + i.id + '"><div class="wt-gantt-label" title="' + esc(i.title) + '">' + esc(i.title) + '</div>' +
                            '<div class="wt-gantt-area"><div class="wt-gantt-bar" style="left:' + percent(start) + '%;width:' + ((days(start, end) + 1) * dayWidth) + '%" title="' + esc(fmt(start) + ' sampai ' + fmt(end) + ', ' + i.progress + '%') + '">' +
                            '<span style="width:' + Number(i.progress) + '%;background:' + fills[i.health] + '"></span></div>' + over + '</div></div>';
                    }).join('');
                }).join('');

                const inner = '<div class="wt-gantt"><div class="wt-gantt-inner"><div class="wt-gantt-row" style="border:0"><div></div><div class="wt-gantt-head">' + ticks + '</div></div>' + body +
                    '<div class="wt-gantt-today-wrap" style="position:absolute;top:0;bottom:0;left:220px;right:0;pointer-events:none"><div class="wt-gantt-today" style="left:' + percent(today) + '%"></div></div></div></div>';
                $view.html(inner);
            }

            function fillProgrammerSelects() {
                const programmers = state.board.programmers;
                const filterValue = $('#wt-f-programmer').val();
                const options = programmers.map(function (p) { return '<option value="' + p.id + '">' + esc(p.name) + '</option>'; }).join('');
                $('#wt-f-programmer').html('<option value="">Semua programmer</option><option value="none">Belum ditugaskan</option>' + options).val(filterValue || '').trigger('change.select2');
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
                    ['Programmer', programmerOf(item) ? programmerOf(item).name : 'Belum ditugaskan'], ['Mulai', fmt(item.start_date, true)], ['Target', fmt(item.target_date, true)],
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

            function celebrate() {
                if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
                const colors = ['#2f80ed', '#12a150', '#f2c94c', '#eb5757', '#9b51e0'];
                for (let n = 0; n < 70; n++) {
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
                toast('Mantap, satu kerjaan beres!');
            }

            function moveStatus(id, status) {
                const item = state.board.items.find(function (i) { return i.id === id; });
                if (!item || item.status === status) return;
                if (guardDemo()) return;
                const send = function (reason) {
                    post(ROUTES.status, { id: id, status: status, reason: reason }, function (res) {
                        if (res.just_finished) celebrate();
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

            function renderProgrammerList() {
                $('#wt-programmer-list').html(state.board.programmers.length ? state.board.programmers.map(function (p) {
                    return '<div class="d-flex align-items-center gap-2 py-1"><span class="wt-avatar wt-mini-avatar" style="background:' + esc(p.color) + '">' + esc(initials(p.name)) + '</span><span class="flex-grow-1 small fw-bold">' + esc(p.name) + '</span>' +
                        '<button type="button" class="btn btn-sm btn-link text-danger p-0" data-remove-programmer="' + p.id + '" aria-label="Nonaktifkan ' + esc(p.name) + '"><i class="fa-solid fa-user-minus"></i></button></div>';
                }).join('') : '<div class="wt-sub">Belum ada programmer.</div>');
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

            $view.on('click', 'tbody tr, .wt-kcard, .wt-gantt-row[data-id]', function (e) {
                if ($(e.target).closest('.wt-assign').length) return;
                openDetail(Number(this.dataset.id));
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
                post(ROUTES.progress, { id: state.current.id, progress: $('#wt-range').val(), note: $('#wt-note').val() }, function (res) {
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('wt-detail-modal')).hide();
                    toast(res.message);
                    if (res.just_finished) celebrate();
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
                post(ROUTES.programmerStore, { user_id: userId, color: state.color }, function (res) {
                    toast(res.message);
                    load().done(function () { renderProgrammerList(); loadUsers(); });
                });
            });

            $('#wt-programmer-list').on('click', '[data-remove-programmer]', function () {
                const id = this.dataset.removeProgrammer;
                if (guardDemo()) return;
                Swal.fire({ icon: 'warning', title: 'Nonaktifkan programmer ini?', text: 'Kerjaan yang masih aktif akan kembali ke Belum ditugaskan.', showCancelButton: true, confirmButtonText: 'Nonaktifkan', cancelButtonText: 'Batal', confirmButtonColor: '#dc3545' }).then(function (r) {
                    if (!r.isConfirmed) return;
                    post(ROUTES.programmerDelete, { id: id }, function (res) {
                        toast(res.message);
                        load().done(function () { renderProgrammerList(); loadUsers(); });
                    });
                });
            });

            load();
        })();
    </script>
@endsection
