@extends('layouts.index')

@section('custom-link')
    <style>
        .pc-wrap, #pcModal {
            --pc-pri: var(--sb-color, #082149);
            --pc-pri2: #1d4a8a;
            --pc-pri-lt: #e9eef6;
            --pc-sec: var(--sb-secondary-color, #238380);
            --pc-sec-lt: #e5f2f1;
            --pc-ok: #2e8a57;
            --pc-ok-lt: #e8f6ee;
            --pc-warn: #d88202;
            --pc-warn-lt: #fff5e6;
            --pc-err: #d33141;
            --pc-err-lt: #fdedef;
            --pc-acc: #ff971f;
            --pc-ink: #212529;
            --pc-ink2: #3d4450;
            --pc-muted: #6c757d;
            --pc-faint: #adb5bd;
            --pc-line: #e2e6ec;
            --pc-soft: #f4f6f9;
            color: var(--pc-ink);
        }
        .pc-wrap .mono, #pcModal .mono { font-family: ui-monospace, SFMono-Regular, Consolas, "Liberation Mono", monospace; }
        .pc-wrap .lbl { font-size: 11px; font-weight: 700; letter-spacing: 1.1px; text-transform: uppercase; }

        /* head */
        .pc-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 14px; }
        .pc-head h5 { margin: 0; }
        .pc-head p { margin: 2px 0 0; color: var(--pc-muted); font-size: 13.5px; }
        .pc-head-r { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
        .pc-head-r .form-select { width: auto; font-weight: 600; color: var(--pc-pri); }

        .pc-grid { display: grid; grid-template-columns: minmax(0, 1fr) 320px; gap: 16px; align-items: start; margin-bottom: 15px; }
        .pc-col, .pc-side { display: flex; flex-direction: column; gap: 16px; min-width: 0; }
        .pc-card { background: #fff; border: 1px solid var(--pc-line); border-radius: .5rem; box-shadow: 0 1px 3px rgba(0,0,0,.06); overflow: hidden; }
        .pc-card-h { display: flex; align-items: center; gap: 10px; padding: 10px 16px; background: var(--pc-pri); color: #fff; flex-wrap: wrap; }
        .pc-card-h .lbl { color: #fff; }
        .pc-card-h .r { margin-left: auto; font-size: 12.5px; color: #c9d4e6; }

        /* scan */
        .pc-scan-b { padding: 16px; }
        .pc-inp-row { display: flex; gap: 8px; }
        .pc-inp { position: relative; flex: 1; min-width: 0; }
        .pc-inp svg { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); width: 22px; height: 22px; color: var(--pc-faint); }
        #pc-scan { width: 100%; height: 58px; border-radius: .5rem; border: 2px solid var(--pc-line); background: var(--pc-soft); padding: 0 16px 0 50px; font-size: 21px; font-family: ui-monospace, Consolas, monospace; letter-spacing: 1px; outline: none; transition: .15s; }
        #pc-scan:focus { background: #fff; border-color: var(--pc-sec); box-shadow: 0 0 0 4px rgba(35,131,128,.15); }
        #pc-scan::placeholder { font-family: Roboto, Nunito, sans-serif; font-size: 16px; letter-spacing: 0; color: var(--pc-faint); }
        .pc-inp:focus-within svg { color: var(--pc-sec); }
        .pc-inp:after { content: ""; position: absolute; left: 50px; right: 16px; bottom: 9px; border-bottom: 2px dashed var(--pc-sec); transform: scaleX(0); transform-origin: left; opacity: 0; pointer-events: none; }
        .pc-inp.sew:after { animation: pcSew .5s ease-out; }
        @keyframes pcSew { 0% { transform: scaleX(0); opacity: 1 } 75% { transform: scaleX(1); opacity: 1 } 100% { transform: scaleX(1); opacity: 0 } }
        .pc-inp-row .btn { min-width: 64px; font-weight: 600; }
        .pc-under { display: flex; align-items: center; gap: 8px; margin-top: 10px; font-size: 12.5px; color: var(--pc-muted); flex-wrap: wrap; }
        .pc-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--pc-ok); box-shadow: 0 0 0 3px rgba(46,138,87,.2); }
        .pc-dot.busy { background: var(--pc-acc); box-shadow: 0 0 0 3px rgba(255,151,31,.25); animation: pcPulse 1s infinite; }
        @keyframes pcPulse { 50% { opacity: .4 } }
        .pc-under .hint { margin-left: auto; }
        .pc-under kbd { background: var(--pc-soft); color: var(--pc-ink2); border: 1px solid var(--pc-line); font-size: 11px; padding: 0 5px; }
        #pc-reader-wrap { margin-top: 12px; }
        #pc-reader { max-width: 420px; margin: 0 auto; border-radius: .5rem; overflow: hidden; }

        /* hasil scan */
        .pc-res { margin-top: 16px; border-top: 1px solid var(--pc-line); padding-top: 16px; min-height: 96px; }
        .pc-res.flash { animation: pcFl .3s ease-out; }
        @keyframes pcFl { from { opacity: .3 } to { opacity: 1 } }
        .pc-idle { display: flex; align-items: center; gap: 12px; color: var(--pc-muted); height: 80px; }
        .pc-idle i.ic { width: 40px; height: 40px; border-radius: 50%; border: 1px dashed var(--pc-sec); color: var(--pc-sec); background: var(--pc-sec-lt); display: grid; place-items: center; font-style: normal; flex: none; }
        .pc-rh { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        .pc-badge { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; padding: 5px 12px; border-radius: 20px; color: #fff; }
        .pc-res.ok .pc-badge { background: var(--pc-ok); animation: pcPop .45s cubic-bezier(.2,1.7,.4,1); }
        .pc-res.warn .pc-badge { background: var(--pc-warn); }
        .pc-res.err .pc-badge { background: var(--pc-err); }
        .pc-res.pick .pc-badge { background: var(--pc-pri); }
        @keyframes pcPop { 0% { transform: scale(.4) } 100% { transform: none } }
        .pc-rh .bc { font-size: 20px; font-weight: 600; }
        .pc-rh .tm { margin-left: auto; color: var(--pc-faint); font-size: 12.5px; }
        .pc-msg { margin: 12px 0 0; padding: 10px 12px; border-radius: .4rem; line-height: 1.5; font-size: 13.5px; border-left: 3px solid; }
        .pc-res.warn .pc-msg { background: var(--pc-warn-lt); border-color: var(--pc-warn); }
        .pc-res.err .pc-msg { background: var(--pc-err-lt); border-color: var(--pc-err); }
        .pc-res.pick .pc-msg { background: var(--pc-pri-lt); border-color: var(--pc-pri); }
        .pc-facts { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); border: 1px solid var(--pc-line); border-radius: .4rem; margin-top: 14px; background: #fafbfc; }
        .pc-facts > div { padding: 10px 12px; min-width: 0; border-left: 1px solid var(--pc-line); }
        .pc-facts > div:first-child { border-left: 0; }
        .pc-facts small { display: block; font-size: 11px; color: var(--pc-muted); text-transform: uppercase; letter-spacing: .8px; margin-bottom: 3px; }
        .pc-facts b { display: block; font-size: 15px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .pc-facts .qty b { font-size: 18px; color: var(--pc-ok); }
        .pc-facts .sw { display: inline-block; width: 12px; height: 12px; border-radius: 50%; margin-right: 6px; vertical-align: -1px; border: 1px solid rgba(0,0,0,.15); }
        .pc-meta { margin-top: 10px; font-size: 12.5px; color: var(--pc-muted); line-height: 1.6; }
        .pc-meta b { color: var(--pc-ink2); font-weight: 600; }
        .pc-hist { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 10px; align-items: center; font-size: 12px; }
        .pc-hist span { border: 1px solid var(--pc-line); border-radius: 20px; padding: 3px 9px; color: var(--pc-ink2); }
        .pc-hist span.lead { border: 0; padding: 0; color: var(--pc-muted); }
        .pc-hist span.y { border-color: #b9e3c9; background: var(--pc-ok-lt); color: var(--pc-ok); }
        .pc-hist span.cur { outline: 2px solid var(--pc-ok); outline-offset: 1px; }
        .pc-knot { width: 30px; height: 30px; flex: none; }
        .pc-knot path { stroke-dasharray: 220; stroke-dashoffset: 220; animation: pcDraw .8s ease-out forwards; }
        @keyframes pcDraw { to { stroke-dashoffset: 0 } }
        .pc-shake { animation: pcShake .4s; }
        @keyframes pcShake { 20%, 60% { transform: translateX(-7px) } 40%, 80% { transform: translateX(7px) } }

        /* pilih kiriman */
        .pc-opts { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 8px; margin-top: 12px; }
        .pc-opt { text-align: left; border: 1px solid var(--pc-line); background: #fff; border-radius: .5rem; padding: 12px 14px; cursor: pointer; position: relative; animation: pcPop .35s cubic-bezier(.2,1.6,.4,1) both; }
        .pc-opt:hover { border-color: #9fb3cf; }
        .pc-opt.on { border-color: var(--pc-pri); box-shadow: 0 0 0 3px rgba(8,33,73,.15); background: #f7f9fc; }
        .pc-opt kbd { position: absolute; right: 10px; top: 10px; font-size: 11px; border: 1px solid var(--pc-line); background: var(--pc-soft); color: var(--pc-muted); padding: 0 6px; }
        .pc-opt b { display: block; font-weight: 600; font-size: 13px; }
        .pc-opt small { color: var(--pc-muted); font-size: 12px; }
        .pc-opt .q { font-size: 22px; font-weight: 700; margin-top: 4px; color: var(--pc-pri); }
        .pc-opt .q em { font-style: normal; font-size: 12.5px; font-weight: 500; color: var(--pc-muted); }

        /* ringkasan progres */
        .pc-sum .per { font-size: 15px; font-weight: 700; color: #fff; }
        .pc-sum .pc-card-h { display: block; padding: 12px 16px; }
        .pc-sum .pc-card-h .lbl { color: #9fd6d3; }
        .pc-ring { position: relative; width: 156px; height: 156px; margin: 18px auto 14px; }
        .pc-ring svg { transform: rotate(-90deg); }
        #pc-rp { transition: stroke-dashoffset .7s ease; }
        .pc-ring .c { position: absolute; inset: 0; display: grid; place-items: center; text-align: center; }
        .pc-ring .c b { font-size: 25px; font-weight: 700; display: block; line-height: 1; color: var(--pc-pri); letter-spacing: -1px; }
        .pc-ring .c span { font-size: 12px; color: var(--pc-muted); }
        .pc-ring.bump { animation: pcBump .5s; }
        @keyframes pcBump { 40% { transform: scale(1.06) rotate(5deg) } }
        .pc-kv { margin: 0 16px; }
        .pc-kr { display: grid; grid-template-columns: 10px minmax(0, 1fr) auto 12px; column-gap: 10px; row-gap: 1px; align-items: center; cursor: pointer; margin: 0 -8px; padding: 9px 8px; border-radius: .4rem; border-bottom: 1px dashed var(--pc-line); transition: background .15s; }
        .pc-kr:hover { background: var(--pc-soft); }
        .pc-kr i { grid-column: 1; grid-row: 1; width: 10px; height: 10px; border-radius: 50%; }
        .pc-kr span { grid-column: 2; grid-row: 1; white-space: nowrap; color: var(--pc-ink2); }
        .pc-kr small { grid-column: 2; grid-row: 2; font-size: 11.5px; color: var(--pc-muted); }
        .pc-kr b { grid-column: 3; grid-row: 1 / 3; font-size: 16px; }
        .pc-kr em { grid-column: 4; grid-row: 1 / 3; font-style: normal; color: var(--pc-faint); font-size: 15px; transition: transform .15s; }
        .pc-kr:hover em { color: var(--pc-ink); transform: translateX(2px); }
        .pc-kr[data-m=got] b { color: var(--pc-ok); }
        .pc-kr[data-m=wait] b { color: var(--pc-warn); }
        .pc-kr[data-m=all] b { color: var(--pc-pri); }
        .pc-carry { margin: 10px 16px 0; font-size: 12.5px; color: #8a5300; background: var(--pc-warn-lt); border: 1px solid #f5d29a; border-radius: .4rem; padding: 8px 10px; cursor: pointer; display: grid; grid-template-columns: 1fr auto; column-gap: 8px; align-items: center; }
        .pc-carry:hover { border-color: var(--pc-acc); }
        .pc-carry .q { grid-column: 1; font-size: 12px; color: var(--pc-warn); margin-top: 1px; }
        .pc-carry em { grid-row: 1 / 3; grid-column: 2; font-style: normal; font-weight: 600; }
        .pc-khint { font-size: 11.5px; text-align: center; margin: 8px 16px 0; padding: 5px 8px; background: var(--pc-sec-lt); color: var(--pc-sec); border-radius: .4rem; }
        .pc-old { margin: 12px 16px 0; padding-bottom: 16px; font-size: 12.5px; color: var(--pc-muted); line-height: 1.5; }
        .pc-old b { color: var(--pc-ink); }

        .pc-age { font-size: 12px; color: var(--pc-muted); }
        .pc-age.w { color: var(--pc-warn); }
        .pc-age.b { color: var(--pc-err); font-weight: 600; }

        /* dashboard sisa roll */
        .pc-dsh { padding: 16px 16px 6px; }
        .pc-kp { display: grid; grid-template-columns: 1.2fr 1fr 1fr; gap: 12px; }
        .pc-kpi { border-radius: .5rem; padding: 12px 14px; min-width: 0; position: relative; overflow: hidden; background: var(--pc-pri-lt); }
        .pc-kpi:after { content: ""; position: absolute; right: -18px; bottom: -18px; width: 70px; height: 70px; border-radius: 50%; background: rgba(255,255,255,.3); }
        .pc-kpi small { display: block; font-size: 11px; font-weight: 700; letter-spacing: .8px; text-transform: uppercase; color: var(--pc-pri); }
        .pc-kpi b { display: block; font-size: 26px; font-weight: 700; line-height: 1.15; margin-top: 4px; color: var(--pc-pri); }
        .pc-kpi b em { font-style: normal; font-size: 14px; font-weight: 500; opacity: .75; margin-left: 3px; }
        .pc-kpi .s2 { font-size: 12.5px; color: var(--pc-muted); margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .pc-kpi .qrow { display: flex; gap: 14px; flex-wrap: wrap; margin-top: 4px; }
        .pc-kpi .qrow b { font-size: 20px; margin: 0; }
        .pc-kpi.hot { background: linear-gradient(135deg, var(--pc-acc), var(--pc-warn)); color: #fff; cursor: pointer; transition: .15s; box-shadow: 0 6px 16px rgba(216,130,2,.25); }
        .pc-kpi.hot small, .pc-kpi.hot b, .pc-kpi.hot .s2 { color: #fff; }
        .pc-kpi.hot .s2 strong { background: rgba(255,255,255,.22); border-radius: 4px; padding: 0 5px; font-weight: 600; }
        .pc-kpi.hot .go { position: absolute; right: 10px; top: 10px; font-size: 11.5px; font-weight: 600; background: rgba(255,255,255,.22); border-radius: 20px; padding: 2px 8px; }
        .pc-kpi.hot:hover { transform: translateY(-1px); box-shadow: 0 10px 22px rgba(216,130,2,.35); }
        .pc-kpi.age-k { background: #fdeef0; }
        .pc-kpi.age-k small { color: var(--pc-err); }
        .pc-kpi.age-k b { color: var(--pc-ink); }
        .pc-kpi.age-k.late b { color: var(--pc-err); }
        .pc-aging { margin-top: 16px; }
        .pc-aging-h { display: flex; justify-content: space-between; font-size: 12px; color: var(--pc-muted); margin-bottom: 6px; }
        .pc-abar { display: flex; height: 12px; border-radius: 8px; overflow: hidden; background: var(--pc-soft); gap: 2px; }
        .pc-abar i { display: block; height: 100%; transition: flex-grow .6s; }
        .pc-aleg { display: flex; gap: 14px; flex-wrap: wrap; margin-top: 8px; font-size: 12px; color: var(--pc-ink2); }
        .pc-aleg span { display: flex; align-items: center; gap: 6px; }
        .pc-aleg i { width: 9px; height: 9px; border-radius: 50%; display: inline-block; }
        .pc-bars { margin-top: 16px; border-top: 1px solid var(--pc-line); }
        .pc-bars-h { font-size: 12px; color: var(--pc-muted); padding: 12px 0 4px; }
        .pc-br, .pc-bars-h2 { display: grid; grid-template-columns: 96px minmax(0, 1fr) 150px 150px 62px; gap: 12px; align-items: center; }
        .pc-bars-h2 { font-size: 11px; font-weight: 700; letter-spacing: .6px; text-transform: uppercase; color: var(--pc-muted); padding: 0 0 4px; }
        .pc-bars-h2 span { text-align: right; }
        .pc-br { padding: 7px 6px; margin: 0 -6px; border-radius: .4rem; cursor: pointer; transition: background .15s; }
        .pc-br:hover { background: var(--pc-soft); }
        .pc-br .dt2 { font-weight: 600; font-size: 13px; }
        .pc-br .tr { height: 10px; border-radius: 5px; background: #e9edf2; overflow: hidden; }
        .pc-br .tr i { display: block; height: 100%; border-radius: 5px; min-width: 6px; transition: width .6s; }
        .pc-br .q { font-size: 12.5px; color: var(--pc-ink2); text-align: right; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .pc-br .q b { color: var(--pc-ink); }
        .pc-br .ag { text-align: right; }
        .pc-br .k { font-size: 12.5px; color: var(--pc-muted); text-align: right; white-space: nowrap; }
        .pc-br .k b { color: var(--pc-ink2); }
        .pc-br .k .pp { display: inline-block; min-width: 58px; margin-left: 4px; font-size: 11.5px; font-weight: 700; color: var(--pc-pri); background: var(--pc-pri-lt); border-radius: 10px; padding: 1px 6px; text-align: center; }
        .pc-msep { font-size: 11px; font-weight: 700; letter-spacing: .8px; text-transform: uppercase; color: var(--pc-pri); padding: 10px 0 2px; }
        .pc-msep span { text-transform: none; letter-spacing: 0; font-size: 11px; color: var(--pc-warn); background: var(--pc-warn-lt); border: 1px solid #f5d29a; border-radius: 4px; padding: 0 6px; margin-left: 6px; }
        .pc-more { display: block; width: 100%; border: 0; border-radius: .4rem; margin-top: 6px; background: var(--pc-pri-lt); color: var(--pc-pri); font-weight: 600; padding: 9px; font-size: 12.5px; }
        .pc-more:hover { background: #dbe3ef; }
        .pc-dhint { font-size: 11.5px; color: var(--pc-muted); text-align: center; padding: 8px 0 12px; }
        .pc-empty { padding: 36px; text-align: center; color: var(--pc-muted); }

        /* rak hari ini */
        .pc-ak { padding: 14px 16px 8px; }
        .pc-rak { position: relative; background: var(--pc-soft); border-radius: .5rem; padding: 12px 12px 6px; border: 1px solid var(--pc-line); }
        .pc-rak-more { font-size: 11.5px; color: var(--pc-muted); text-align: center; margin: -4px 0 8px; }
        .pc-shelf { display: grid; grid-template-columns: repeat(8, 1fr); gap: 3px; padding: 0 3px 2px; position: relative; margin-bottom: 30px; align-items: end; }
        .pc-shelf:last-child { margin-bottom: 26px; }
        .pc-shelf-l { position: absolute; left: -4px; right: -4px; bottom: -25px; display: flex; justify-content: space-between; font-size: 10.5px; color: var(--pc-muted); white-space: nowrap; }
        .pc-shelf-l b { color: var(--pc-pri); font-weight: 700; letter-spacing: .3px; }
        .pc-shelf-l.e { opacity: .55; }
        .pc-slot:not(.e) { cursor: pointer; border-radius: 50%; transition: transform .15s; }
        .pc-slot:not(.e):hover { transform: translateY(-2px); }
        .pc-slot.on { outline: 2px solid var(--pc-acc); outline-offset: 1px; }
        .pc-pop { position: absolute; z-index: 5; width: 220px; background: #fff; border: 1px solid var(--pc-line); border-radius: .5rem; box-shadow: 0 8px 22px rgba(8,33,73,.18); padding: 10px 12px; font-size: 12.5px; }
        .pc-pop:before { content: ""; position: absolute; left: var(--ax, 50%); width: 10px; height: 10px; background: #fff; border: 1px solid var(--pc-line); transform: translateX(-50%) rotate(45deg); }
        .pc-pop.dn:before { top: -6px; border-right: 0; border-bottom: 0; }
        .pc-pop.up:before { bottom: -6px; border-left: 0; border-top: 0; }
        .pc-pop .h { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; }
        .pc-pop .h i { width: 14px; height: 14px; border-radius: 50%; flex: none; background: var(--c); box-shadow: 0 0 0 2px #fff, 0 0 0 3px rgba(0,0,0,.15); }
        .pc-pop .h b { font-size: 13.5px; }
        .pc-pop .x { margin-left: auto; border: 0; background: none; color: var(--pc-muted); font-size: 16px; line-height: 1; padding: 0 2px; cursor: pointer; }
        .pc-pop dl { display: grid; grid-template-columns: auto 1fr; gap: 3px 10px; margin: 0; }
        .pc-pop dt { color: var(--pc-muted); font-weight: 400; }
        .pc-pop dd { margin: 0; text-align: right; font-weight: 600; color: var(--pc-ink); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .pc-rt { margin-top: 4px; }
        .pc-rt-h { display: flex; justify-content: space-between; align-items: baseline; gap: 8px; }
        .pc-rt-h span { font-size: 11.5px; color: var(--pc-muted); }
        .pc-rt-h span b { color: var(--pc-pri); }
        .pc-rt-b { display: flex; align-items: flex-end; gap: 3px; height: 54px; padding-top: 14px; border-bottom: 1px solid var(--pc-line); }
        .pc-rt-c { flex: 1; height: 100%; display: flex; flex-direction: column; justify-content: flex-end; align-items: center; position: relative; }
        .pc-rt-c i { display: block; width: 100%; min-height: 2px; border-radius: 3px 3px 0 0; background: #c9d5e6; transition: height .4s; }
        .pc-rt-c.v i { background: var(--pc-sec); }
        .pc-rt-c.pk i { background: var(--pc-pri); }
        .pc-rt-c.now i { background: var(--pc-acc); }
        .pc-rt-c em { position: absolute; top: -14px; font-style: normal; font-size: 10px; font-weight: 700; color: var(--pc-pri); }
        .pc-rt-c.now em { color: var(--pc-acc); }
        .pc-rt-x { display: flex; gap: 3px; font-size: 10px; color: var(--pc-muted); margin-top: 3px; }
        .pc-rt-x span { flex: 1; text-align: center; }
        .pc-rt-x span.now { color: var(--pc-acc); font-weight: 700; }

        /* --- animasi: hover --- */
        .pc-slot svg { transition: transform .35s cubic-bezier(.3,1.5,.5,1); }
        .pc-slot:not(.e):hover svg { transform: rotate(-35deg) scale(1.08); }
        .pc-shelf-l b, .pc-shelf-l span { transition: color .2s; }
        .pc-shelf:hover .pc-shelf-l b { color: var(--pc-sec); }
        .pc-shelf:hover:after { background: linear-gradient(#2f8f8b, var(--pc-sec)); }
        .pc-rt-c em { opacity: 0; transform: translateY(3px); transition: opacity .15s, transform .15s; }
        .pc-rt-c.pk em, .pc-rt-c.now em, .pc-rt-c:hover em { opacity: 1; transform: none; }
        .pc-rt-c i { transition: height .4s, opacity .2s, filter .2s; }
        .pc-rt-b:hover .pc-rt-c i { opacity: .45; }
        .pc-rt-b .pc-rt-c:hover i { opacity: 1; filter: brightness(1.1); }
        .pc-fd { transition: transform .2s, background .2s; border-radius: .35rem; }
        .pc-fd:hover { transform: translateX(3px); background: var(--pc-soft); }
        .pc-fd i { transition: transform .3s; }
        .pc-fd:hover i { transform: scale(1.25); }

        /* --- animasi: saat scan --- */
        .pc-rt-c.hot i { transform-origin: bottom; animation: pcBarHot .8s cubic-bezier(.3,1.6,.5,1); }
        .pc-rt-c.hot em { opacity: 1; animation: pcPop .6s .1s backwards; }
        @keyframes pcBarHot { 0% { transform: scaleY(.6) } 60% { transform: scaleY(1.12); filter: brightness(1.25) } 100% { transform: none } }
        .pc-shelf.hot .pc-shelf-l b { animation: pcLblHot 1.4s; }
        @keyframes pcLblHot { 0%, 40% { color: var(--pc-acc) } 100% { color: var(--pc-pri) } }
        .pc-rak-f b.pop { display: inline-block; animation: pcPop .55s; }
        @keyframes pcPop { 0% { transform: scale(.6) } 55% { transform: scale(1.35) } 100% { transform: none } }
        .pc-pop { animation: pcPopIn .18s ease-out; }
        @keyframes pcPopIn { from { opacity: 0; transform: translateY(4px) } }

        /* --- animasi: pertama kali tampil --- */
        .pc-ak.intro .pc-slot:not(.e) svg { animation: pcRollIn .45s var(--d) backwards cubic-bezier(.2,1.4,.4,1); }
        @keyframes pcRollIn { from { opacity: 0; transform: translateX(-14px) rotate(-180deg) scale(.6) } }
        .pc-ak.intro .pc-rt-c i { transform-origin: bottom; animation: pcBarIn .5s var(--d) backwards ease-out; }
        @keyframes pcBarIn { from { transform: scaleY(0) } }

        @media (prefers-reduced-motion: reduce) {
            .pc-ak *, .pc-ak *:before, .pc-ak *:after { animation: none !important; transition: none !important; }
        }
        .pc-shelf:after { content: ""; position: absolute; left: -7px; right: -7px; bottom: -7px; height: 6px; border-radius: 2px; background: linear-gradient(#3b5379, var(--pc-pri)); box-shadow: 0 2px 3px rgba(0,0,0,.15); }
        .pc-slot { aspect-ratio: 1; display: grid; place-items: end center; position: relative; }
        .pc-slot.e:before { content: ""; width: 70%; height: 70%; border-radius: 50%; border: 1.5px dashed #c5ccd6; }
        .pc-slot.e.nx:before { border-color: var(--pc-sec); background: rgba(35,131,128,.12); animation: pcNx 1.6s ease-in-out infinite; }
        @keyframes pcNx { 50% { transform: scale(.82) } }
        .pc-slot svg { width: calc(100% * var(--s, 1)); height: calc(100% * var(--s, 1)); filter: drop-shadow(0 2px 1px rgba(0,0,0,.2)); }
        .pc-slot.hold svg { opacity: 0; }
        .pc-slot.land svg { animation: pcLand .5s cubic-bezier(.2,1.6,.4,1); }
        @keyframes pcLand { 0% { transform: translateY(-8px) scale(1.3) } 100% { transform: none } }
        .pc-rak-f { display: flex; justify-content: space-between; gap: 8px; font-size: 12.5px; color: var(--pc-muted); margin-top: 10px; }
        .pc-rak-f b { color: var(--pc-pri); }
        .pc-feed-h { font-size: 11px; font-weight: 700; letter-spacing: .8px; text-transform: uppercase; color: var(--pc-muted); margin: 14px 0 4px; }
        .pc-fd { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid var(--pc-line); }
        .pc-fd:last-child { border-bottom: 0; }
        .pc-fd i { width: 12px; height: 12px; border-radius: 50%; flex: none; background: var(--c, var(--pc-ok)); box-shadow: 0 0 0 2px #fff, 0 0 0 3px rgba(0,0,0,.15); }
        .pc-fd .m { min-width: 0; flex: 1; }
        .pc-fd .m b { display: block; font-size: 13px; font-weight: 600; }
        .pc-fd .m span { font-size: 11.5px; color: var(--pc-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block; }
        .pc-fd .t { font-size: 12px; color: var(--pc-muted); text-align: right; white-space: nowrap; }
        .pc-fd .t b { display: block; color: var(--pc-ink); }
        .pc-fd.nw { animation: pcFdn 1.8s; }
        @keyframes pcFdn { 0%, 30% { background: #dcf3e5 } 100% { background: transparent } }
        .pc-ak-empty { font-size: 12.5px; color: var(--pc-muted); padding: 6px 0 10px; }

        /* modal */
        #pcModal .modal-header .sub { font-size: 13px; color: #c9d4e6; margin-top: 2px; }
        #pcModal .modal-header .sub b { color: #fff; }
        #pcModal .modal-header .lbl { font-size: 11px; font-weight: 700; letter-spacing: 1.1px; text-transform: uppercase; color: #9fd6d3; }
        #pcModal .pc-mt { display: flex; gap: 10px; align-items: center; padding: 10px 16px; border-bottom: 1px solid var(--pc-line); flex-wrap: wrap; }
        #pcModal .pc-mt input { flex: 1; min-width: 200px; }
        #pcModal .pc-fchip { display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; background: var(--pc-pri-lt); border: 1px solid #c5d1e3; color: var(--pc-pri); border-radius: 20px; padding: 3px 6px 3px 10px; }
        #pcModal .pc-fchip button { border: 0; background: transparent; cursor: pointer; color: var(--pc-muted); font-size: 13px; padding: 0 4px; }
        #pcModal .pc-mbody { max-height: 62vh; overflow: auto; }
        #pcModal table { margin: 0; }
        #pcModal th { position: sticky; top: 0; z-index: 1; background: var(--pc-soft); color: var(--pc-pri); font-size: 11px !important; letter-spacing: .6px; text-transform: uppercase; white-space: nowrap; }
        #pcModal td { white-space: nowrap; vertical-align: middle; }
        #pcModal td .s { color: var(--pc-muted); font-size: 12px; }
        #pcModal td.n { color: var(--pc-faint); width: 36px; }
        #pcModal .stt { font-size: 12.5px; font-weight: 600; }
        #pcModal .stt.ok { color: var(--pc-ok); }
        #pcModal .stt.pd { color: var(--pc-warn); }
        #pcModal .pc-mf { font-size: 12.5px; color: var(--pc-muted); justify-content: space-between; }

        .pc-toast { position: fixed; left: 50%; bottom: 24px; transform: translate(-50%, 120px); opacity: 0; background: var(--sb-color, #082149); color: #fff; padding: 10px 18px; border-radius: 30px; font-weight: 500; transition: .2s; z-index: 2000; white-space: nowrap; box-shadow: 0 10px 24px rgba(8,33,73,.3); }
        .pc-toast.on { transform: translate(-50%, 0); opacity: 1; }
        .pc-flyer { position: fixed; z-index: 2001; width: 44px; height: 44px; margin: -22px 0 0 -22px; pointer-events: none; filter: drop-shadow(0 8px 6px rgba(0,0,0,.2)); }
        .pc-thr { position: fixed; z-index: 2002; pointer-events: none; border-radius: 2px; }
        .pc-skel { padding: 36px; text-align: center; color: var(--pc-muted); }

        @media (max-width: 1100px) { .pc-grid { grid-template-columns: 1fr; margin-bottom: 10px; } }
        @media (max-width: 640px) {
            .pc-facts { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .pc-facts > div:nth-child(3) { border-left: 0; }
            .pc-facts > div:nth-child(n+3) { border-top: 1px solid var(--pc-line); }
            .pc-kp { grid-template-columns: 1fr 1fr; }
            .pc-kp .pc-kpi.hot { grid-column: 1 / -1; }
            .pc-br { grid-template-columns: 70px minmax(0, 1fr) 54px; }
            .pc-br .q { grid-column: 1 / 3; grid-row: 2; text-align: left; }
            .pc-br .k { grid-column: 3; grid-row: 2; justify-self: end; }
            .pc-bars-h2, .pc-under .hint { display: none; }
            #pc-scan { font-size: 17px; }
        }
        @media (prefers-reduced-motion: reduce) { .pc-wrap *, .pc-flyer, .pc-thr { animation-duration: .01ms !important; transition-duration: .01ms !important; } }
    </style>
@endsection

@section('content')
    <div class="pc-wrap">
        <div class="pc-head">
            <div>
                <h5 class="fw-bold text-sb"><i class="fa fa-download fa-sm"></i> Penerimaan Fabric Cutting</h5>
                <p>Scan barcode kain dari gudang. Langsung tersimpan.</p>
            </div>
            <div class="pc-head-r">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="pc-fx"></button>
                <select class="form-select form-select-sm" id="pc-month" title="Bulan BPPB"></select>
                <a href="{{ route('create-old-penerimaan-cutting') }}" class="btn btn-outline-secondary btn-sm" title="Buka tampilan lama (scan → daftar → simpan)"><i class="fas fa-history"></i> Tampilan lama</a>
                <a href="{{ route('penerimaan-cutting') }}" class="btn btn-primary btn-sm"><i class="fas fa-reply"></i> Kembali</a>
            </div>
        </div>

        <div class="pc-grid">
            <div class="pc-col">
                <section class="pc-card">
                    <div class="pc-card-h"><span class="lbl"><i class="fa fa-barcode"></i> Scan Barcode Fabric</span></div>
                    <div class="pc-scan-b" id="pc-scan-card">
                        <div class="pc-inp-row">
                            <div class="pc-inp">
                                <input id="pc-scan" autocomplete="off" placeholder="Scan barcode di sini">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 7V5a2 2 0 0 1 2-2h2M17 3h2a2 2 0 0 1 2 2v2M21 17v2a2 2 0 0 1-2 2h-2M7 21H5a2 2 0 0 1-2-2v-2M7 8v8M10 8v8M13 8v8M17 8v8"/></svg>
                            </div>
                            <button type="button" class="btn btn-sb-secondary" id="pc-get">Get</button>
                            <button type="button" class="btn btn-outline-primary" id="pc-cam" title="Scan pakai kamera"><i class="fa fa-camera"></i></button>
                        </div>
                        <div class="pc-under">
                            <span class="pc-dot" id="pc-dot"></span><span id="pc-stat">Siap scan</span>
                            <span class="hint">Lebih dari satu kiriman? Pilih dengan <kbd>←</kbd><kbd>→</kbd> lalu <kbd>Enter</kbd>, atau tekan angkanya.</span>
                        </div>
                        <div id="pc-reader-wrap" class="d-none"><div id="pc-reader"></div></div>
                        <div class="pc-res" id="pc-res">
                            <div class="pc-idle"><i class="ic"><i class="fa fa-barcode"></i></i><div><b class="text-dark">Belum ada scan</b><br>Detail kain muncul di sini setelah barcode di-scan.</div></div>
                        </div>
                    </div>
                </section>

                <section class="pc-card" id="pc-sisa"></section>
            </div>

            <div class="pc-side">
                <aside class="pc-card pc-sum" id="pc-sum"></aside>
                <section class="pc-card" id="pc-aktv"></section>
            </div>
        </div>
    </div>

    <div class="modal fade" id="pcModal" tabindex="-1" aria-labelledby="pcModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light align-items-start">
                    <div>
                        <span class="lbl" id="pcModalLbl"></span>
                        <h5 class="modal-title fw-bold" id="pcModalTitle"></h5>
                        <div class="sub" id="pcModalSub"></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="pc-mt">
                    <input type="text" class="form-control form-control-sm" id="pcModalQ" placeholder="Cari barcode, BPPB, ID item, warna, lot…">
                    <span id="pcModalF"></span>
                </div>
                <div class="modal-body p-0 pc-mbody">
                    <table class="table table-sm table-hover">
                        <thead><tr id="pcModalH"></tr></thead>
                        <tbody id="pcModalTb"></tbody>
                    </table>
                </div>
                <div class="modal-footer pc-mf" id="pcModalFoot"></div>
            </div>
        </div>
    </div>

    <div class="pc-toast" id="pc-toast"></div>
@endsection

@section('custom-script')
    <script>
        (function () {
            const PC = {
                urlScan: '{{ route('scan-barcode-penerimaan-cutting') }}',
                urlStore: '{{ route('store-scan-penerimaan-cutting') }}',
                urlSummary: '{{ route('summary-penerimaan-cutting') }}',
                csrf: '{{ csrf_token() }}',
                today: '{{ date('Y-m-d') }}',
                minMonth: '2026-10',
            };

            /* ===== helper ===== */
            const $id = id => document.getElementById(id);
            const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
            const n2 = v => (+v || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 });
            const pctTxt = v => (+v || 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '%';
            const BLN = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            const BLNF = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            const fd = d => { if (!d) return '-'; const [y, m, x] = d.slice(0, 10).split('-'); return `${+x} ${BLN[+m]} ${y}`; };
            const fds = d => { if (!d) return '-'; const [, m, x] = d.slice(0, 10).split('-'); return `${+x} ${BLN[+m]}`; };
            const fmon = m => { const [y, x] = m.split('-'); return `${BLNF[+x]} ${y}`; };
            const u = r => { const k = String(r.unitK || '').toUpperCase(); return k === 'METER' ? 'm' : k === 'KGM' ? 'kg' : k.toLowerCase(); };
            const byU = a => { const o = {}; a.forEach(r => { const k = u(r); (o[k] ||= { q: 0, n: 0 }); o[k].q += (+r.qtyK || 0); o[k].n++; }); return o; };
            const UORD = ['m', 'kg'];
            const ukeys = o => Object.keys(o).sort((x, y) => (UORD.indexOf(x) + 9) % 9 - (UORD.indexOf(y) + 9) % 9);
            const qtyTxt = a => { const o = byU(a), k = ukeys(o); return k.length ? k.map(x => `${n2(o[x].q)} ${esc(x)}`).join(' · ') : '0 m'; };
            const jam = () => new Date().toTimeString().slice(0, 5);
            const ago = d => Math.max(0, Math.round((new Date(TODAY) - new Date(d.slice(0, 10))) / 864e5));
            const ageCls = a => a >= 30 ? 'b' : a >= 21 ? 'w' : '';
            const errMsg = x => (x && x.responseJSON && x.responseJSON.message) || (x && x.status ? `Error ${x.status}` : 'Koneksi bermasalah');

            /* ===== state ===== */
            let TODAY = PC.today, ROWS = [], TODAY_ROWS = [], CARRY_TOT = {}, carryFrom = '';
            let curMonth = TODAY.slice(0, 7), loaded = false, prevPct = 0, showAll = false;
            let pending = null, pendingList = null, selIdx = 0, busy = false, queue = [];
            let sumXhr = null;

            /* ===== bulan ===== */
            const months = [];
            { let [y, m] = TODAY.slice(0, 7).split('-').map(Number); for (let i = 0; i < 12; i++) { const ym = `${y}-${String(m).padStart(2, '0')}`; if (ym < PC.minMonth) break; months.push(ym); if (--m < 1) { m = 12; y--; } } }
            if (!months.length) months.push(TODAY.slice(0, 7));
            $id('pc-month').innerHTML = months.map(m => `<option value="${m}">BPPB ${fmon(m)}</option>`).join('');
            $id('pc-month').onchange = e => { curMonth = e.target.value; prevPct = 0; showAll = false; loadSummary(); focus(); };

            const inDate = r => r.tgl && r.tgl.slice(0, 7) === curMonth;
            const inPend = r => !r.rcv;
            const isCarry = r => r.tgl && r.tgl.slice(0, 7) < curMonth;

            function indexRows() {
                const by = {};
                ROWS.forEach(r => (by[r.bc] ||= []).push(r));
                Object.values(by).forEach(a => a.forEach((r, i) => { r.ke = i + 1; r.dari = a.length; }));
            }

            function loadSummary() {
                if (sumXhr) sumXhr.abort();
                loaded = false;
                renderLoading();
                sumXhr = $.ajax({ url: PC.urlSummary, data: { month: curMonth }, dataType: 'json' })
                    .done(res => {
                        ROWS = res.rows || [];
                        TODAY_ROWS = res.today_rows || [];
                        CARRY_TOT = res.carry_date_totals || {};
                        carryFrom = res.carry_from || '';
                        TODAY = res.today || TODAY;
                        indexRows();
                        loaded = true;
                        render();
                    })
                    .fail((x, s) => {
                        if (s === 'abort') return;
                        const fail = `<div class="pc-skel text-danger">Gagal memuat ringkasan (${esc(errMsg(x))}).<br><button type="button" class="btn btn-sm btn-outline-danger mt-2 pc-retry">Coba lagi</button></div>`;
                        $id('pc-sum').innerHTML = fail; $id('pc-sisa').innerHTML = fail; $id('pc-aktv').innerHTML = '';
                    });
            }
            document.addEventListener('click', e => { if (e.target.closest('.pc-retry')) loadSummary(); });

            function renderLoading() {
                const sk = `<div class="pc-skel"><div class="spinner-border spinner-border-sm text-sb"></div> Memuat…</div>`;
                $id('pc-sum').innerHTML = `<div class="pc-card-h"><span class="lbl">Progres penerimaan</span><div class="per">BPPB ${fmon(curMonth)}</div></div>` + sk;
                $id('pc-sisa').innerHTML = `<div class="pc-card-h"><span class="lbl">Sisa roll belum diterima</span></div>` + sk;
                if (!$id('pc-aktv').innerHTML) $id('pc-aktv').innerHTML = `<div class="pc-card-h"><span class="lbl">PENERIMAAN HARI INI</span></div>` + sk;
            }

            /* ===== ringkasan (ring) ===== */
            const R = 66, C = 2 * Math.PI * R;
            function renderSum() {
                const all = ROWS.filter(inDate), got = all.filter(r => r.rcv), wait = all.filter(r => !r.rcv);
                // dibulatkan ke bawah & maks 99,99% selama masih ada pending, supaya tidak tampil 100% padahal belum selesai
                const pct = !all.length ? 0 : wait.length ? Math.min(99.99, Math.floor(got.length / all.length * 10000) / 100) : 100;
                const pend = ROWS.filter(inPend), carry = pend.filter(isCarry);
                const oldest = [...pend].sort((a, b) => a.tgl.localeCompare(b.tgl))[0];
                $id('pc-sum').innerHTML = `
                    <div class="pc-card-h"><span class="lbl">Progres penerimaan</span><div class="per">BPPB ${fmon(curMonth)}</div></div>
                    <div class="pc-ring">
                        <svg width="156" height="156" viewBox="0 0 156 156">
                            <circle cx="78" cy="78" r="${R}" fill="none" stroke="#e9edf2" stroke-width="15"/>
                            <circle id="pc-rp" cx="78" cy="78" r="${R}" fill="none" stroke="${pct === 100 ? '#2e8a57' : '#238380'}" stroke-width="15" stroke-linecap="round"
                                stroke-dasharray="${C}" stroke-dashoffset="${C * (1 - prevPct / 100)}"/>
                            <circle cx="78" cy="78" r="52" fill="none" stroke="#c5ccd6" stroke-width="1.2" stroke-dasharray="3 4"/>
                        </svg>
                        <div class="c"><div><b>${pctTxt(pct)}</b><span>${got.length} / ${all.length} roll</span></div></div>
                    </div>
                    <div class="pc-kv">
                        <div class="pc-kr" data-m="got" title="Lihat detail roll diterima"><i style="background:var(--pc-ok)"></i><span>Diterima</span><b>${got.length}</b><small>${qtyTxt(got)}</small><em>›</em></div>
                        <div class="pc-kr" data-m="wait" title="Lihat detail roll pending"><i style="background:var(--pc-acc)"></i><span>Pending</span><b>${wait.length}</b><small>${qtyTxt(wait)}</small><em>›</em></div>
                        <div class="pc-kr" data-m="all" title="Lihat detail semua roll keluar"><i style="background:var(--pc-pri)"></i><span>Total keluar</span><b>${all.length}</b><small>${qtyTxt(all)}</small><em>›</em></div>
                    </div>
                    ${carry.length ? `<div class="pc-carry" id="pc-carry" title="Lihat roll pending dari bulan sebelumnya"><span class="t">＋ <b>${carry.length} roll</b> pending bulan sebelumnya</span><span class="q">${qtyTxt(carry)} · sejak ${fmon(carryFrom || curMonth)}</span><em>›</em></div>` : ''}
                    <div class="pc-khint mb-3"><i class="fa fa-hand-pointer"></i> Klik baris di atas untuk lihat detail</div>
                    ${oldest ? `<div class="pc-old d-none">Paling lama pending:<br><b>${fds(oldest.tgl)}</b> · <span class="pc-age ${ageCls(ago(oldest.tgl))}">${ago(oldest.tgl)} hari</span> · <span class="mono">${esc(oldest.bc)}</span></div>`
                             : `<div class="pc-old d-none"><b>Semua sudah diterima ✓</b></div>`}`;
                requestAnimationFrame(() => requestAnimationFrame(() => { const rp = $id('pc-rp'); if (rp) rp.style.strokeDashoffset = C * (1 - pct / 100); }));
                prevPct = pct;
            }
            $id('pc-sum').addEventListener('click', e => {
                if (e.target.closest('#pc-carry')) { openModal(null, 'wait', 'carry'); return; }
                const k = e.target.closest('.pc-kr');
                if (k) openModal(null, k.dataset.m, k.dataset.m === 'wait' ? 'month' : null);
            });

            /* ===== sisa roll: dashboard ===== */
            const AGE = [['< 7 hari', a => a < 7, '#2c9efc'], ['7–20 hari', a => a >= 7 && a < 21, '#ffc107'], ['21–29 hari', a => a >= 21 && a < 30, '#ff971f'], ['≥ 30 hari', a => a >= 30, '#fa4456']];
            const ageCol = a => AGE.find(x => x[1](a))[2];
            const totalOn = tgl => tgl.slice(0, 7) < curMonth ? (+CARRY_TOT[tgl] || ROWS.filter(r => r.tgl === tgl).length) : ROWS.filter(r => r.tgl === tgl).length;
            function renderSisa() {
                const w = ROWS.filter(inPend), wc = w.filter(isCarry);
                const head = `<div class="pc-card-h"><span class="lbl"><i class="fa fa-hourglass-half"></i> Sisa roll belum diterima</span><span class="r">s/d ${fmon(curMonth)} · termasuk ${carryFrom ? 'sejak ' + fmon(carryFrom) : 'bulan sebelumnya'}</span></div>`;
                if (!w.length) { $id('pc-sisa').innerHTML = head + `<div class="pc-empty">Semua roll sudah diterima ✓</div>`; return; }
                const o = byU(w), uk = ukeys(o);
                const g = {}; w.forEach(r => (g[r.tgl] ||= { tgl: r.tgl, rows: [] }).rows.push(r));
                const groups = Object.values(g).sort((a, b) => a.tgl.localeCompare(b.tgl));
                const oa = ago(groups[0].tgl);
                const buck = AGE.map(([l, f, c]) => [l, c, w.filter(r => f(ago(r.tgl))).length]);
                const LIM = 8, shown = showAll ? groups : groups.slice(0, LIM);
                $id('pc-sisa').innerHTML = head + `
                    <div class="pc-dsh">
                        <div class="pc-kp">
                            <div class="pc-kpi hot" id="pc-pend" title="Lihat detail barcode pending"><span class="go">Detail ›</span><small>Roll pending</small><b>${w.length.toLocaleString('id-ID')}<em>roll</em></b><div class="s2">${w.length - wc.length} bulan ini${wc.length ? ` · <strong>${wc.length} bulan sebelumnya</strong>` : ''}</div></div>
                            <div class="pc-kpi"><small>Qty pending</small><div class="qrow">${uk.map(k => `<b>${n2(o[k].q)}<em>${esc(k)}</em></b>`).join('')}</div><div class="s2">${uk.map(k => `${o[k].n} roll ${esc(k)}`).join(' · ')}</div></div>
                            <div class="pc-kpi age-k ${oa >= 30 ? 'late' : ''}"><small>Paling lama</small><b>${oa}<em>hari</em></b><div class="s2">sejak ${fds(groups[0].tgl)}</div></div>
                        </div>
                        <div class="pc-aging"><div class="pc-aging-h"><span>Durasi pending</span><span>${w.length} roll</span></div>
                            <div class="pc-abar">${buck.filter(b => b[2]).map(([l, c, n]) => `<i style="flex-grow:${n};background:${c}" title="${l}: ${n} roll"></i>`).join('')}</div>
                            <div class="pc-aleg">${buck.map(([l, c, n]) => `<span style="${n ? '' : 'opacity:.45'}"><i style="background:${c}"></i>${l} <b>${n}</b></span>`).join('')}</div>
                        </div>
                        <div class="pc-bars">
                            <div class="pc-bars-h">Per tanggal BPPB · panjang bar = % pending dari total keluar</div>
                            <div class="pc-bars-h2"><i></i><i></i><span>Pending</span><span>Total keluar</span><span>Durasi</span></div>
                            ${shown.map((x, i) => {
                                const a = ago(x.tgl), c = ageCol(a), tk = Math.max(totalOn(x.tgl), x.rows.length), pp = x.rows.length / tk * 100, mo = x.tgl.slice(0, 7);
                                const sep = (i === 0 || shown[i - 1].tgl.slice(0, 7) !== mo) ? `<div class="pc-msep">${fmon(mo)}${mo < curMonth ? ' <span>bulan sebelumnya</span>' : ''}</div>` : '';
                                return sep + `<div class="pc-br" data-d="${x.tgl}" title="Klik untuk lihat barcode ${fd(x.tgl)}">
                                    <span class="dt2">${fd(x.tgl)}</span>
                                    <div class="tr" title="${x.rows.length} dari ${tk} roll masih pending (${pctTxt(pp)})"><i style="width:${pp}%;background:${c}"></i></div>
                                    <span class="q"><b>${x.rows.length}</b> roll · ${qtyTxt(x.rows)}</span>
                                    <span class="k">dari <b>${tk}</b> roll <span class="pp">${pctTxt(pp)}</span></span>
                                    <span class="ag pc-age ${ageCls(a)}">${a} hari</span></div>`;
                            }).join('')}
                            ${groups.length > LIM ? `<button type="button" class="pc-more" id="pc-moreBtn">${showAll ? 'Tampilkan lebih sedikit' : `Tampilkan semua (${groups.length} tanggal)`}</button>` : ''}
                        </div>
                        <div class="pc-dhint"><i class="fa fa-hand-pointer"></i> Klik kotak oranye atau baris tanggal untuk lihat daftar barcode</div>
                    </div>`;
            }
            $id('pc-sisa').addEventListener('click', e => {
                if (e.target.closest('#pc-moreBtn')) { showAll = !showAll; renderSisa(); return; }
                if (e.target.closest('#pc-pend')) { openModal(null, 'wait'); return; }
                const t = e.target.closest('.pc-br');
                if (t) openModal(t.dataset.d, 'wait');
            });

            /* ===== modal detail ===== */
            const MODE = {
                wait: { lbl: 'Pending · belum diterima cutting', t: 'Detail roll pending', urut: 'urut dari yang paling lama', col: 'Durasi pending', empty: 'Semua roll sudah diterima ✓' },
                got: { lbl: 'Sudah diterima cutting', t: 'Detail roll diterima', urut: 'urut dari yang terakhir diterima', col: 'Diterima', empty: 'Belum ada roll yang diterima' },
                all: { lbl: 'Total keluar dari gudang', t: 'Detail semua roll keluar', urut: 'urut dari tanggal BPPB paling lama', col: 'Status', empty: 'Tidak ada data' }
            };
            let moDate = null, moQ = '', moMode = 'wait', moScope = null;
            const moEl = $id('pcModal');
            const moBs = bootstrap.Modal.getOrCreateInstance(moEl);
            moEl.addEventListener('shown.bs.modal', () => $id('pcModalQ').focus());
            moEl.addEventListener('hidden.bs.modal', () => focus());

            function openModal(d, m, sc) {
                if (!loaded) return;
                moDate = d; moMode = m || 'wait'; moScope = sc || null; moQ = ''; $id('pcModalQ').value = '';
                renderModal(); moBs.show();
            }
            function renderModal() {
                const M = MODE[moMode];
                let w;
                if (moMode === 'wait') { w = ROWS.filter(inPend); if (moScope === 'carry') w = w.filter(isCarry); if (moScope === 'month') w = w.filter(inDate); }
                else { w = ROWS.filter(inDate); if (moMode === 'got') w = w.filter(r => r.rcv); }
                if (moDate) w = w.filter(r => r.tgl === moDate);
                if (moMode === 'got') w.sort((a, b) => (b.rcv.tgl + (b.rcv.jam || '')).localeCompare(a.rcv.tgl + (a.rcv.jam || '')) || b.tgl.localeCompare(a.tgl) || String(a.bc).localeCompare(String(b.bc)));
                else w.sort((a, b) => a.tgl.localeCompare(b.tgl) || String(a.bppb).localeCompare(String(b.bppb)) || String(a.bc).localeCompare(String(b.bc)));
                const tot = w.length, oo = byU(w), totQ = ukeys(oo).map(k => `${n2(oo[k].q)} ${esc(k)} (${oo[k].n} roll)`).join(' + ') || '0 m';
                if (moQ) w = w.filter(r => [r.bc, r.bppb, r.ws, r.item, r.warna, r.lot, r.req].join(' ').toLowerCase().includes(moQ));
                const CAP = 300, list = w.slice(0, CAP);
                $id('pcModalLbl').textContent = M.lbl; $id('pcModalTitle').textContent = M.t;
                const scopeTxt = moDate ? '· BPPB ' + fd(moDate)
                    : moMode === 'wait' ? (moScope === 'carry' ? '· dari bulan sebelum ' + fmon(curMonth) : moScope === 'month' ? '· keluar ' + fmon(curMonth) : '· s/d ' + fmon(curMonth) + ' (termasuk bulan sebelumnya)')
                    : '· keluar ' + fmon(curMonth);
                $id('pcModalSub').innerHTML = `<b>${tot.toLocaleString('id-ID')} roll</b> · <b>${totQ}</b> ${scopeTxt} · ${M.urut}`;
                $id('pcModalF').innerHTML = (moDate ? `<span class="pc-fchip">Tanggal: <b>${fd(moDate)}</b><button type="button" id="pcMoAll" title="Tampilkan semua tanggal">✕</button></span>` : '')
                    + (moScope && moMode === 'wait' ? ` <span class="pc-fchip">${moScope === 'carry' ? 'Hanya bulan sebelumnya' : 'Hanya ' + fmon(curMonth)}<button type="button" id="pcMoSc" title="Tampilkan semua pending">✕</button></span>` : '');
                $id('pcModalH').innerHTML = `<th>#</th><th>Barcode</th><th>Tgl BPPB</th><th>${M.col}</th><th>No. BPPB · WS</th><th>ID Item · Warna</th><th>Lot · Roll</th><th class="text-end">Qty</th>`;
                const third = r => {
                    const a = ago(r.tgl);
                    if (moMode === 'got') return `${fds(r.rcv.tgl)}${r.rcv.jam ? ' · ' + esc(r.rcv.jam) : ''}<div class="s">${esc(r.rcv.by)}</div>`;
                    if (moMode === 'all') return r.rcv ? `<span class="stt ok">● Diterima</span>` : `<span class="stt pd">● Pending</span><div class="s"><span class="pc-age ${ageCls(a)}">${a} hari</span></div>`;
                    return `<span class="pc-age ${ageCls(a)}">${a} hari</span>`;
                };
                $id('pcModalTb').innerHTML = list.length ? list.map((r, i) => `<tr>
                        <td class="n">${i + 1}</td>
                        <td><b class="mono">${esc(r.bc)}</b>${r.dari > 1 ? `<div class="s">keluar ke-${r.ke} dari ${r.dari}</div>` : ''}</td>
                        <td>${fd(r.tgl)}</td>
                        <td>${third(r)}</td>
                        <td>${esc(r.bppb)}<div class="s">WS ${esc(r.ws)}</div></td>
                        <td><b class="mono">${esc(r.item)}</b><div class="s">${esc(r.warna)}</div></td>
                        <td>${esc(r.lot)}<div class="s">Roll ${esc(r.roll)}</div></td>
                        <td class="text-end"><b>${n2(r.qtyK)}</b> ${esc(u(r))}</td></tr>`).join('')
                    : `<tr><td colspan="8" class="pc-empty">${moQ ? 'Tidak ada yang cocok dengan pencarian' : M.empty}</td></tr>`;
                $id('pcModalFoot').innerHTML = `<span>${w.length > CAP ? `Menampilkan ${CAP} dari ${w.length.toLocaleString('id-ID')} · persempit dengan pencarian` : `${w.length.toLocaleString('id-ID')} barcode`}</span><span>Esc untuk menutup</span>`;
            }
            moEl.addEventListener('click', e => {
                if (e.target.closest('#pcMoAll')) { moDate = null; renderModal(); }
                if (e.target.closest('#pcMoSc')) { moScope = null; renderModal(); }
            });
            $id('pcModalQ').oninput = e => { moQ = e.target.value.toLowerCase(); renderModal(); };

            /* ===== rak hari ini ===== */
            const PER = 8, MIN_SHELF = 3, MAX_SHELF = 5;
            let AK_START = 0;
            // ukuran roll: relatif ke qty terbesar hari ini dengan satuan yang sama (55%–100%)
            function rollScale(r, mx) {
                const m = mx[u(r)] || 0, q = +r.qtyK || 0;
                return m > 0 && q > 0 ? (.55 + .45 * Math.sqrt(Math.min(1, q / m))).toFixed(3) : '.55';
            }
            function hourOf(r) { const h = parseInt(String(r.jam || '').slice(0, 2), 10); return isNaN(h) ? null : h; }
            function ritmeHtml(all, flash) {
                const cnt = {}, qty = {};
                all.forEach(r => { const h = hourOf(r); if (h === null) return; cnt[h] = (cnt[h] || 0) + 1; (qty[h] = qty[h] || []).push(r); });
                const hs = Object.keys(cnt).map(Number), nowH = new Date().getHours(), isToday = TODAY === PC.today;
                const lo = Math.min(7, ...hs), hi = Math.max(17, ...hs);
                const mx = Math.max(1, ...hs.map(h => cnt[h]));
                const pk = hs.length ? hs.reduce((a, b) => cnt[b] > cnt[a] ? b : a) : null;
                const hot = flash && FX && all.length ? hourOf(all[all.length - 1]) : null;
                let bars = '', xs = '', bi = 0;
                for (let h = lo; h <= hi; h++) {
                    const c = cnt[h] || 0, now = isToday && h === nowH;
                    const cls = [c ? 'v' : '', h === pk ? 'pk' : '', now ? 'now' : '', h === hot ? 'hot' : ''].join(' ');
                    const tip = `${String(h).padStart(2, '0')}:00–${String(h).padStart(2, '0')}:59 · ${c} roll${c ? ' · ' + qtyTxt(qty[h]) : ''}`;
                    bars += `<div class="pc-rt-c ${cls}" title="${tip}" style="--d:${(bi++) * 35}ms">${c ? `<em>${c}</em>` : ''}<i style="height:${c ? Math.max(6, c / mx * 100) : 0}%"></i></div>`;
                    xs += `<span class="${now ? 'now' : ''}">${(h - lo) % 2 === 0 || now ? String(h).padStart(2, '0') : ''}</span>`;
                }
                return `<div class="pc-rt">
                    <div class="pc-rt-h"><div class="pc-feed-h" style="margin:14px 0 0">Ritme per jam</div>
                        <span>${pk !== null ? `puncak <b>${String(pk).padStart(2, '0')}:00</b> · ${cnt[pk]} roll` : 'belum ada scan'}</span></div>
                    <div class="pc-rt-b">${bars}</div><div class="pc-rt-x">${xs}</div></div>`;
            }
            let akIntro = true;
            function renderAktv(flash) {
                const all = TODAY_ROWS, n = all.length;
                const need = Math.ceil((n + 1) / PER), nShelf = Math.min(MAX_SHELF, Math.max(MIN_SHELF, need));
                const start = need > MAX_SHELF ? (need - MAX_SHELF) * PER : 0;
                const shown = all.slice(start);
                AK_START = start;
                const mx = {}; all.forEach(r => { const k = u(r); mx[k] = Math.max(mx[k] || 0, +r.qtyK || 0); });
                let sh = '';
                for (let s = 0; s < nShelf; s++) {
                    const rs = shown.slice(s * PER, s * PER + PER), no = start / PER + s + 1;
                    const hotS = flash && FX && shown.length && s === Math.floor((shown.length - 1) / PER);
                    sh += `<div class="pc-shelf ${hotS ? 'hot' : ''}">`;
                    for (let i = 0; i < PER; i++) {
                        const k = s * PER + i, r = shown[k];
                        sh += r ? `<div class="pc-slot ${flash && FX && k === shown.length - 1 ? 'hold' : ''}" data-k="${k}" style="--s:${rollScale(r, mx)};--d:${k * 25}ms">${endSvg(fcol(r.warna))}</div>`
                                : `<div class="pc-slot e ${k === shown.length ? 'nx' : ''}"></div>`;
                    }
                    const a = rs.length ? rs[0].jam || '-' : '', b = rs.length ? rs[rs.length - 1].jam || '-' : '';
                    sh += `<div class="pc-shelf-l ${rs.length ? '' : 'e'}"><b>Baris ${no}</b><span>${rs.length ? (a === b ? a : `${a}–${b}`) + ` · ${rs.length}/${PER}` : 'kosong'}</span></div></div>`;
                }
                const hidden = start;
                const recent = all.slice(-3).reverse();
                $id('pc-aktv').innerHTML = `
                    <div class="pc-card-h"><span class="lbl"><i class="fa fa-layer-group"></i> PENERIMAAN HARI INI</span><span class="r">${fd(TODAY)}</span></div>
                    <div class="pc-ak ${akIntro && FX ? 'intro' : ''}">
                        <div class="pc-rak">${hidden ? `<div class="pc-rak-more">+${hidden} roll di baris sebelumnya</div>` : ''}${sh}</div>
                        <div class="pc-rak-f"><span><b class="${flash && FX ? 'pop' : ''}">${n}</b> roll diterima</span><span>${n ? qtyTxt(all) : '0 m'}</span></div>
                        ${ritmeHtml(all, flash)}
                        <div class="pc-feed-h">Scan terakhir</div>
                        ${recent.length ? recent.map((r, i) => `<div class="pc-fd ${flash && i === 0 ? 'nw' : ''}"><i style="--c:${fcol(r.warna)}"></i>
                            <div class="m"><b class="mono">${esc(r.bc)}</b><span>${esc(r.warna || '-')} · ${n2(r.qtyK)} ${esc(u(r))}</span></div>
                            <div class="t"><b>${esc(r.jam || '-')}</b>${r.by && r.by !== '-' ? esc(r.by) : ''}</div></div>`).join('')
                            : `<div class="pc-ak-empty">Belum ada scan hari ini.</div>`}
                    </div>`;
                akIntro = false;
            }

            function closePop() {
                document.querySelectorAll('#pc-aktv .pc-pop').forEach(p => p.remove());
                document.querySelectorAll('#pc-aktv .pc-slot.on').forEach(s => s.classList.remove('on'));
            }
            function openPop(slot) {
                const k = +slot.dataset.k, r = TODAY_ROWS[AK_START + k], rak = slot.closest('.pc-rak');
                if (!r || !rak) return;
                const was = slot.classList.contains('on');
                closePop();
                if (was) return;
                slot.classList.add('on');
                const p = document.createElement('div');
                p.innerHTML = `<div class="h"><i style="--c:${fcol(r.warna)}"></i><b class="mono">${esc(r.bc)}</b><button type="button" class="x" aria-label="Tutup">×</button></div>
                    <dl><dt>Warna</dt><dd title="${esc(r.warna || '-')}">${esc(r.warna || '-')}</dd>
                        <dt>Qty</dt><dd>${n2(r.qtyK)} ${esc(u(r))}</dd>
                        <dt>Jam terima</dt><dd>${esc(r.jam || '-')}</dd>
                        <dt>Penerima</dt><dd>${r.by && r.by !== '-' ? esc(r.by) : '-'}</dd>
                        <dt>Urutan</dt><dd>roll ke-${AK_START + k + 1} hari ini</dd></dl>`;
                rak.appendChild(p);
                const R = rak.getBoundingClientRect(), S = slot.getBoundingClientRect();
                const cx = S.left + S.width / 2 - R.left, W = p.offsetWidth || 220;
                const left = Math.max(4, Math.min(R.width - W - 4, cx - W / 2));
                const down = S.top - R.top < R.height / 2;
                p.className = 'pc-pop ' + (down ? 'dn' : 'up');
                p.style.left = left + 'px';
                p.style.setProperty('--ax', (cx - left) + 'px');
                if (down) p.style.top = (S.bottom - R.top + 8) + 'px'; else p.style.bottom = (R.bottom - S.top + 8) + 'px';
            }
            $id('pc-aktv').addEventListener('click', e => {
                const slot = e.target.closest('.pc-slot:not(.e)');
                if (slot) { e.stopPropagation(); openPop(slot); return; }
                if (e.target.closest('.pc-pop .x') || !e.target.closest('.pc-pop')) closePop();
            });
            document.addEventListener('click', e => { if (!e.target.closest('#pc-aktv')) closePop(); });
            document.addEventListener('keydown', e => { if (e.key === 'Escape') closePop(); });

            function render(flash) { if (!loaded) return; renderSum(); renderSisa(); renderAktv(flash); }

            /* ===== scan ===== */
            const inp = $id('pc-scan');
            function focus() { setTimeout(() => { if (!moEl.classList.contains('show')) inp.focus({ preventScroll: true }); }, 20); }
            document.querySelector('.pc-wrap').addEventListener('click', e => {
                if (!e.target.closest('input,select,button,a,.pc-opt,.pc-br,.pc-kpi,.pc-kr,.pc-carry,#pc-reader-wrap,.pc-pop')) focus();
            });
            function setStatus(b) { $id('pc-dot').classList.toggle('busy', b); $id('pc-stat').textContent = b ? 'Memproses…' : 'Siap scan'; }

            inp.addEventListener('keydown', e => {
                if (pending && !busy) {
                    if (e.key === 'ArrowRight' || e.key === 'ArrowDown') { selIdx = (selIdx + 1) % pending.length; drawPick(); e.preventDefault(); return; }
                    if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') { selIdx = (selIdx - 1 + pending.length) % pending.length; drawPick(); e.preventDefault(); return; }
                    if (/^[1-9]$/.test(e.key) && +e.key <= pending.length && !inp.value) { save(pending[+e.key - 1], pendingList); e.preventDefault(); return; }
                    if (e.key === 'Enter' && !inp.value.trim()) { save(pending[selIdx], pendingList); e.preventDefault(); return; }
                }
                if (e.key === 'Enter') { e.preventDefault(); scan(inp.value); }
            });
            $id('pc-get').onclick = () => scan(inp.value);

            function done() {
                busy = false; setStatus(false);
                if (queue.length) scan(queue.shift());
            }

            function scan(raw) {
                const bc = String(raw || '').trim().toUpperCase();
                inp.value = '';
                if (!bc) return;
                if (busy) { queue.push(bc); return; }
                busy = true; pending = null; pendingList = null; setStatus(true); sew();
                $.ajax({ url: PC.urlScan + '/' + encodeURIComponent(bc), dataType: 'json' })
                    .done(res => handleScan(bc, res))
                    .fail(x => { show('err', '✕ Gagal', bc, 'Gagal mengambil data barcode: ' + esc(errMsg(x))); beep('err'); fxErr(); done(); });
            }

            function handleScan(bc, res) {
                const list = (res && res.rows) || [];
                if (!list.length) {
                    if (res && res.legacy_received) {
                        show('warn', '! Sudah diterima', bc, 'Barcode ini sudah pernah diterima sebelumnya, jadi <b>tidak disimpan dua kali</b>.');
                        beep('warn'); fxWarn();
                    } else {
                        show('err', '✕ Tidak dikenal', bc, 'Barcode ini tidak ada di data pengeluaran gudang ke Cutting, jadi <b>tidak disimpan</b>. Cek label atau tanyakan ke gudang.');
                        beep('err'); fxErr();
                    }
                    return done();
                }
                list.forEach((r, i) => { r.ke = i + 1; r.dari = list.length; });
                const wait = list.filter(r => !r.rcv), ready = wait.filter(r => !r.future);
                if (!wait.length) {
                    show('warn', '! Sudah diterima', bc, `Semua pengeluaran barcode ini (${list.length}x) sudah diterima sebelumnya, jadi <b>tidak disimpan dua kali</b>.`, list[list.length - 1], list);
                    beep('warn'); fxWarn(); return done();
                }
                if (!ready.length) {
                    show('err', '✕ Belum bisa diterima', bc, `Tanggal BPPB (${fd(wait[0].tgl)}) melebihi tanggal hari ini, jadi <b>tidak disimpan</b>.`, wait[0], list);
                    beep('err'); fxErr(); return done();
                }
                if (ready.length === 1) { busy = false; save(ready[0], list); return; }
                pending = ready; pendingList = list; selIdx = 0; drawPick(); beep('pick'); done();
            }

            function save(r, list) {
                if (busy) return;
                busy = true; pending = null; setStatus(true);
                $.ajax({ url: PC.urlStore, type: 'post', dataType: 'json', data: { _token: PC.csrf, whs_bppb_det_id: r.id } })
                    .done(res => {
                        const d = Object.assign({}, r, res.data, { style: r.style, nama: r.nama });
                        if (list) { const i = list.findIndex(x => x.id === d.id); if (i >= 0) list[i] = d; }
                        applySaved(d);
                        show('ok', '✓ Tersimpan', d.bc, null, d, list);
                        toast(`Tersimpan · ${n2(d.qtyK)} ${u(d)}`); beep('ok');
                        const q = document.querySelector('#pc-res .pc-facts .qty'), from = q && q.getBoundingClientRect();
                        render(true); focus();
                        if (FX && from) flyRoll(d, from); else landRoll();
                    })
                    .fail(x => {
                        if (x.status === 409) { show('warn', '! Sudah diterima', r.bc, esc(errMsg(x)), r, list); beep('warn'); fxWarn(); }
                        else { show('err', '✕ Gagal simpan', r.bc, esc(errMsg(x)), r, list); beep('err'); fxErr(); }
                    })
                    .always(done);
            }

            function applySaved(d) {
                const ex = ROWS.find(x => x.id === d.id);
                if (ex) { ex.rcv = d.rcv; ex.qtyK = d.qtyK; ex.unitK = d.unitK; }
                else if (inDate(d)) { ROWS.push(d); ROWS.sort((a, b) => a.tgl.localeCompare(b.tgl) || a.id - b.id); indexRows(); }
                if (d.rcv && d.rcv.tgl === TODAY) TODAY_ROWS.push({ id: d.id, bc: d.bc, warna: d.warna, qtyK: d.qtyK, unitK: d.unitK, jam: d.rcv.jam, by: d.rcv.by });
            }

            function show(k, label, bc, msg, r, list) {
                const el = $id('pc-res'); el.className = 'pc-res ' + k; void el.offsetWidth; el.classList.add('flash');
                el.innerHTML = `<div class="pc-rh"><span class="pc-badge">${label}</span><b class="mono bc">${esc(bc)}</b><span class="tm">${jam()}</span></div>`
                    + (msg ? `<div class="pc-msg">${msg}</div>` : '')
                    + (r ? `<div class="pc-facts">
                            <div><small>ID Item</small><b class="mono">${esc(r.item)}</b></div>
                            <div><small>Warna</small><b title="${esc(r.warna)}"><i class="sw" style="background:${fcol(r.warna)}"></i>${esc(r.warna || '-')}</b></div>
                            <div><small>Lot · Roll</small><b>${esc(r.lot)} · ${esc(r.roll)}</b></div>
                            <div class="qty"><small>Qty</small><b>${n2(r.qtyK)} ${esc(u(r))}</b></div>
                        </div>
                        <div class="pc-meta"><b>${esc(r.bppb)}</b> · ${fd(r.tgl)} · ${esc(r.req || '-')}${r.style ? ' · ' + esc(r.style) : ''} · WS ${esc(r.ws)}${r.nama ? `<br>${esc(r.nama)}` : ''}</div>` : '')
                    + (list && list.length > 1 ? hist(list, r) : '');
            }
            function hist(list, cur) {
                return `<div class="pc-hist"><span class="lead">Keluar ${list.length}x:</span>${list.map(h =>
                    `<span class="${h.rcv ? 'y' : ''} ${cur && h.id === cur.id && h.rcv && h.rcv.tgl === TODAY ? 'cur' : ''}" title="${esc(h.bppb)}">${h.rcv ? '✓' : '○'} ${fds(h.tgl)} · ${n2(h.qtyK)} ${esc(u(h))}</span>`).join('')}</div>`;
            }

            function drawPick() {
                const r = pending[0];
                const el = $id('pc-res'); el.className = 'pc-res pick';
                el.innerHTML = `<div class="pc-rh"><span class="pc-badge">? Pilih kiriman</span><b class="mono bc">${esc(r.bc)}</b><span class="tm">ID ${esc(r.item)} · ${esc(r.warna)}</span></div>
                    <div class="pc-msg">Ada <b>${pending.length} kiriman belum diterima</b>. Tekan <b>Enter</b> untuk yang ditandai, atau tekan angkanya.</div>
                    <div class="pc-opts">${pending.map((p, i) => `
                        <button type="button" class="pc-opt ${i === selIdx ? 'on' : ''}" data-i="${i}" style="animation-delay:${i * .05}s"><kbd>${i + 1}</kbd>
                            <b>${esc(p.bppb)}</b><small>${fd(p.tgl)} · keluar ke-${p.ke}</small>
                            <div class="q">${n2(p.qtyK)} <em>${esc(u(p))}</em></div></button>`).join('')}</div>` + hist(pendingList);
                el.querySelectorAll('.pc-opt').forEach(b => b.onclick = () => save(pending[+b.dataset.i], pendingList));
                focus();
            }

            let tt;
            function toast(m) { const t = $id('pc-toast'); t.textContent = m; t.classList.add('on'); clearTimeout(tt); tt = setTimeout(() => t.classList.remove('on'), 1600); }

            /* ===== animasi & suara ===== */
            let FX = true; try { FX = localStorage.getItem('pc_fx') !== '0'; } catch (e) {}
            function fxLabel() { $id('pc-fx').innerHTML = FX ? '<i class="fa fa-volume-up"></i> Animasi & suara: ON' : '<i class="fa fa-volume-mute"></i> Animasi & suara: OFF'; }
            $id('pc-fx').onclick = () => { FX = !FX; try { localStorage.setItem('pc_fx', FX ? '1' : '0'); } catch (e) {} fxLabel(); focus(); };
            fxLabel();

            let ac;
            function tone(f, t0, d, type = 'sine', v = .06) {
                try {
                    ac ||= new (window.AudioContext || window.webkitAudioContext)();
                    const o = ac.createOscillator(), g = ac.createGain(); o.type = type; o.frequency.value = f;
                    g.gain.setValueAtTime(v, ac.currentTime + t0); g.gain.exponentialRampToValueAtTime(.0001, ac.currentTime + t0 + d);
                    o.connect(g); g.connect(ac.destination); o.start(ac.currentTime + t0); o.stop(ac.currentTime + t0 + d + .02);
                } catch (e) {}
            }
            function beep(k) {
                if (!FX) return;
                if (k === 'ok') { tone(880, 0, .07); tone(1320, .06, .12); return; }
                tone({ warn: 620, err: 260, pick: 880 }[k], 0, .25, k === 'err' ? 'square' : 'sine');
            }

            const CMAP = [['BLACK', '#22252b'], ['WHITE', '#f7f7f2'], ['BROWN', '#8b5a2b'], ['HEATHER', '#a3a6ad'], ['GREY', '#9ca3af'], ['GRAY', '#9ca3af'], ['RED', '#d63a2f'], ['WINE', '#7a1f2b'], ['MAROON', '#7a1f2b'], ['VIOLET', '#8b6fd0'], ['PURPLE', '#7e57c2'], ['AMETHYST', '#9b6bc4'], ['MIRAGE', '#3d4c6e'], ['NAVY', '#1f3a6e'], ['BLUE', '#2f6fd6'], ['GREEN', '#2f9e5b'], ['OLIVE', '#6b7a3a'], ['PINK', '#e88bb0'], ['YELLOW', '#f2c94c'], ['ORANGE', '#f28c28'], ['CREAM', '#efe6c8'], ['BEIGE', '#e3d3b0'], ['KHAKI', '#c3b091']];
            function fcol(w) {
                const W = String(w || '').toUpperCase(), f = CMAP.find(([k]) => W.includes(k));
                if (f) return f[1];
                let h = 0; for (const c of W) h = (h * 31 + c.charCodeAt(0)) % 360;
                return `hsl(${h} 45% 55%)`;
            }
            function endSvg(c) {
                return `<svg viewBox="0 0 40 40"><circle cx="20" cy="20" r="18" fill="${c}" stroke="#00000033"/>
                    <circle cx="20" cy="20" r="13.5" fill="none" stroke="#0000001c" stroke-width="1.4"/><circle cx="20" cy="20" r="9" fill="none" stroke="#0000001c" stroke-width="1.4"/>
                    <path d="M7.5 9.5A16 16 0 0 1 20 3.5" stroke="#ffffff66" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                    <path d="M20 15.5V2.5" stroke="#00000030" stroke-width="1.2"/>
                    <circle cx="20" cy="20" r="4.6" fill="#e9edf2" stroke="#00000044"/><circle cx="20" cy="20" r="1.9" fill="#00000055"/></svg>`;
            }
            const KNOT = `<svg class="pc-knot" viewBox="0 0 40 40"><path d="M3 31C9 9 31 7 27 20S7 29 15 14 35 11 31 26 16 37 37 34" fill="none" stroke="#d88202" stroke-width="2.6" stroke-linecap="round"/></svg>`;
            function sew() { if (!FX) return; const b = document.querySelector('.pc-inp'); b.classList.remove('sew'); void b.offsetWidth; b.classList.add('sew'); }
            function fxErr() { if (!FX) return; const c = $id('pc-scan-card'); c.classList.remove('pc-shake'); void c.offsetWidth; c.classList.add('pc-shake'); }
            function fxWarn() { if (!FX) return; const h = document.querySelector('#pc-res .pc-rh'); if (h) h.insertAdjacentHTML('afterbegin', KNOT); }

            function flyRoll(r, from) {
                const slot = document.querySelector('.pc-slot.hold');
                if (!slot) { landRoll(); return; }
                const to = slot.getBoundingClientRect();
                const x0 = from.left + from.width / 2, y0 = from.top + from.height / 2, x1 = to.left + to.width / 2, y1 = to.top + to.height / 2, dx = x1 - x0, dy = y1 - y0, sc = to.width / 44;
                const d = document.createElement('div'); d.className = 'pc-flyer'; d.innerHTML = endSvg(fcol(r.warna)); d.style.left = x0 + 'px'; d.style.top = y0 + 'px'; document.body.appendChild(d);
                const kf = [];
                for (let i = 0; i <= 14; i++) {
                    const t = i / 14, lift = Math.sin(Math.PI * t) * Math.min(160, 40 + Math.abs(dx) * .25);
                    kf.push({ transform: `translate(${dx * t}px,${dy * t - lift}px) rotate(${t * (dx >= 0 ? 720 : -720)}deg) scale(${1 + Math.sin(Math.PI * t) * .25 - (1 - sc) * t})` });
                }
                d.animate(kf, { duration: 720, easing: 'cubic-bezier(.45,0,.55,1)' }).onfinish = () => { d.remove(); landRoll(); threads(x1, y1, 10); };
            }
            function landRoll() {
                const s = document.querySelector('.pc-slot.hold');
                if (s) { s.classList.remove('hold'); if (FX) s.classList.add('land'); }
                if (FX) { const ring = document.querySelector('.pc-ring'); if (ring) { ring.classList.remove('bump'); void ring.offsetWidth; ring.classList.add('bump'); } tone(523, 0, .08, 'triangle', .05); }
                const n = TODAY_ROWS.length;
                if (n && n % PER === 0) setTimeout(() => { toast(`Rak baris ${n / PER} penuh!`); if (FX) { threads(innerWidth / 2, innerHeight * .35, 40); [659, 880].forEach((f, i) => tone(f, i * .09, .14, 'triangle', .05)); } }, 250);
            }
            const TC = ['#238380', '#082149', '#2c9efc', '#ff971f', '#12be60'];
            function threads(x, y, n) {
                if (!FX) return;
                for (let i = 0; i < n; i++) {
                    const s = document.createElement('div'); s.className = 'pc-thr';
                    const big = n > 20, w = big ? 10 + Math.random() * 10 : 6 + Math.random() * 6;
                    Object.assign(s.style, { left: x + 'px', top: y + 'px', width: w + 'px', height: '2.5px', background: TC[i % TC.length] });
                    document.body.appendChild(s);
                    const a = Math.random() * Math.PI * 2, rr = (big ? 120 : 30) + Math.random() * (big ? 220 : 50), rot = Math.random() * 540;
                    s.animate([{ transform: 'translate(-50%,-50%) rotate(0deg)', opacity: 1 }, { transform: `translate(${Math.cos(a) * rr}px,${Math.sin(a) * rr + (big ? 120 : 20)}px) rotate(${rot}deg)`, opacity: 0 }],
                        { duration: (big ? 1400 : 600) + Math.random() * 400, easing: 'cubic-bezier(.1,.7,.3,1)' }).onfinish = () => s.remove();
                }
            }

            /* ===== kamera (html5-qrcode) ===== */
            let qr = null, camOn = false, lastCode = '', lastAt = 0;
            function camBtn() {
                const b = $id('pc-cam');
                b.classList.toggle('btn-primary', camOn); b.classList.toggle('btn-outline-primary', !camOn);
                b.title = camOn ? 'Matikan kamera' : 'Scan pakai kamera';
            }
            async function camStart() {
                $id('pc-reader-wrap').classList.remove('d-none');
                try {
                    qr ||= new Html5Qrcode('pc-reader');
                    await qr.start({ facingMode: 'environment' }, { fps: 10, qrbox: { width: 250, height: 250 } }, onCode);
                    camOn = true;
                } catch (e) {
                    camOn = false; $id('pc-reader-wrap').classList.add('d-none');
                    iziToast.warning({ title: 'Kamera', message: 'Kamera tidak dapat dibuka.', position: 'topCenter', timeout: 2000 });
                }
                try { localStorage.setItem('pc_cam', camOn ? '1' : '0'); } catch (e) {}
                camBtn();
            }
            async function camStop() {
                try { if (qr && qr.isScanning) { await qr.stop(); await qr.clear(); } } catch (e) {}
                camOn = false; $id('pc-reader-wrap').classList.add('d-none');
                try { localStorage.setItem('pc_cam', '0'); } catch (e) {}
                camBtn(); focus();
            }
            function onCode(text) {
                const code = String(text).split('-')[0].trim(), t = Date.now();
                if (!code || (code === lastCode && t - lastAt < 3000)) return;
                lastCode = code; lastAt = t;
                scan(code);
            }
            $id('pc-cam').onclick = () => camOn ? camStop() : camStart();
            camBtn();
            {
                let pref = null; try { pref = localStorage.getItem('pc_cam'); } catch (e) {}
                const touch = window.matchMedia && window.matchMedia('(pointer:coarse)').matches;
                if (pref === '1' || (pref === null && touch)) camStart();
            }

            loadSummary();
            focus();
        })();
    </script>
@endsection
