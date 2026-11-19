{{--
    Style panduan halaman (modal panduan, peta halaman, sorotan tur).
    Pola sama dengan panduan di cutting/penerimaan-cutting/create-penerimaan-cutting.
--}}
<style>
    :root {
        --pg-pri: #082149;
        --pg-sec: #238380;
        --pg-acc: #ff971f;
        --pg-ink: #2b2f36;
        --pg-muted: #6c757d;
        --pg-line: #e2e6ec;
        --pg-soft: #f4f7fb;
    }

    /* Modal panduan */
    .pg-guide .modal-header .lbl { display: block; font-size: 10.5px; font-weight: 700; letter-spacing: .8px; text-transform: uppercase; opacity: .7; }
    .pg-g { padding: 0 20px 20px; color: var(--pg-ink); font-size: 13.5px; scroll-behavior: smooth; }
    .pg-g-nav { position: sticky; top: 0; z-index: 2; display: flex; flex-wrap: wrap; gap: 6px; padding: 12px 0; margin-bottom: 4px; background: #fff; border-bottom: 1px solid var(--pg-line); }
    .pg-g-nav a { padding: 4px 10px; border: 1px solid var(--pg-line); border-radius: 20px; background: var(--pg-soft); color: var(--pg-pri); font-size: 12px; font-weight: 600; text-decoration: none; }
    .pg-g-nav a:hover { background: var(--pg-pri); color: #fff; }
    .pg-g section { padding-top: 16px; scroll-margin-top: 56px; }
    .pg-g h6 { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 10px; padding-left: 8px; border-left: 4px solid var(--pg-sec); color: var(--pg-pri); font-weight: 700; }
    .pg-g ul { margin-bottom: 0; padding-left: 18px; }
    .pg-g li { margin-bottom: 6px; line-height: 1.5; }
    .pg-g-t { margin-bottom: 0; }
    .pg-g-t td { padding: 7px 8px; line-height: 1.5; vertical-align: top; }
    .pg-g-t td:first-child { width: 210px; color: var(--pg-pri); white-space: nowrap; }
    .pg-g-btn { display: inline-block; padding: 2px 8px; border: 1px solid #c5ccd6; border-radius: .3rem; background: #fff; font-size: 12px; }
    .pg-g-note { margin-bottom: 10px; color: var(--pg-muted); font-size: 12.5px; }
    .pg-g kbd { padding: 1px 5px; font-size: 11px; }
    .pg-g-flow { display: flex; flex-wrap: wrap; align-items: stretch; gap: 8px; margin-bottom: 12px; }
    .pg-g-flow > div { flex: 1; min-width: 150px; padding: 8px 12px; border: 1px solid var(--pg-line); border-radius: .5rem; background: var(--pg-soft); }
    .pg-g-flow > div.ok { border-color: #b9dfc8; background: #e6f4ec; }
    .pg-g-flow b { display: block; color: var(--pg-pri); }
    .pg-g-flow span { color: var(--pg-muted); font-size: 12px; }
    .pg-g-flow > i { align-self: center; color: var(--pg-sec); font-style: normal; font-weight: 700; }
    .pg-g-faq dt { margin-top: 8px; color: var(--pg-pri); }
    .pg-g-faq dd { margin: 2px 0 0; }
    .pg-g-show { padding: 2px 10px; border: 1px solid var(--pg-sec); border-radius: 20px; background: #fff; color: var(--pg-sec); font-size: 11.5px; font-weight: 600; }
    .pg-g-show:hover { background: var(--pg-sec); color: #fff; }
    .pg-g-show.pri { background: var(--pg-sec); color: #fff; }
    .pg-g-show.pri:hover { filter: brightness(.92); }

    /* Contoh tampilan kecil di dalam panduan */
    .pg-g-chip { display: inline-flex; align-items: center; gap: 4px; padding: 1px 8px; border: 1px solid #b6d4fe; border-radius: 999px; background: #e7f1ff; color: #084298; font-size: 11.5px; }
    .pg-g-dot { display: inline-block; width: 10px; height: 10px; border: 1px solid rgba(0, 0, 0, .25); border-radius: 50%; vertical-align: middle; }
    .pg-g-dot.unknown { border: 1px dashed rgba(108, 117, 125, .6); background: rgba(108, 117, 125, .25); }
    .pg-g-line { display: inline-block; width: 4px; height: 14px; border-radius: 2px; vertical-align: middle; }
    .pg-g-link { color: #0d6efd; font-weight: 700; text-decoration: underline dotted; text-underline-offset: 3px; }

    /* Peta halaman */
    .pg-map { max-width: 760px; padding: 14px; border: 1px solid var(--pg-line); border-radius: .6rem; background: var(--pg-soft); }
    .pg-map-z { position: relative; cursor: pointer; transition: box-shadow .15s; }
    .pg-map-z:hover { z-index: 6; box-shadow: 0 0 0 2px var(--pg-acc); }
    .pg-map-n { position: absolute; top: -9px; left: -9px; z-index: 2; display: grid; place-items: center; width: 22px; height: 22px; border-radius: 50%; background: var(--pg-acc); box-shadow: 0 0 0 2px #fff; color: #fff !important; font-size: 12px; font-weight: 700; text-decoration: none; }
    .pg-map-n.sm { top: -7px; left: -7px; width: 18px; height: 18px; font-size: 10.5px; }
    .pg-map-n:hover, .pg-map-n:focus-visible { z-index: 5; background: var(--pg-pri); }
    .pg-map-tip { position: absolute; top: calc(100% + 8px); left: -4px; width: 230px; padding: 8px 10px; border-radius: .45rem; background: var(--pg-pri); box-shadow: 0 6px 18px rgba(8, 33, 73, .3); color: #fff; font-size: 12px; font-weight: 400; line-height: 1.45; text-align: left; opacity: 0; visibility: hidden; transform: translateY(-4px); transition: opacity .15s, transform .15s, visibility .15s; pointer-events: none; }
    .pg-map-tip:before { content: ""; position: absolute; top: -5px; left: 10px; width: 10px; height: 10px; background: var(--pg-pri); transform: rotate(45deg); }
    .pg-map-n.tip-l .pg-map-tip { right: -4px; left: auto; }
    .pg-map-n.tip-l .pg-map-tip:before { right: 10px; left: auto; }
    .pg-map-tip b { display: block; margin-bottom: 2px; color: #9fd6d3; font-size: 12.5px; }
    .pg-map-tip small { display: block; margin-top: 5px; color: rgba(255, 255, 255, .65); font-size: 10.5px; }
    .pg-map-n:hover .pg-map-tip, .pg-map-n:focus-visible .pg-map-tip { opacity: 1; visibility: visible; transform: none; }
    .pg-map-head { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 12px; }
    .pg-map-head .t b { display: block; color: var(--pg-pri); font-size: 12.5px; }
    .pg-map-head .t span { color: var(--pg-muted); font-size: 10.5px; }
    .pg-map-head .r { display: flex; gap: 4px; padding: 4px; border-radius: .35rem; }
    .pg-map-head .r i { width: 34px; height: 14px; border: 1px solid #b8c2cf; border-radius: 3px; background: #fff; }
    .pg-map-head .r i.p { border-color: #2c7be5; background: #2c7be5; }
    .pg-map-head .r i.s { border-color: var(--pg-sec); background: var(--pg-sec); }
    .pg-map-stack { display: flex; flex-direction: column; gap: 12px; }
    .pg-map-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .pg-map-card { padding: 0 8px 8px; border: 1px solid var(--pg-line); border-radius: .45rem; background: #fff; }
    .pg-map-card .h { margin: 0 -8px 8px; padding: 4px 8px 4px 18px; border-radius: .45rem .45rem 0 0; background: var(--pg-pri); color: #fff; font-size: 10px; font-weight: 700; letter-spacing: .6px; text-transform: uppercase; }
    .pg-map-card .h.b { background: #2c7be5; }
    .pg-map-card .inp { display: flex; gap: 4px; margin-bottom: 6px; }
    .pg-map-card .inp span { flex: 1; padding: 3px 6px; border: 1.5px solid #ced4da; border-radius: .3rem; color: #9aa4b1; font-size: 10px; }
    .pg-map-card .inp i { padding: 3px 8px; border-radius: .3rem; font-size: 10px; font-style: normal; }
    .pg-map-card .inp i.g { border: 1px solid #198754; color: #198754; }
    .pg-map-card .inp i.c { border: 1px solid #2c7be5; color: #2c7be5; }
    .pg-map-card .fl { display: grid; grid-template-columns: repeat(4, 1fr); gap: 4px; }
    .pg-map-card .fl i { height: 12px; border: 1px solid #e2e6ec; border-radius: 3px; background: #f8f9fa; }
    .pg-map-card .save { height: 12px; margin-top: 6px; border-radius: 3px; background: #198754; }
    .pg-map-tools { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; margin-bottom: 8px; }
    .pg-map-tools > span { display: inline-flex; align-items: center; height: 16px; padding: 0 6px; border: 1px solid #c5ccd6; border-radius: .3rem; background: #fff; color: #6c757d; font-size: 9.5px; }
    .pg-map-tools > span.btn-f { border-color: #0d6efd; color: #0d6efd; }
    .pg-map-tools > span.sum { border: 0; border-radius: 999px; background: #e9ecef; }
    .pg-map-tools > span.bon { width: 70px; }
    .pg-map-tools > span.kirim { border-color: #198754; background: #198754; color: #fff; }
    .pg-map-tools .sp { flex: 1; }
    .pg-map-tbl { display: grid; grid-template-columns: 18px 1.2fr 1.4fr .8fr .8fr 1fr; gap: 3px; }
    .pg-map-tbl i { height: 10px; border-radius: 2px; background: #e9edf2; }
    .pg-map-tbl i.hd { background: #c9d3df; }
    .pg-map-tbl i.lk { background: #b6d4fe; }
    .pg-map-tbl i.ck { background: #fff; border: 1px solid #adb5bd; }
    .pg-map-tbl i.sel { background: #d1e7ff; }
    .pg-map-tbl i.del { background: #f1aeb5; }
    .pg-map-bar { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; margin-top: 10px; padding: 6px 8px; border-top: 2px solid #0d6efd; border-radius: .35rem; background: #fff; box-shadow: 0 -3px 8px rgba(0, 0, 0, .08); font-size: 9.5px; color: #495057; }
    .pg-map-bar span { padding: 1px 7px; border-radius: 999px; background: #e7f1ff; }
    .pg-map-bar .sp { flex: 1; background: none; }
    .pg-map-switch { display: flex; justify-content: center; margin-bottom: 8px; }
    .pg-map-switch span { display: inline-flex; align-items: center; gap: 5px; padding: 2px 8px; font-size: 10px; color: var(--pg-pri); }
    .pg-map-switch span:before { content: ""; width: 20px; height: 10px; border-radius: 6px; background: #0d6efd; }

    /* Sorotan tur */
    .pg-spot { position: fixed; z-index: 2000; border-radius: .6rem; box-shadow: 0 0 0 9999px rgba(8, 33, 73, .6); outline: 3px solid var(--pg-acc); outline-offset: 3px; pointer-events: none; animation: pgSpot 1.2s ease-in-out infinite; }
    @keyframes pgSpot { 50% { outline-color: rgba(255, 151, 31, .6); outline-offset: 8px; } }
    .pg-spot-dim { position: fixed; inset: 0; z-index: 2000; background: rgba(8, 33, 73, .6); pointer-events: none; }
    .pg-spot-l { position: fixed; z-index: 2001; width: 340px; max-width: calc(100vw - 16px); padding: 10px 12px; border-radius: .5rem; background: #fff; box-shadow: 0 6px 18px rgba(0, 0, 0, .25); color: var(--pg-pri); font-size: 13px; }
    .pg-spot-l .st { display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; color: var(--pg-sec); font-size: 10.5px; font-weight: 700; letter-spacing: .6px; text-transform: uppercase; }
    .pg-spot-l .dots { display: flex; gap: 4px; }
    .pg-spot-l .dots i { width: 7px; height: 7px; border-radius: 50%; background: #e2e6ec; }
    .pg-spot-l .dots i.done { background: #9fd6d3; }
    .pg-spot-l .dots i.on { width: 16px; border-radius: 4px; background: var(--pg-acc); }
    .pg-spot-l > b { display: block; font-weight: 700; }
    .pg-spot-l > span.d { display: block; margin-top: 3px; color: #3d4450; font-size: 12.5px; line-height: 1.5; }
    .pg-spot-l > span.d b { color: var(--pg-pri); }
    .pg-spot-l > span.m { display: block; margin-top: 6px; padding: 5px 8px; border-radius: .35rem; background: #fff3cd; color: #664d03; font-size: 12px; }
    .pg-spot-l .nav { display: flex; justify-content: space-between; gap: 8px; margin-top: 10px; }
    .pg-spot-l .nav button { padding: 4px 12px; border: 1px solid #c5ccd6; border-radius: .35rem; background: #fff; color: var(--pg-pri); font-size: 12.5px; font-weight: 600; }
    .pg-spot-l .nav button:disabled { opacity: .4; }
    .pg-spot-l .nav button.pri { border-color: var(--pg-sec); background: var(--pg-sec); color: #fff; }
    .pg-spot-l .nav button.skip { border-color: transparent; background: none; color: var(--pg-muted); font-weight: 500; text-decoration: underline; text-underline-offset: 3px; }
    .pg-spot-l .nav button.skip:hover { color: var(--pg-pri); filter: none; }
    .pg-spot-l .nav button:not(:disabled):hover { filter: brightness(.95); }
    .pg-spot-l small { display: block; margin-top: 6px; padding-top: 5px; border-top: 1px solid #e2e6ec; color: var(--pg-muted); font-size: 11.5px; font-weight: 400; }
    .pg-spot-l kbd { padding: 0 4px; font-size: 10px; }
    .pg-spot-cv { position: fixed; inset: 0; z-index: 1999; cursor: pointer; }
    .pg-tour-wait { position: fixed; inset: 0; z-index: 1998; display: grid; place-items: center; background: rgba(8, 33, 73, .45); cursor: wait; animation: pgTwIn .2s ease-out; }
    .pg-tour-wait > div { display: flex; align-items: center; gap: 10px; padding: 10px 18px; border-radius: 2rem; background: #fff; box-shadow: 0 8px 24px rgba(0, 0, 0, .25); color: var(--pg-pri); font-size: 13.5px; font-weight: 600; }
    .pg-tour-wait .spinner-border { color: var(--pg-sec); }
    @keyframes pgTwIn { from { opacity: 0; } }

    @media (max-width: 576px) {
        .pg-g-t td:first-child { width: auto; white-space: normal; }
        .pg-g-flow > i { display: none; }
        .pg-map-row { grid-template-columns: 1fr; }
    }

    @media (prefers-reduced-motion: reduce) {
        .pg-spot, .pg-tour-wait { animation: none; }
    }
</style>
