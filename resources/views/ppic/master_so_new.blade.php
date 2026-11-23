@extends('layouts.index')

@section('custom-link')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <style>

        #mso{
        --navy:#0b1f3f; --navy-2:#13305e; --teal:#0ea5a4; --teal-lt:#e6f7f6;
        --bg:#f3f6fa; --card:#ffffff; --ink:#18212f; --muted:#667085; --line:#e4e9f0; --line-2:#eef2f7;
        --blue:#2563eb; --blue-lt:#eaf1ff;
        --green:#16a34a; --green-lt:#e8f7ee;
        --amber:#d97706; --amber-lt:#fff4e0;
        --violet:#7c3aed; --violet-lt:#f2ecff;
        --rose:#e11d48; --rose-lt:#ffecf0;
        --sky:#0284c7; --sky-lt:#e6f5fd;
        --q-head:#f5f8ff;
        }
        #mso *{box-sizing:border-box}
        #mso{margin:0}
        #mso{background:var(--bg);font-family:"Plus Jakarta Sans",Arial,sans-serif;color:var(--ink);font-size:14px}
        #mso button,#mso input,#mso select{font-family:inherit;font-size:14px;color:inherit}
        #mso .mono{font-family:"JetBrains Mono",monospace;font-size:12.5px}

        #mso .wrap{max-width:none;width:100%;margin:0;padding:22px 20px 40px}

        /* HEADER */
        #mso .ph{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:18px}
        #mso .crumb{font-size:12.5px;color:var(--muted);margin-bottom:4px}
        #mso .ph h1{margin:0;font-size:24px;font-weight:800;letter-spacing:-.3px}
        #mso .ph p{margin:4px 0 0;color:var(--muted)}
        #mso .mbtn{border:1px solid var(--line);background:#fff;border-radius:10px;padding:9px 14px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:7px;white-space:nowrap}
        #mso .mbtn:hover{border-color:#cdd5df;background:#fafbfc}
        #mso .mbtn.pri{background:var(--navy);border-color:var(--navy);color:#fff}
        #mso .mbtn.pri:hover{background:var(--navy-2)}
        #mso .mbtn.teal{background:var(--teal);border-color:var(--teal);color:#fff}
        #mso .mbtn.green{color:var(--green)} #mso .mbtn.blue{color:var(--blue)} #mso .mbtn.amber{color:var(--amber)}
        #mso .mbtn svg{width:16px;height:16px}

        /* KPI */
        #mso .kpis{display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:18px}
        #mso .kpi{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:14px 16px;position:relative;overflow:hidden}
        #mso .kpi .lbl{font-size:12.5px;color:var(--muted);font-weight:600;display:flex;align-items:center;gap:6px}
        #mso .kpi .dot{width:8px;height:8px;border-radius:50%}
        #mso .kpi .val{font-size:26px;font-weight:800;margin-top:6px;letter-spacing:-.5px}
        #mso .kpi .sub{font-size:12px;color:var(--muted);margin-top:2px}
        #mso .bar{height:6px;background:var(--line-2);border-radius:99px;margin-top:10px;overflow:hidden}
        #mso .bar i{display:block;height:100%;border-radius:99px}

        /* CARD */
        #mso .mcard{background:var(--card);border:1px solid var(--line);border-radius:16px;box-shadow:0 1px 2px #1018280a}
        #mso .upload{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;cursor:pointer}
        #mso .upload .l{display:flex;align-items:center;gap:12px}
        #mso .ico{width:38px;height:38px;border-radius:10px;display:grid;place-items:center}
        #mso .ico svg{width:20px;height:20px}
        #mso .upload b{display:block}
        #mso .upload span{font-size:12.5px;color:var(--muted)}
        #mso .upbody{display:none;border-top:1px dashed var(--line);padding:18px}
        #mso .upload-wrap.open .upbody{display:block}
        #mso .drop{border:2px dashed #c7d2e0;border-radius:12px;padding:26px;text-align:center;color:var(--muted);background:#fafcff}
        #mso .drop b{color:var(--ink)}

        /* FILTER */
        #mso .filters{padding:16px 18px;border-bottom:1px solid var(--line);display:grid;grid-template-columns:150px 1fr 1fr 1.1fr 1.1fr 1.1fr auto;gap:12px;align-items:end}
        #mso .f label{display:block;font-size:12px;font-weight:600;color:var(--muted);margin-bottom:6px}
        #mso .f input,#mso .f select{width:100%;height:40px;border:1px solid var(--line);border-radius:10px;padding:0 12px;background:#fff;outline:none}
        #mso .f input:focus,#mso .f select:focus{border-color:var(--teal);box-shadow:0 0 0 3px #0ea5a422}
        #mso .f .acts{display:flex;gap:8px}
        #mso .chips{display:flex;gap:8px;flex-wrap:wrap;padding:12px 18px;border-bottom:1px solid var(--line);align-items:center}
        #mso .chip{background:var(--line-2);border-radius:99px;padding:5px 10px 5px 12px;font-size:12.5px;font-weight:600;display:inline-flex;align-items:center;gap:6px}
        #mso .chip button{border:0;background:none;cursor:pointer;color:var(--muted);padding:0;font-size:14px;line-height:1}
        #mso .chips .muted{color:var(--muted);font-size:12.5px;margin-right:4px}

        /* TOOLBAR */
        #mso .tb{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 18px;flex-wrap:wrap}
        #mso .tb .l{display:flex;align-items:center;gap:8px;color:var(--muted)}
        #mso .tb select{height:34px;border:1px solid var(--line);border-radius:8px;padding:0 8px}
        #mso .search{position:relative}
        #mso .search input{height:38px;width:280px;border:1px solid var(--line);border-radius:10px;padding:0 12px 0 36px;outline:none}
        #mso .search input:focus{border-color:var(--teal);box-shadow:0 0 0 3px #0ea5a422}
        #mso .search svg{position:absolute;left:11px;top:11px;width:16px;height:16px;color:var(--muted)}
        #mso .legend{display:flex;gap:14px;font-size:12px;color:var(--muted);flex-wrap:wrap}
        #mso .legend span{display:inline-flex;align-items:center;gap:5px}
        #mso .legend i{width:10px;height:10px;border-radius:3px;display:inline-block}

        /* TABLE */
        #mso .tw{overflow:auto;max-height:560px;border-top:1px solid var(--line)}
        #mso table{border-collapse:separate;border-spacing:0;width:max-content;min-width:100%}
        #mso th,#mso td{padding:0 12px;white-space:nowrap;text-align:left}
        #mso thead th{position:sticky;background:#f8fafc;z-index:3;font-size:11.5px;text-transform:uppercase;letter-spacing:.4px;color:#475467;font-weight:700}
        #mso thead tr.g th{top:0;height:32px;border-bottom:1px solid var(--line);font-size:11px}
        #mso thead tr.h th{top:32px;height:40px;border-bottom:1px solid var(--line)}
        #mso thead tr.s th{top:72px;height:44px;border-bottom:1px solid var(--line);background:#fff;padding:6px 8px;text-transform:none;letter-spacing:0}
        #mso thead tr.s input{width:100%;min-width:60px;height:30px;border:1px solid var(--line-2);background:#f8fafc;border-radius:7px;padding:0 8px;font-size:12.5px;outline:none}
        #mso thead tr.s input:focus{border-color:var(--teal);background:#fff}
        #mso th.grp-info{color:var(--navy)}
        #mso th.grp-qty{background:var(--q-head)!important;color:var(--blue);text-align:center}
        #mso th.q,#mso td.q{text-align:right}
        #mso th.q{background:var(--q-head)!important}
        #mso tbody td{height:52px;border-bottom:1px solid var(--line-2);font-size:13.5px}
        #mso tbody tr:hover td{background:#f6fbff}
        #mso tbody tr:hover td.stk{background:#f6fbff}
        #mso td.q{font-variant-numeric:tabular-nums;font-weight:600}
        #mso td.q.zero{color:#c0c7d2;font-weight:500}
        #mso td.q.pos{color:var(--green)}
        #mso td.q.neg{color:var(--rose)}
        #mso td.qfirst,#mso th.qfirst{border-left:2px solid #dbe5ff}
        #mso td.qlast,#mso th.qlast{border-right:2px solid #dbe5ff}
        #mso .stk{position:sticky;background:#fff;z-index:2}
        #mso thead .stk{z-index:5}
        #mso .stk1{left:0;width:84px;min-width:84px;max-width:84px}
        #mso .stk2{left:84px;width:230px;min-width:230px;max-width:230px;box-shadow:6px 0 8px -6px #10182822;border-right:1px solid var(--line)}
        #mso thead .stk{background:#f8fafc}
        #mso thead tr.s .stk{background:#fff}
        #mso .buyer b{display:block;font-weight:700;font-size:13px}
        #mso .ws{font-weight:600;color:var(--navy)}
        #mso .date{display:inline-flex;align-items:center;gap:6px}
        #mso .date .d{width:8px;height:8px;border-radius:50%;background:var(--amber)}
        #mso .size{display:inline-grid;place-items:center;min-width:34px;height:26px;padding:0 8px;border-radius:7px;background:var(--navy);color:#fff;font-weight:700;font-size:12px}
        #mso .dest{display:inline-block;padding:2px 8px;border-radius:6px;background:var(--line-2);font-weight:700;font-size:12px}
        #mso .prog{display:flex;align-items:center;gap:8px;min-width:120px}
        #mso .prog .bar{flex:1;margin:0;height:7px}
        #mso .prog small{font-weight:700;font-size:12px;min-width:36px;text-align:right}
        #mso .st{display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:99px;font-weight:700;font-size:12px}
        #mso .st::before{content:"";width:7px;height:7px;border-radius:50%;background:currentColor}
        #mso .st.st-open{background:var(--green-lt);color:var(--green)}
        #mso .st.st-close{background:#eef0f3;color:#475467}
        #mso .ab{width:34px;height:34px;border-radius:9px;border:1px solid var(--line);background:#fff;display:inline-grid;place-items:center;cursor:pointer;color:var(--amber)}
        #mso .ab:hover{background:var(--amber-lt);border-color:#f5d39b}
        #mso .ab svg{width:16px;height:16px}

        #mso tfoot td{position:sticky;bottom:0;background:var(--navy);color:#fff;height:50px;font-weight:700;z-index:3;border:0}
        #mso tfoot td.q{font-size:14px}
        #mso tfoot td.stk{background:var(--navy);z-index:4}
        #mso tfoot .tl{font-size:12px;text-transform:uppercase;letter-spacing:.6px;color:#ffffffd9}

        /* PAGER */
        #mso .pg{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;flex-wrap:wrap;gap:10px;border-top:1px solid var(--line)}
        #mso .pg .info{color:var(--muted)}
        #mso .pg .info b{color:var(--ink)}
        #mso .pages{display:flex;gap:6px}
        #mso .pages button{min-width:36px;height:36px;border:1px solid var(--line);background:#fff;border-radius:9px;font-weight:600;cursor:pointer;padding:0 10px}
        #mso .pages button.on{background:var(--navy);border-color:var(--navy);color:#fff}
        #mso .pages button:disabled{opacity:.45;cursor:not-allowed}




        /* ===== UPLOAD v2 ===== */
        #mso .upbody{padding:0 18px 18px;border-top:1px solid var(--line)}
        #mso .steps{display:flex;gap:10px;padding:14px 0 16px;flex-wrap:wrap}
        #mso .step{display:flex;align-items:center;gap:8px;font-size:12.5px;font-weight:600;color:var(--muted)}
        #mso .step i{width:24px;height:24px;border-radius:50%;display:grid;place-items:center;font-style:normal;font-size:12px;background:var(--line-2)}
        #mso .step.on{color:var(--ink)} #mso .step.on i{background:var(--teal);color:#fff}
        #mso .step.done i{background:var(--green-lt);color:var(--green)}
        #mso .step-sep{width:36px;height:2px;background:var(--line);align-self:center;border-radius:2px}
        #mso .up-top{display:grid;grid-template-columns:1.4fr 1fr;gap:14px}
        #mso .drop{text-align:left;border:2px dashed #c7d2e0;border-radius:14px;padding:18px;display:flex;align-items:center;gap:14px;background:#fafcff;cursor:pointer;transition:.15s}
        #mso .drop:hover{border-color:var(--teal);background:#f3fbfb}
        #mso .drop .ico{width:46px;height:46px;border-radius:12px;background:var(--green-lt);color:var(--green);flex:none}
        #mso .drop .fn{font-weight:700}
        #mso .drop .fm{font-size:12.5px;color:var(--muted)}
        #mso .drop .chg{margin-left:auto}
        #mso .helpers{display:grid;gap:10px}
        #mso .helper{display:flex;align-items:center;gap:12px;border:1px solid var(--line);border-radius:12px;padding:10px 12px;text-decoration:none;color:inherit}
        #mso .helper:hover{background:#fafbfc}
        #mso .helper .ico{width:34px;height:34px;border-radius:9px;flex:none}
        #mso .helper .ico svg{width:17px;height:17px}
        #mso .helper b{display:block;font-size:13.5px}
        #mso .helper span{font-size:12px;color:var(--muted)}
        #mso .helper .go{margin-left:auto;color:var(--muted)}
        #mso .pv-h{display:flex;align-items:center;justify-content:space-between;margin:22px 0 10px;gap:10px;flex-wrap:wrap}
        #mso .pv-h h3{margin:0;font-size:16px}
        #mso .pv-h p{margin:2px 0 0;font-size:12.5px;color:var(--muted)}
        #mso .sum{display:grid;grid-template-columns:repeat(5,1fr);gap:10px}
        #mso .sbox{border:1px solid var(--line);border-radius:12px;padding:10px 12px;cursor:pointer;text-align:left;background:#fff;transition:.12s}
        #mso .sbox:hover{border-color:#cdd5df}
        #mso .sbox.on{outline:2px solid currentColor;outline-offset:-2px}
        #mso .sbox .t{font-size:12px;font-weight:700;display:flex;align-items:center;gap:6px}
        #mso .sbox .t::before{content:"";width:8px;height:8px;border-radius:50%;background:currentColor}
        #mso .sbox .n{font-size:22px;font-weight:800;color:var(--ink);margin-top:2px}
        #mso .sbox .d{font-size:11.5px;color:var(--muted);line-height:1.3}
        #mso .c-all{color:var(--navy)} #mso .c-ok{color:var(--green)} #mso .c-dbl{color:var(--amber)} #mso .c-inv{color:var(--rose)} #mso .c-ada{color:var(--violet)}
        #mso .pv-tw{overflow:auto;max-height:340px;border:1px solid var(--line);border-radius:12px;margin-top:12px}
        #mso .pv-tw table{min-width:100%}
        #mso .pv-tw thead th{top:0;height:40px;border-bottom:1px solid var(--line)}
        #mso .pv-tw tbody td{height:46px;font-size:13px}
        #mso .pv-tw tbody tr.bad td{background:#fff7f8}
        #mso .pv-tw tbody tr.warn td{background:#fffaf0}
        #mso .pv-tw tbody tr.ada td{background:#faf7ff}
        #mso .pv-tw tbody td.err{color:var(--rose);font-weight:700}
        #mso .pv-tw tfoot td{height:44px}
        #mso .tag{display:inline-flex;align-items:center;gap:6px;padding:3px 10px;border-radius:99px;font-weight:700;font-size:12px;white-space:nowrap}
        #mso .tag::before{content:"";width:7px;height:7px;border-radius:50%;background:currentColor}
        #mso .tag.ok{background:var(--green-lt);color:var(--green)}
        #mso .tag.dbl{background:var(--amber-lt);color:var(--amber)}
        #mso .tag.inv{background:var(--rose-lt);color:var(--rose)}
        #mso .tag.ada{background:var(--violet-lt);color:var(--violet)}
        #mso .ket{font-size:12.5px;color:#475467;white-space:normal;min-width:220px;max-width:260px;line-height:1.35}
        #mso .del{width:30px;height:30px;border-radius:8px;border:1px solid var(--line);background:#fff;display:inline-grid;place-items:center;cursor:pointer;color:var(--rose)}
        #mso .del:hover{background:var(--rose-lt);border-color:#f8c2cd}
        #mso .del svg{width:15px;height:15px}
        #mso .up-foot{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:14px;flex-wrap:wrap}
        #mso .up-foot .msg{font-size:13px;color:var(--muted)}
        #mso .up-foot .msg b{color:var(--ink)}
        #mso .mbtn.save{background:var(--green);border-color:var(--green);color:#fff}
        #mso .mbtn.save:hover{background:#13883e}
        #mso .mbtn.ghost-amber{color:var(--amber);border-color:#f5d39b}
        #mso .empty{padding:28px;text-align:center;color:var(--muted)}
        @media(max-width:1000px){#mso .up-top{grid-template-columns:1fr}#mso .sum{grid-template-columns:repeat(2,1fr)}}


        /* ===== KPI interaktif + drawer + notif ===== */
        #mso .kpi{cursor:pointer;transition:.15s;text-align:left;font:inherit;color:inherit}
        #mso .kpi:hover{border-color:#c9d3e0;box-shadow:0 6px 18px -8px #10182833;transform:translateY(-1px)}
        #mso .kpi .more{position:absolute;right:12px;top:12px;font-size:11.5px;font-weight:700;color:var(--muted);opacity:0;transition:.15s}
        #mso .kpi:hover .more{opacity:1}
        #mso .hx{display:inline-block;padding:2px 8px;border-radius:99px;font-size:11.5px;font-weight:800;white-space:nowrap}
        #mso .hx.late{background:var(--rose);color:#fff}
        #mso .hx.red{background:var(--rose-lt);color:var(--rose)}
        #mso .hx.amb{background:var(--amber-lt);color:var(--amber)}
        #mso .hx.ok{background:var(--line-2);color:var(--muted)}
        #mso .date .d.red{background:var(--rose)} #mso .date .d.amb{background:var(--amber)} #mso .date .d.grn{background:var(--green)} #mso .date .d.gry{background:#c0c7d2}
        #mso .ov{position:fixed;inset:0;background:#0b1f3f55;backdrop-filter:blur(2px);z-index:50;opacity:0;pointer-events:none;transition:.2s}
        #mso .ov.on{opacity:1;pointer-events:auto}
        #mso .drw{position:fixed;top:0;right:0;bottom:0;width:min(880px,100%);background:#fff;z-index:51;transform:translateX(100%);transition:.25s ease;display:flex;flex-direction:column;box-shadow:-20px 0 40px -20px #10182844}
        #mso .drw.on{transform:none}
        #mso .drw-h{padding:18px 20px;border-bottom:1px solid var(--line);display:flex;gap:12px;align-items:flex-start}
        #mso .drw-h h3{margin:0;font-size:18px}
        #mso .drw-h p{margin:3px 0 0;font-size:12.5px;color:var(--muted)}
        #mso .drw-h .x{margin-left:auto;border:1px solid var(--line);background:#fff;border-radius:9px;width:34px;height:34px;font-size:18px;cursor:pointer}
        #mso .drw-s{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;padding:14px 20px}
        #mso .drw-s div{background:#f8fafc;border-radius:10px;padding:10px 12px}
        #mso .drw-s small{display:block;font-size:11.5px;color:var(--muted);font-weight:600}
        #mso .drw-s b{font-size:20px}
        #mso .drw-t{display:flex;gap:6px;padding:0 20px 10px}
        #mso .drw-t button{border:1px solid var(--line);background:#fff;border-radius:99px;padding:6px 12px;font-weight:600;font-size:12.5px;cursor:pointer}
        #mso .drw-t button.on{background:var(--navy);color:#fff;border-color:var(--navy)}
        #mso .drw-b{overflow:auto;padding:0 20px 20px;flex:1}
        #mso .drw-f{display:flex;gap:8px;padding:0 20px 10px}
        #mso .drw-f input,#mso .drw-f select{height:32px;border:1px solid var(--line);border-radius:99px;padding:0 12px;font-size:12.5px;background:#fff;outline:none}
        #mso .drw-f input{flex:1;min-width:0}
        #mso .drw-f input:focus,#mso .drw-f select:focus{border-color:var(--teal)}
        #mso .drw-pg{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:10px 20px;border-top:1px solid var(--line);font-size:12.5px;color:var(--muted)}
        #mso .drw-pg:empty{display:none}
        #mso .drw-pg b{color:var(--ink)}
        #mso .drw-pg .pb{display:flex;gap:4px}
        #mso .drw-pg button{border:1px solid var(--line);background:#fff;border-radius:99px;min-width:30px;height:30px;padding:0 10px;font-weight:600;font-size:12.5px;cursor:pointer}
        #mso .drw-pg button.on{background:var(--navy);color:#fff;border-color:var(--navy)}
        #mso .drw-pg button:disabled{opacity:.45;cursor:not-allowed}
        #mso .porow{border:1px solid var(--line);border-radius:12px;padding:12px 14px;margin-bottom:10px;cursor:pointer;transition:.12s}
        #mso .porow:hover{border-color:#b9c6d8;background:#fbfcfe}
        #mso .porow .r1{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
        #mso .porow .r1 b{font-size:14px}
        #mso .porow .r1 .sp{flex:1}
        #mso .porow .meta{font-size:12px;color:var(--muted);margin-top:2px}
        #mso .porow .nums{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-top:10px}
        #mso .porow .nums div{font-size:11.5px;color:var(--muted)}
        #mso .porow .nums b{display:block;font-size:15px;color:var(--ink)}
        #mso .porow .nums .kur b{color:var(--rose)}
        #mso .porow .sz{display:flex;gap:6px;flex-wrap:wrap;margin-top:10px}
        #mso .porow .sz span{font-size:11.5px;background:var(--rose-lt);color:var(--rose);border-radius:7px;padding:3px 8px;font-weight:700}
        #mso .porow .sz span.okz{background:var(--green-lt);color:var(--green)}
        #mso .porow .go{font-size:12px;color:var(--teal);font-weight:700}


        #mso .alert2{width:100%;display:flex;align-items:center;gap:14px;flex-wrap:wrap;text-align:left;font:inherit;color:var(--ink);background:#fff1f2;border:1px solid #fecdd3;border-radius:14px;padding:12px 16px;margin-bottom:16px;cursor:pointer;transition:.15s}
        #mso .alert2:hover{border-color:#fda4af;box-shadow:0 6px 18px -10px #e11d4855}
        #mso .alert2 .ai{width:34px;height:34px;border-radius:10px;background:#fff;color:var(--rose);display:grid;place-items:center;flex:none}
        #mso .alert2 .ai svg{width:18px;height:18px}
        #mso .alert2 .at{font-size:15px;font-weight:600}
        #mso .alert2 .at b{font-size:20px;font-weight:800;color:var(--rose)}
        #mso .alert2 .asep{width:1px;height:24px;background:#fecdd3}
        #mso .alert2 .ago{margin-left:auto;font-weight:700;color:var(--rose);font-size:13.5px;white-space:nowrap}


        /* ===== WARNA LEBIH HIDUP ===== */
        #mso{background:radial-gradient(1200px 500px at 100% -10%,#e0f7f5 0,transparent 60%),radial-gradient(900px 400px at -10% 10%,#ece8ff 0,transparent 55%),var(--bg)}
        #mso .ph{background:linear-gradient(120deg,#0b1f3f 0%,#1d4ed8 55%,#0ea5a4 100%);color:#fff;border-radius:18px;padding:22px 24px;position:relative;overflow:hidden;box-shadow:0 12px 30px -16px #1d4ed888}
        #mso .ph::after{content:"";position:absolute;right:-60px;top:-80px;width:280px;height:280px;border-radius:50%;background:#ffffff14}
        #mso .ph::before{content:"";position:absolute;right:160px;bottom:-110px;width:220px;height:220px;border-radius:50%;background:#2dd4bf22}
        #mso .ph>*{position:relative;z-index:1}
        #mso .ph .crumb{color:#bfd3f0} #mso .ph p{color:#d7e5f7}
        #mso .kpi{--c:#0b1f3f;background:linear-gradient(150deg,color-mix(in srgb,var(--c) 13%,#fff) 0%,#fff 70%);border-color:color-mix(in srgb,var(--c) 22%,#fff);border-top:4px solid var(--c)}
        #mso .kpi .val{color:var(--c)}
        #mso .kpi .lbl{color:color-mix(in srgb,var(--c) 70%,#475467)}
        #mso .kpi .bar{background:color-mix(in srgb,var(--c) 12%,#fff)}
        #mso .mcard>div:first-child .ico{background:linear-gradient(135deg,#dbeafe,#ccfbf1)!important;color:#1d4ed8!important}
        #mso thead tr.g th.g-ord{background:#e8edf6!important;color:var(--navy)}
        #mso thead tr.g th.g-det{background:#e3f6f5!important;color:#0b7f7e}
        #mso thead tr.g th.grp-qty{background:linear-gradient(90deg,#e0eaff,#efe7ff)!important;color:#4338ca}
        #mso thead tr.g th.g-prg{background:#e6f6ec!important;color:#15803d}
        #mso tr.h th.q{border-top:3px solid var(--qc,#2563eb);color:var(--qc,#2563eb)!important}
        #mso tbody tr:nth-child(even) td{background:#fbfcff}
        #mso tbody tr:nth-child(even) td.stk{background:#fbfcff}
        #mso .size{background:linear-gradient(135deg,#1d4ed8,#0ea5a4)}
        #mso .ws{color:#1d4ed8}
        #mso .dest{background:#e0f2fe;color:#0369a1}
        #mso tfoot td,#mso tfoot td.stk{background:#0f2a52}
        #mso .pages button.on{background:linear-gradient(135deg,#1d4ed8,#0ea5a4);border-color:transparent}
        #mso .mbtn.pri{background:linear-gradient(135deg,#0b1f3f,#1d4ed8);border-color:transparent}
        #mso .chip{background:#e0f2fe;color:#0369a1}

        /* ===== NOTIF PO MEPET (soft glow + teks berjalan) ===== */
        #mso .alert2{position:relative;overflow:hidden}
        #mso .alert2:hover{box-shadow:0 14px 28px -12px #e11d48cc;transform:translateY(-1px)}
        #mso .alert2>*{position:relative;z-index:1}
        #mso .alert2 .ai{background:#fff;color:#e11d48;position:relative}
        #mso .alert2 .ai::before{content:"";position:absolute;inset:0;border-radius:10px;border:2px solid #fff;animation:ping 1.6s cubic-bezier(0,0,.2,1) infinite}
        @keyframes ping{0%{transform:scale(1);opacity:.9}100%{transform:scale(1.8);opacity:0}}
        #mso .alert2 .ai svg{animation:wig 2.4s ease-in-out infinite}
        @keyframes wig{0%,80%,100%{transform:rotate(0)}84%{transform:rotate(-14deg)}88%{transform:rotate(12deg)}92%{transform:rotate(-8deg)}96%{transform:rotate(5deg)}}
        #mso .alert2 .at b{font-size:22px}
        #mso .alert2 #aN{margin-right:6px} #mso .alert2 #aQ{margin:0 6px}        #mso .alert2 .ago{padding:6px 12px;border-radius:99px;margin-left:0}
        #mso .ticker{flex:1;min-width:200px;overflow:hidden;white-space:nowrap;-webkit-mask-image:linear-gradient(90deg,transparent,#000 6%,#000 94%,transparent);mask-image:linear-gradient(90deg,transparent,#000 6%,#000 94%,transparent)}
        #mso .ticker .tk{display:inline-flex;gap:28px;padding-left:100%;animation:tick 22s linear infinite}
        #mso .alert2:hover .ticker .tk{animation-play-state:paused}
        @keyframes tick{to{transform:translateX(-100%)}}
        #mso .ticker .ti{font-size:13px;font-weight:600}
        #mso .ticker .ti em{font-style:normal;border-radius:99px;padding:1px 8px;margin-right:6px;font-weight:800}
        #mso .mini-spin{display:inline-block;width:16px;height:16px;border:2px solid #fecdd3;border-top-color:#e11d48;border-radius:50%;animation:mspin .8s linear infinite;vertical-align:-2px}
        @keyframes mspin{to{transform:rotate(360deg)}}
        /* ukuran spinner = ukuran angka qty */
        #mso .alert2 .at b .mini-spin{width:22px;height:22px;border-width:3px;vertical-align:-4px}
        #mso .kpi .val .mini-spin{width:28px;height:28px;border-width:3px;vertical-align:-5px;border-color:color-mix(in srgb,var(--c) 18%,#fff);border-top-color:var(--c)}
        #mso .alert2 .aload{display:none;flex:1;min-width:200px;font-size:13px;font-weight:600;color:#9f1239;text-align:center}
        #mso .alert2.mepet-loading .aload{display:block}
        #mso .alert2.mepet-loading .ticker{display:none}
        #mso .alert2.mepet-loading{cursor:progress}

        /* A: Soft Glow (pastel pink, bernapas pelan) */
        #mso .alert2.opt-a{animation:aGlow 2.6s ease-in-out infinite;background:#fff1f2;border:1px solid #fecdd3;color:var(--ink)}
        @keyframes aGlow{0%,100%{box-shadow:0 0 0 0 #fb718500,0 4px 14px -8px #e11d4840;background:#fff1f2}50%{box-shadow:0 0 0 6px #fb718522,0 4px 14px -8px #e11d4840;background:#ffe4e8}}
        #mso .alert2.opt-a .ai{animation:none;background:#fff;color:#e11d48;box-shadow:0 1px 3px #e11d4822}
        #mso .alert2.opt-a .ai::before{border-color:#fb7185;animation:ping 2.2s cubic-bezier(0,0,.2,1) infinite}
        #mso .alert2.opt-a .at{color:#4b1d24} #mso .alert2.opt-a .at b{color:#e11d48}
        #mso .alert2.opt-a .asep{background:#fecdd3}
        #mso .alert2.opt-a .ticker .ti{color:#9f1239} #mso .alert2.opt-a .ticker .ti em{background:#fff;color:#e11d48}
        #mso .alert2.opt-a .ago{background:#e11d48;color:#fff}

        @media (prefers-reduced-motion:reduce){#mso .alert2.opt-a,#mso .alert2 .ai::before,#mso .alert2 .ai svg,#mso .ticker .tk{animation:none}}


        /* ===== Undo / Batal Upload ===== */
        #mso .pv-empty{margin-top:18px;border:2px dashed #dbe3ee;border-radius:14px;padding:30px;display:flex;flex-direction:column;align-items:center;gap:6px;text-align:center;color:var(--muted);background:#fafcff}
        #mso .pv-empty b{color:var(--ink);font-size:15px}
        #mso .pv-empty .mbtn{margin-top:8px}
        #mso .pe-ico{width:46px;height:46px;border-radius:12px;background:#e0f2fe;color:#0369a1;display:grid;place-items:center;margin-bottom:4px}
        #mso .pe-ico svg{width:22px;height:22px}
        #mso .drop>div{text-align:left}
        #mso .drop.empty .ico{background:#e0f2fe;color:#0369a1}
        #mso .drop.empty .fn{color:var(--muted)}
        #mso .drop.drag{border-color:var(--teal);background:#f3fbfb}
        #mso .cfm-ov{position:fixed;inset:0;background:#0b1f3f55;backdrop-filter:blur(2px);z-index:60;display:none;align-items:center;justify-content:center;padding:16px}
        #mso .cfm-ov.on{display:flex}
        #mso .cfm{background:#fff;border-radius:18px;padding:22px;width:min(440px,100%);box-shadow:0 30px 60px -20px #0b1f3f66;animation:pop .18s ease-out}
        @keyframes pop{from{transform:scale(.95);opacity:0}to{transform:none;opacity:1}}
        #mso .cfm-ico{width:44px;height:44px;border-radius:12px;background:var(--amber-lt);color:var(--amber);display:grid;place-items:center}
        #mso .cfm-ico svg{width:22px;height:22px}
        #mso .cfm h3{margin:12px 0 4px;font-size:17px}
        #mso .cfm p{margin:0 0 16px;color:var(--muted);font-size:13.5px;line-height:1.45}
        #mso .cfm-b{display:flex;flex-direction:column;gap:8px}
        #mso .cfm-b .mbtn{justify-content:center;padding:11px 14px}
        #mso .mbtn.amber-s{background:var(--amber);border-color:var(--amber);color:#fff}
        #mso .mbtn.rose-s{background:var(--rose);border-color:var(--rose);color:#fff}
        #mso .mbtn.save:disabled{opacity:.45;cursor:not-allowed}


        #mso .porow.open{border-color:#93c5fd;box-shadow:0 6px 16px -10px #1d4ed855}
        #mso .pod{margin-top:12px;border:1px solid var(--line);border-radius:10px;overflow:auto;cursor:default}
        #mso .pod table{width:100%;border-collapse:collapse;min-width:700px}
        #mso .pod th{white-space:normal;line-height:1.2;vertical-align:bottom}
        #mso .pod th,#mso .pod td{padding-left:8px!important;padding-right:8px!important}
        #mso .pod th{background:#f1f5fb;font-size:11px;text-transform:uppercase;letter-spacing:.3px;color:#475467;padding:8px 10px;text-align:left;position:static}
        #mso .pod td{padding:7px 10px;border-top:1px solid var(--line-2);font-size:13px;height:auto}
        #mso .pod th.q,#mso .pod td.q{text-align:right}
        #mso .pod td.swo{color:var(--rose);font-weight:700}
        #mso .pod td.swi{color:var(--green);font-weight:700}
        #mso .pod td.z{color:#c0c7d2}
        #mso .pod td.kr{color:var(--rose);font-weight:800}
        #mso .pod td.okk{color:var(--green);font-weight:700;font-size:12px}
        #mso .pod tr.okr td{background:#f6fdf8}
        #mso .pod .size{min-width:30px;height:22px;font-size:11px}
        #mso .pod tfoot td{position:static;background:#0f2a52;color:#fff;font-weight:700;height:auto}


        /* ===== LIST MASTER COMPACT ===== */
        #mso .tw th,#mso .tw td{padding:0 9px}
        #mso thead tr.h th{height:44px;line-height:1.15;vertical-align:middle}
        #mso thead tr.h th small{display:block;font-size:10px;font-weight:600;color:#98a2b3;letter-spacing:.2px}
        #mso thead tr.h th.q small{color:inherit;opacity:.65}
        #mso thead tr.g th{height:28px}
        #mso thead tr.h th{top:28px}
        #mso thead tr.s th{top:72px;height:40px}
        #mso thead tr.s input{height:28px;min-width:44px}
        #mso tbody td{height:46px;font-size:13px}
        #mso td.two{line-height:1.25}
        #mso td.two>b{display:block;font-weight:700;font-size:13px}
        #mso td.two>b.mono{font-size:12.5px}
        #mso td.two>span{display:block;font-size:11.5px;color:var(--muted);margin-top:2px}
        #mso td.two>span.mono{font-size:11px}
        #mso td.two .size{height:20px;min-width:30px;font-size:11px;border-radius:6px}
        #mso td.two .dest{font-size:10.5px;padding:1px 6px}
        #mso td.sw span{display:block;line-height:1.2;font-size:12.5px}
        #mso td.sw span.neg{color:var(--rose)} #mso td.sw span.pos{color:var(--green)} #mso td.sw span.zero{color:#c0c7d2;font-weight:500}
        #mso tfoot td.sw span{color:#fff}
        #mso .stk1{width:70px;min-width:70px;max-width:70px}
        #mso .stk2{left:70px;width:180px;min-width:180px;max-width:180px}
        #mso .buyer b{font-size:12.5px;white-space:normal;line-height:1.25}
        #mso .prog{min-width:84px}
        #mso td .prog .bar{min-width:40px}
        #mso .st{padding:3px 9px;font-size:11.5px}
        #mso .ab{width:30px;height:30px}
        #mso tr.h th.q{--qc:#2563eb}
        #mso tr.h th.q:nth-child(8){--qc:#0b1f3f} #mso tr.h th.q:nth-child(9){--qc:#0891b2} #mso tr.h th.q:nth-child(10){--qc:#7c3aed}
        #mso tr.h th.q:nth-child(11){--qc:#0284c7} #mso tr.h th.q:nth-child(12){--qc:#e11d48} #mso tr.h th.q:nth-child(13){--qc:#15803d}


        /* detail per size compact */
        #mso .pod table{min-width:0!important}
        #mso .pod th{padding:7px 8px!important;font-size:10.5px;border-top:3px solid var(--qc,transparent);color:var(--qc,#475467)!important}
        #mso .pod th small{display:block;font-size:9.5px;font-weight:600;opacity:.7;text-transform:uppercase}
        #mso .pod td{padding:5px 8px!important;font-size:12.5px}
        #mso .pod td.sb{white-space:nowrap}
        #mso .pod td.sb .size{margin-right:8px;vertical-align:middle}
        #mso .pod td.sb .mono{font-size:11px;color:var(--muted)}
        #mso .pod td.sw2 span{display:block;line-height:1.15;font-size:12px}
        #mso .pod td.sw2 span.swo{color:var(--rose);font-weight:700}#mso .pod td.sw2 span.swi{color:var(--green);font-weight:700}#mso .pod td.sw2 span.z{color:#c0c7d2}
        #mso .pod tfoot td.sw2 span{color:#fff}
        #mso .pod td.okk{font-size:14px}

        #mso .kpi .bal{color:#e11d48;font-weight:600}
        #mso .kpi .bal b{font-weight:800}

        #mso .legend.lg2{display:flex;flex-wrap:wrap;column-gap:12px;row-gap:6px;align-items:center}
        #mso .legend.lg2 .lsep{width:1px;height:16px;background:var(--line);margin:0 4px}
        #mso .legend.lg2 b{color:#475467}
        #mso .legend.lg2 span{display:inline-flex;align-items:center;gap:6px;white-space:nowrap}
        #mso .legend.lg2 i{width:10px;height:10px;flex:none}
        #mso .legend.lg2 i.mk{width:10px;height:auto;background:none!important;font-style:normal;font-weight:800;font-size:13px;text-align:center;line-height:1}

        @media(max-width:1200px){
        #mso .kpis{grid-template-columns:repeat(3,1fr)}
        #mso .filters{grid-template-columns:1fr 1fr 1fr}
        }
        @media(max-width:700px){
        #mso .wrap{padding:16px}
        #mso .kpis{grid-template-columns:1fr 1fr}
        #mso .filters{grid-template-columns:1fr}
        #mso .search input{width:100%}
        }

        /* ===== penyesuaian di dalam layout AdminLTE ===== */
        #mso{background:transparent;font-size:14px;line-height:1.5}
        #mso .wrap{padding:4px 0 24px}
        #mso label.drop{font-weight:400;margin:0}
        #mso .ph h1{color:#fff}
        #mso .mbtn:focus{box-shadow:none;outline:none}
        #mso .ov{z-index:1040} #mso .drw{z-index:1041} #mso .cfm-ov{z-index:1050}
        /* style.css ERP memaksa th{vertical-align:bottom!important} & td,th{font-size:13.5px!important} -> kembalikan seperti mockup */
        #mso th{vertical-align:middle!important}
        #mso .pod th{vertical-align:bottom!important}
        #mso thead th{font-size:11.5px!important}
        #mso thead tr.g th{font-size:11px!important}
        #mso thead tr.s th{font-size:14px!important}
        #mso tbody td{font-size:13px!important}
        #mso tbody td.mono{font-size:12.5px!important}
        #mso tfoot td{font-size:13px!important}
        #mso tfoot td.q{font-size:14px!important}
        #mso tfoot .tl{font-size:12px!important}
        #mso .pv-tw thead th{font-size:11.5px!important}
        #mso .pod th{font-size:10.5px!important}
        #mso .pod td{font-size:12.5px!important}
        #mso .pod td.okk{font-size:14px!important}
        /* select2 filter: samakan tinggi dengan input tanggal */
        #mso .f .select2-container--bootstrap4 .select2-selection{height:40px!important;border-radius:10px!important;border-color:var(--line);padding:0!important}
        #mso .f .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered{line-height:38px;padding-left:12px}
        #mso .f .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow{height:38px;top:0;right:6px}
        #mso .f .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow b{top:50%;margin-top:-2px}

        /* ===== Modal Edit ===== */
        .me-modal .modal-dialog{max-width:520px}
        .me-modal .modal-content{border:0;border-radius:18px;overflow:hidden;font-family:"Plus Jakarta Sans",Arial,sans-serif;color:#18212f;box-shadow:0 30px 60px -20px #0b1f3f66}
        .me-modal .me-h{display:flex;align-items:flex-start;gap:12px;padding:18px 20px 14px;border-bottom:1px solid #eef2f7}
        .me-modal .me-ico{width:40px;height:40px;border-radius:11px;background:#fff4e0;color:#d97706;display:grid;place-items:center;flex:none}
        .me-modal .me-ico svg{width:20px;height:20px}
        .me-modal .me-h h3{margin:0;font-size:17px;font-weight:800}
        .me-modal .me-h p{margin:3px 0 0;font-size:11px;color:#667085;line-height:1.4}
        .me-modal .me-h .btn-close{margin-left:auto;font-size:12px}
        .me-modal .modal-body{padding:16px 20px 6px}
        .me-modal .me-sec{margin-bottom:14px}
        .me-modal .me-sec>b{display:block;font-size:12px;font-weight:700;color:#475467;text-transform:uppercase;letter-spacing:.4px;margin-bottom:8px}
        .me-modal .me-row{display:grid;grid-template-columns:1fr 26px 1fr;align-items:end;gap:8px}
        .me-modal .me-row .arr{height:40px;display:grid;place-items:center;color:#98a2b3;font-size:18px}
        .me-modal label{display:block;font-size:11.5px;font-weight:600!important;color:#667085;margin:0 0 5px}
        .me-modal .form-control{height:40px;border:1px solid #e4e9f0;border-radius:10px;font-size:14px;padding:0 12px;box-shadow:none}
        .me-modal .form-control[readonly]{background:#f8fafc;color:#667085}
        .me-modal .form-control:focus{border-color:#0ea5a4;box-shadow:0 0 0 3px #0ea5a422}
        .me-modal .modal-footer{border-top:1px solid #eef2f7;padding:12px 20px;gap:8px}
        .me-modal .modal-footer .btn{border-radius:10px;font-weight:600;padding:8px 16px;font-size:14px}
        .me-modal .me-save{background:#16a34a;border-color:#16a34a;color:#fff}
        .me-modal .me-save:hover{background:#13883e;border-color:#13883e;color:#fff}

        /* ===== Modal Edit Multiple ===== */
        .mm-modal .modal-dialog{max-width:min(1240px,96vw)}
        .mm-tabs{display:flex;gap:6px;padding:12px 20px 0}
        .mm-tabs button{border:1px solid #e4e9f0;background:#fff;border-radius:99px;padding:6px 14px;font-weight:600;font-size:13px;cursor:pointer;color:#475467;display:inline-flex;align-items:center;gap:6px}
        .mm-tabs button.on{background:#0b1f3f;border-color:#0b1f3f;color:#fff}
        .mm-tabs button.on[data-tab=hapus]{background:#e11d48;border-color:#e11d48}
        .mm-filter{display:grid;grid-template-columns:1.3fr 1fr 1fr auto;gap:12px;align-items:end;margin-bottom:14px}
        .mm-filter.two{grid-template-columns:1.3fr 1fr 2fr}
        .mm-bar{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:8px;flex-wrap:wrap}
        .mm-bar .cnt{font-size:12.5px;color:#667085}
        .mm-bar .cnt b{color:#18212f}
        .mm-bar input{height:34px;width:240px;border:1px solid #e4e9f0;border-radius:9px;padding:0 10px;font-size:13px;outline:none}
        .mm-bar input:focus{border-color:#0ea5a4;box-shadow:0 0 0 3px #0ea5a422}
        .mm-tw{overflow:auto;max-height:46vh;border:1px solid #e4e9f0;border-radius:12px}
        .mm-t{border-collapse:separate;border-spacing:0;width:max-content;min-width:100%}
        .mm-t th{position:sticky;top:0;background:#f8fafc;z-index:1;font-size:11px!important;text-transform:uppercase;letter-spacing:.3px;color:#475467;font-weight:700;padding:10px;border-bottom:1px solid #e4e9f0;white-space:nowrap;vertical-align:middle!important;text-align:left}
        .mm-t td{font-size:13px!important;padding:6px 10px;border-bottom:1px solid #eef2f7;white-space:nowrap;vertical-align:middle}
        .mm-t tbody tr:hover td{background:#f6fbff}
        .mm-t th.q,.mm-t td.q{text-align:right}
        .mm-t th.c,.mm-t td.c{text-align:center}
        .mm-in{height:32px;border:1px solid #e4e9f0;border-radius:8px;padding:0 8px;font-size:13px;outline:none;background:#fff}
        .mm-in:focus{border-color:#0ea5a4;box-shadow:0 0 0 3px #0ea5a422}
        .mm-in[readonly]{background:#f3f5f8;color:#98a2b3;cursor:not-allowed}
        .mm-in.w-bc{width:140px} .mm-in.w-q{width:90px;text-align:right} .mm-in.w-po{width:120px} .mm-in.w-tgl{width:140px}
        .mm-empty{text-align:center;color:#667085;padding:28px 10px!important}
        .mm-foot{display:flex;justify-content:flex-end;margin:12px 0 6px}
        .mm-cb{width:17px;height:17px;cursor:pointer;vertical-align:middle}
        .mm-lock{color:#c0c7d2}
        .me-modal .me-del{background:#e11d48;border-color:#e11d48;color:#fff}
        .me-modal .me-del:hover{background:#be123c;border-color:#be123c;color:#fff}
        .me-modal .me-outline{border:1px solid #0ea5a4;color:#0b7f7e;background:#fff;height:40px;white-space:nowrap}
        .me-modal .me-outline:hover{background:#e6f7f6;color:#0b7f7e}
        .mm-modal .select2-container--bootstrap4 .select2-selection{height:40px!important;border-radius:10px!important;border-color:#e4e9f0;padding:0!important}
        .mm-modal .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered{line-height:38px;padding-left:12px}
        .mm-modal .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow{height:38px;top:0;right:6px}
        .mm-modal .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow b{top:50%;margin-top:-2px}
        @media(max-width:768px){.mm-filter,.mm-filter.two{grid-template-columns:1fr}}

        /* ===== Tombol Panduan di header ===== */
        /* overflow:clip = tetap memotong dekorasi, tapi isinya tidak bisa ikut ter-scroll */
        #mso .ph,#mso .alert2,#mso .kpi{overflow:clip}
        #mso .ph-guide{border:1px solid #ffffff55;background:#ffffff1f;color:#fff;border-radius:10px;padding:8px 14px;font-weight:600;font-size:13.5px;cursor:pointer;display:inline-flex;align-items:center;gap:7px;white-space:nowrap}
        #mso .ph-guide:hover,#mso .ph-guide:focus-visible{background:#ffffff38;outline:none}

        /* ===== Panduan (pola sama seperti Packing List) ===== */
        #soGuide,.sg-spot-l{--g-ink:#18212f;--g-muted:#667085;--g-line:#e4e9f0;--g-soft:#f5f8fc;--g-pri:#1d4ed8;--g-acc:#0ea5a4}
        #soGuide .modal-content{border:0;border-radius:18px;overflow:hidden;font-family:"Plus Jakarta Sans",Arial,sans-serif}
        #soGuide .sg-head{background:linear-gradient(120deg,#0b1f3f 0%,#1d4ed8 55%,#0ea5a4 100%);color:#fff;border:0}
        .sg{padding:0 20px 20px;color:var(--g-ink);font-size:13.5px}
        .sg-nav{position:sticky;top:0;z-index:2;display:flex;flex-wrap:wrap;gap:6px;padding:12px 0;background:#fff;border-bottom:1px solid var(--g-line);margin-bottom:4px}
        .sg-nav a{font-size:12px;font-weight:600;padding:4px 10px;border-radius:20px;background:var(--g-soft);color:var(--g-pri);text-decoration:none;border:1px solid var(--g-line)}
        .sg-nav a:hover{background:var(--g-pri);color:#fff}
        .sg section{padding-top:16px;scroll-margin-top:56px}
        .sg h6{display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap;font-weight:700;color:var(--g-pri);border-left:4px solid var(--g-acc);padding-left:8px;margin-bottom:10px}
        .sg ul{padding-left:18px;margin-bottom:0}
        .sg li{margin-bottom:6px;line-height:1.5}
        .sg-t{margin-bottom:0}
        .sg-t td{vertical-align:top!important;padding:7px 8px;line-height:1.5;font-size:13px!important}
        .sg-t td:first-child{width:210px;white-space:nowrap;color:var(--g-pri)}
        .sg-flow{display:flex;align-items:stretch;gap:8px;margin-bottom:12px;flex-wrap:wrap}
        .sg-flow>div{flex:1;min-width:130px;background:var(--g-soft);border:1px solid var(--g-line);border-radius:.5rem;padding:8px 12px}
        .sg-flow>div.ok{background:#e8f7ee;border-color:#b9e8cf}
        .sg-flow b{display:block;color:var(--g-pri)} .sg-flow span{font-size:12px;color:var(--g-muted)}
        .sg-flow>i{align-self:center;font-style:normal;font-weight:700;color:var(--g-acc)}
        .sg-faq dt{color:var(--g-pri);margin-top:8px} .sg-faq dd{margin:2px 0 0}
        .sg-show{border:1px solid var(--g-pri);background:#fff;color:var(--g-pri);font-size:11.5px;font-weight:600;border-radius:20px;padding:2px 10px}
        .sg-show:hover,.sg-show.pri{background:var(--g-pri);color:#fff}
        .sg-show.pri:hover{filter:brightness(.92)}
        .sg-note{font-size:12.5px;color:var(--g-muted);margin-bottom:10px}
        .sg-dot{display:inline-block;width:9px;height:9px;border-radius:50%;margin-right:6px;vertical-align:0}

        /* Panduan: peta halaman */
        .sg-map{background:var(--g-soft);border:1px solid var(--g-line);border-radius:.6rem;padding:14px;max-width:760px}
        .sg-map-z{position:relative;cursor:pointer;transition:box-shadow .15s;border-radius:.35rem}
        .sg-map-z:hover{box-shadow:0 0 0 2px var(--g-acc);z-index:6}
        .sg-map-n{position:absolute;top:-9px;left:-9px;z-index:2;width:20px;height:20px;border-radius:50%;background:var(--g-acc);color:#fff!important;font-size:11px;font-weight:700;display:grid;place-items:center;text-decoration:none;box-shadow:0 0 0 2px #fff}
        .sg-map-n:hover,.sg-map-n:focus-visible{background:var(--g-pri);z-index:5}
        .sg-map-tip{position:absolute;top:calc(100% + 8px);left:-4px;width:230px;padding:8px 10px;border-radius:.45rem;background:#18212f;color:#fff;font-size:12px;font-weight:400;line-height:1.45;text-align:left;box-shadow:0 6px 18px rgba(0,0,0,.25);opacity:0;visibility:hidden;transform:translateY(-4px);transition:opacity .15s,transform .15s,visibility .15s;pointer-events:none}
        .sg-map-tip:before{content:"";position:absolute;top:-5px;left:10px;width:10px;height:10px;background:#18212f;transform:rotate(45deg)}
        .sg-map-n.tip-l .sg-map-tip{left:auto;right:-4px}
        .sg-map-n.tip-l .sg-map-tip:before{left:auto;right:10px}
        .sg-map-tip b{display:block;font-size:12.5px;margin-bottom:2px;color:#99f6e4}
        .sg-map-tip small{display:block;margin-top:5px;font-size:10.5px;color:rgba(255,255,255,.65)}
        .sg-map-n:hover .sg-map-tip,.sg-map-n:focus-visible .sg-map-tip{opacity:1;visibility:visible;transform:none}
        .sg-map-head{display:flex;justify-content:space-between;align-items:center;gap:10px;padding:8px 10px;border-radius:.45rem;background:linear-gradient(120deg,#0b1f3f,#1d4ed8 60%,#0ea5a4)}
        .sg-map-head b{font-size:12px;color:#fff}
        .sg-map-head .sg-map-z i{display:block;width:54px;height:14px;border-radius:4px;background:#ffffff44}
        .sg-map-body{display:flex;flex-direction:column;gap:10px;margin-top:10px}
        .sg-map-up{height:20px;background:#fff;border:1px solid var(--g-line)}
        .sg-map-al{height:18px;background:#ffe4e8;border:1px solid #fecdd3}
        .sg-map-kp{display:grid;grid-template-columns:repeat(5,1fr);gap:5px;padding:2px}
        .sg-map-kp i{height:26px;border-radius:.3rem;background:#fff;border:1px solid var(--g-line);border-top:3px solid var(--c)}
        .sg-map-card{background:#fff;border:1px solid var(--g-line);border-radius:.45rem;padding:10px;display:flex;flex-direction:column;gap:10px}
        .sg-map-frow{display:flex;gap:8px;align-items:center}
        .sg-map-fl{display:flex;gap:5px;flex:1;padding:2px}
        .sg-map-fl i{flex:1;height:14px;border-radius:.25rem;border:1px solid #c5ccd6}
        .sg-map-ac{display:flex;gap:4px;padding:2px}
        .sg-map-ac i{width:18px;height:14px;border-radius:.25rem}
        .sg-map-tbw{position:relative}
        .sg-map-tb{display:grid;grid-template-columns:repeat(12,1fr) 22px;gap:3px;padding:2px}
        .sg-map-tb i{height:11px;border-radius:.15rem;background:var(--g-soft);border:1px solid var(--g-line)}
        .sg-map-tb i.th{background:#dbe5ff;border-color:#c3d3fb}
        .sg-map-tb i.q{background:#efe7ff;border-color:#ddd0fb}
        .sg-map-tb i.ab{background:#fff4e0;border-color:#f5d39b}
        .sg-map-abz{position:absolute;right:0;top:16px;bottom:0;width:26px}

        /* Panduan: sorot elemen & tur */
        .sg-spot{position:fixed;z-index:2000;border-radius:.6rem;box-shadow:0 0 0 9999px rgba(11,31,63,.6);outline:3px solid #0ea5a4;outline-offset:3px;pointer-events:none;animation:sgSpot 1.2s ease-in-out infinite}
        @keyframes sgSpot{50%{outline-offset:8px;outline-color:rgba(14,165,164,.6)}}
        .sg-spot-cv{position:fixed;inset:0;z-index:1999;cursor:pointer}
        .sg-spot-l{position:fixed;z-index:2001;background:#fff;color:var(--g-ink);font-family:"Plus Jakarta Sans",Arial,sans-serif;font-size:13px;padding:10px 12px;border-radius:.6rem;box-shadow:0 6px 18px rgba(0,0,0,.25);width:340px;max-width:calc(100vw - 16px)}
        .sg-spot-l .st{display:flex;justify-content:space-between;align-items:center;font-size:10.5px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--g-pri);margin-bottom:4px}
        .sg-spot-l .dots{display:flex;gap:4px}
        .sg-spot-l .dots i{width:7px;height:7px;border-radius:50%;background:#e2e6ec}
        .sg-spot-l .dots i.done{background:#a5c0f7}
        .sg-spot-l .dots i.on{background:var(--g-acc);width:16px;border-radius:4px}
        .sg-spot-l>b{display:block;font-weight:700}
        .sg-spot-l>span.d{display:block;color:#3d4450;font-size:12.5px;line-height:1.5;margin-top:3px}
        .sg-spot-l>span.d b{color:var(--g-ink)}
        .sg-spot-l .nav{display:flex;justify-content:space-between;gap:8px;margin-top:10px}
        .sg-spot-l .nav button{border:1px solid #c5ccd6;background:#fff;color:var(--g-ink);font-size:12.5px;font-weight:600;border-radius:.4rem;padding:4px 12px}
        .sg-spot-l .nav button:disabled{opacity:.4}
        .sg-spot-l .nav button.pri{background:var(--g-pri);border-color:var(--g-pri);color:#fff}
        .sg-spot-l .nav button:not(:disabled):hover{filter:brightness(.95)}
        .sg-spot-l small{display:block;color:var(--g-muted);font-size:11.5px;margin-top:6px;padding-top:5px;border-top:1px solid #e2e6ec}
        .sg-spot-l kbd{font-size:10px;padding:0 4px}
        @media(max-width:576px){.sg-t td:first-child{width:auto;white-space:normal}.sg-flow>i{display:none}}

    </style>
@endsection

@section('content')
<div id="mso">
    <div class="wrap">
        <div class="ph">
            <div>
                <div class="crumb">Master &nbsp;›&nbsp; Master SO PPIC</div>
                <h1>Master SO PPIC</h1>
                <p>Daftar order (SO) dari PPIC beserta progres qty dari PO sampai Packing Out.</p>
            </div>
            <button type="button" class="ph-guide" id="soGuideBtn"><i class="fas fa-book-open"></i> Panduan</button>
        </div>

        <!-- UPLOAD (collapsible) -->
        <div class="mcard upload-wrap" id="upWrap" style="margin-bottom:16px">
            <div class="upload" onclick="toggleUpload()">
                <div class="l">
                    <div class="ico" style="background:var(--teal-lt);color:var(--teal)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 16V4M7 9l5-5 5 5M4 20h16"/></svg></div>
                    <div><b>Upload Master SO PPIC</b><span>Klik untuk upload file Excel, cek hasilnya, lalu simpan</span></div>
                </div>
            <span id="upTog" style="font-size:22px;color:var(--muted)">+</span>
            </div>
            <div class="upbody">
                <div class="steps">
                    <div class="step done" id="st1"><i>✓</i>Pilih file Excel</div><div class="step-sep"></div>
                    <div class="step on" id="st2"><i>2</i>Cek preview</div><div class="step-sep"></div>
                    <div class="step"><i>3</i>Simpan</div>
                </div>
                <div class="up-top">
                    <label class="drop empty">
                        <input type="file" id="pvFile" accept=".xlsx,.xls,.csv" hidden>
                        <div class="ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><path d="M14 3v6h6M9 13l6 6M15 13l-6 6"/></svg></div>
                        <div>
                            <div class="fn" id="pvFn">Belum ada file dipilih</div>
                            <div class="fm" id="pvFm">Tarik file Excel ke sini atau klik untuk memilih file (.xlsx / .xls / .csv)</div>
                        </div>
                        <span class="mbtn chg" id="pvChg">Pilih File</span>
                    </label>
                    <div class="helpers">
                        <a class="helper" href="{{ route('contoh_upload_ppic_so') }}">
                        <div class="ico" style="background:var(--amber-lt);color:var(--amber)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4v12M7 11l5 5 5-5M4 20h16"/></svg></div>
                        <div><b>Contoh Upload</b><span>Download format Excel yang benar</span></div><span class="go">›</span></a>
                        <a class="helper" href="#" onclick="exportMasterSB();return false;">
                        <div class="ico" style="background:var(--green-lt);color:var(--green)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v14c0 1.7 3.6 3 8 3s8-1.3 8-3V5M4 12c0 1.7 3.6 3 8 3s8-1.3 8-3"/></svg></div>
                        <div><b>Master Data SO SB</b><span>Download data SO SB untuk dicocokkan</span></div><span class="go">›</span></a>
                    </div>
                </div>

                <div class="pv-empty" id="pvEmpty">
                    <div class="pe-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 16V4M7 9l5-5 5 5M4 20h16"/></svg></div>
                    <b>Belum ada data untuk dicek</b>
                    <span>Pilih atau tarik file Excel ke kotak di atas untuk melihat preview.</span>
                </div>
                
                <div id="pvArea" style="display:none">
                    <div class="pv-h">
                        <div><h3>Preview Data</h3><p>Klik kotak di bawah untuk melihat baris per status. Simpan hanya bisa jika semua baris berstatus <b style="color:var(--green)">Ok</b>.</p></div>
                        <div class="search"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg><input id="pvq" placeholder="Cari di preview..."></div>
                    </div>
                    <div class="sum" id="pvsum"></div>

                    <div class="pv-tw">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID SO Det</th>
                                    <th>WS</th>
                                    <th>Style</th>
                                    <th>Desc</th>
                                    <th>PO</th>
                                    <th>Color</th>
                                    <th>Size</th>
                                    <th>Dest</th>
                                    <th>Barcode</th>
                                    <th class="q">Total</th>
                                    <th>Buyer</th>
                                    <th>Tgl Shipment</th>
                                    <th>Status</th>
                                    <th>Keterangan</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="pvtb"></tbody>
                            <tfoot>
                                <tr id="pvtf"></tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="up-foot">
                        <button class="mbtn ghost-amber" onclick="pvUndo()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7v6h6"/><path d="M3 13a9 9 0 1 0 3-7L3 9"/></svg>Undo / Batal Upload</button>
                        <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
                            <span class="msg" id="pvmsg"></span>
                            <button class="mbtn save" onclick="pvSimpan()" disabled><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12l5 5L20 7"/></svg>Simpan</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- NOTIF PO MEPET -->
        <button class="alert2 opt-a" id="alertBox" onclick="openDrawer('mepet')">
            <span class="ai"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4M12 17h.01"/><path d="M10.3 3.9L1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg></span>
            <span class="at"><b id="aN">0</b> PO mendekati tanggal shipment</span>
            <span class="asep"></span>
            <span class="at">Kurang <b id="aQ">0</b> pcs</span>
            <span class="aload">Memuat data PO mendekati tanggal shipment...</span>
            <span class="ticker"><span class="tk" id="aTk"></span></span>
            <span class="ago">Lihat detail ›</span>
        </button>

        <!-- KPI -->
        <div class="kpis" id="kpis"></div>

        <!-- LIST -->
        <div class="mcard">
            <div style="display:flex;align-items:center;gap:12px;padding:16px 18px;border-bottom:1px solid var(--line)">
                <div class="ico" style="background:#e8edf6;color:var(--navy)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg></div>
                <div><b style="font-size:16px">List Master</b><div style="font-size:12.5px;color:var(--muted)">Pilih filter lalu klik Cari</div></div>
            </div>

            <div class="filters">
                <div class="f"><label>Status Order</label>
                    <select id="fstat" class="select2bs4"><option value="">All</option><option value="open">Open</option><option value="close">Close</option></select>
                </div>
                <div class="f"><label>Tgl Shipment Awal</label><input type="date" id="fd1" value="{{ date('Y-m-d') }}"></div>
                <div class="f"><label>Tgl Shipment Akhir</label><input type="date" id="fd2" value="{{ date('Y-m-d') }}"></div>
                <div class="f"><label>WS</label><select id="fws" class="select2bs4"></select></div>
                <div class="f"><label>Style</label><select id="fst" class="select2bs4"></select></div>
                <div class="f"><label>No. PO</label><select id="fpo" class="select2bs4"></select></div>
                <div class="f"><label>&nbsp;</label>
                    <div class="acts">
                    <button class="mbtn pri" title="Cari" onclick="cari()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>Cari</button>
                    <button class="mbtn" title="Reset Filter" onclick="resetFilter()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/></svg></button>
                    <button class="mbtn green" title="Export Excel" onclick="exportExcel()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><path d="M14 3v6h6M9 13l6 6M15 13l-6 6"/></svg></button>
                    <button class="mbtn amber" title="Edit Multiple" data-bs-toggle="modal" data-bs-target="#modalEditMassal"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg></button>
                    </div>
                </div>
            </div>

            <div class="chips">
                <span class="muted">Filter aktif:</span>
                <span id="chipL" style="display:contents"></span>
            </div>

            <div class="tb">
                <div class="l">Tampilkan
                    <select id="per"><option>10</option><option>25</option><option>50</option></select> data
                </div>
                <div class="legend lg2">
                    <b>Qty:</b>
                    <span><i class="mk" style="color:var(--green)">+</i>hijau = switching masuk</span>
                    <span><i class="mk" style="color:var(--rose)">−</i>merah = switching keluar</span>
                    <span><i class="mk" style="color:#c0c7d2">0</i>abu = 0</span>
                    <span class="lsep"></span>
                    <b>Tgl Shipment:</b>
                    <span><i style="background:var(--rose);border-radius:50%"></i>≤ 3 hari / lewat</span>
                    <span><i style="background:var(--amber);border-radius:50%"></i>4–7 hari</span>
                    <span><i style="background:var(--green);border-radius:50%"></i>&gt; 7 hari</span>
                    <span><i style="background:#c0c7d2;border-radius:50%"></i>Order Close</span>
                </div>
                <div class="search"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg><input id="q" placeholder="Cari barcode, PO, color, size..."></div>
            </div>

            <div class="tw">
                <table>
                    <thead>
                        <tr class="g">
                            <th class="stk stk1 grp-info g-ord"></th>
                            <th class="stk stk2 grp-info g-ord">Order</th>
                            <th class="grp-info g-det" colspan="5">Detail Order</th>
                            <th class="grp-qty qfirst qlast" colspan="6">Quantity (pcs)</th>
                            <th colspan="4" class="grp-info g-prg">Progres &amp; Upload</th>
                        </tr>
                        <tr class="h">
                            <th class="stk stk1">ID</th>
                            <th class="stk stk2">Buyer</th>
                            <th>Tgl Shipment</th><th>WS<small>Style</small></th><th>No. PO<small>Dest</small></th><th>Color<small>Desc</small></th><th>Size<small>Barcode</small></th>
                            <th class="q qfirst">Qty<small>PO</small></th><th class="q">Packing<small>Line</small></th><th class="q">Transfer<small>Garment</small></th><th class="q">Packing<small>In</small></th><th class="q">Switching<small>Out / In</small></th><th class="q qlast">Packing<small>Out</small></th>
                            <th>Progres<small>Out</small></th><th>User<small>Tgl Upload</small></th><th>Status<small>Order</small></th><th></th>
                        </tr>
                        <tr class="s" id="srow"></tr>
                    </thead>
                    <tbody id="tb"></tbody>
                    <tfoot><tr id="tf"></tr></tfoot>
                </table>
            </div>

            <div class="pg">
                <div class="info" id="info"></div>
                <div class="pages" id="pages"></div>
            </div>
        </div>
    </div>

    <div class="ov" id="ov" onclick="closeDrawer()"></div>
    <aside class="drw" id="drw">
        <div class="drw-h"><div><h3 id="dT"></h3><p id="dP"></p></div><button class="x" onclick="closeDrawer()">×</button></div>
        <div class="drw-s" id="dS"></div>
        <div class="drw-t" id="dTab"></div>
        <div class="drw-f">
            <input id="dQ" placeholder="Cari PO, WS, style, color, dest...">
            <select id="dRange">
                <option value="">Semua tgl shipment</option>
                <option value="late">Sudah lewat</option>
                <option value="d3">0 – 3 hari lagi</option>
                <option value="d7">4 – 7 hari lagi</option>
                <option value="far">&gt; 7 hari lagi</option>
            </select>
        </div>
        <div class="drw-b" id="dB"></div>
        <div class="drw-pg" id="dPg"></div>
    </aside>

    <div class="cfm-ov" id="cfm" onclick="if(event.target===this)cfmClose()">
        <div class="cfm">
            <div class="cfm-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7v6h6"/><path d="M3 13a9 9 0 1 0 3-7L3 9"/></svg></div>
            <h3>Undo / Batal Upload</h3>
            <p id="cfmP"></p>
            <div class="cfm-b" id="cfmB"></div>
        </div>
    </div>
</div>

{{-- Modal Edit (sama seperti Master SO lama) --}}
<div class="modal fade me-modal" id="exampleModalEdit" tabindex="-1" role="dialog" aria-labelledby="exampleModalEditLabel"
    aria-hidden="true">
    <form action="{{ route('update_data_ppic_master_so') }}" method="post" onsubmit="submitEdit(this, event)"
        name='form_edit' id='form_edit'>
        @method('POST')
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="me-h">
                    <div class="me-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg></div>
                    <div>
                        <h3>Update Data</h3>
                        <p id="me_info">Ubah tanggal shipment atau qty PO</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type='hidden' id='txtid_c' name='txtid_c' autocomplete="off" readonly>
                    <div class="me-sec">
                        <b>Tgl Shipment</b>
                        <div class="me-row">
                            <div>
                                <label for="txted_tgl_shipment">Sebelumnya</label>
                                <input type='text' class='form-control' id='txted_tgl_shipment'
                                    name='txted_tgl_shipment' autocomplete="off" readonly>
                            </div>
                            <span class="arr">→</span>
                            <div>
                                <label for="txted_tgl_shipment_skrg">Baru</label>
                                <input type='date' class='form-control' id='txted_tgl_shipment_skrg'
                                    name='txted_tgl_shipment_skrg' autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="me-sec">
                        <b>Qty PO</b>
                        <div class="me-row">
                            <div>
                                <label for="txted_qty_po">Sebelumnya</label>
                                <input type='text' class='form-control' id='txted_qty_po'
                                    name='txted_qty_po' autocomplete="off" readonly>
                            </div>
                            <span class="arr">→</span>
                            <div>
                                <label for="txted_qty_po_skrg">Baru</label>
                                <input type='text' class='form-control' id='txted_qty_po_skrg'
                                    name='txted_qty_po_skrg' autocomplete="off" inputmode="numeric">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn me-save"><i class="fas fa-check"></i> Simpan</button>
                </div>
            </div>
        </div>
    </form>
</div>
{{-- Modal Edit Multiple (fungsi sama seperti Master SO lama) --}}
<div class="modal fade me-modal mm-modal" id="modalEditMassal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="me-h">
                <div class="me-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg></div>
                <div>
                    <h3>Edit Multiple</h3>
                    <p>Ubah atau hapus data Master SO per WS &amp; No. PO</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="mm-tabs">
                <button type="button" class="on" data-tab="edit" onclick="mmTab('edit')"><i class="fas fa-edit"></i> Edit</button>
                <button type="button" data-tab="hapus" onclick="mmTab('hapus')"><i class="fas fa-trash-alt"></i> Hapus</button>
            </div>
            <div class="modal-body">
                {{-- EDIT --}}
                <div id="mm_edit">
                    <form id="form_e" name="form_e" method="post" action="{{ route('edit_multiple_ppic_master_so') }}" onsubmit="mmSubmitEdit(this, event)">
                        <div class="mm-filter">
                            <div>
                                <label for="cbows_edit_tgl">WS</label>
                                <select class="mm-s2" id="cbows_edit_tgl" name="cbows_edit_tgl" style="width:100%">
                                    <option value="">Pilih WS</option>
                                    @foreach ($data_ws as $dataws)
                                        <option value="{{ $dataws->isi }}">{{ $dataws->tampil }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="cbopo_edit_tgl">No. PO</label>
                                <select class="mm-s2" id="cbopo_edit_tgl" name="cbopo_edit_tgl" style="width:100%">
                                    <option value="">Pilih No PO</option>
                                </select>
                            </div>
                            <div>
                                <label for="tgl_ubah">Update Tanggal Shipment</label>
                                <input type="date" class="form-control" id="tgl_ubah" name="tgl_ubah" value="{{ date('Y-m-d') }}">
                            </div>
                            <div>
                                <button type="button" class="btn me-outline" onclick="update_tgl()" title="Ubah tgl shipment semua baris di PO ini ke Tanggal Ubah"><i class="fas fa-calendar-check"></i> Update Tanggal</button>
                            </div>
                        </div>
                        <div class="mm-bar">
                            <span class="cnt" id="mm_edit_cnt"></span>
                            <input type="text" id="mm_edit_q" placeholder="Cari di tabel..." oninput="mmRender('edit')">
                        </div>
                        <div class="mm-tw">
                            <table class="mm-t">
                                <thead>
                                    <tr>
                                        <th>ID SO Det</th><th>Buyer</th><th>Tgl Shipment</th><th>WS</th><th>Style</th><th>Barcode</th>
                                        <th class="q">Qty PO</th><th>No. PO</th><th>Reff</th><th>Dest</th><th>Desc</th><th>Color</th><th>Size</th>
                                        <th class="q">Qty Trf Garment</th><th class="q">Qty Packing Out</th><th>User</th><th>Tgl Upload</th>
                                    </tr>
                                </thead>
                                <tbody id="mm_edit_tb"></tbody>
                            </table>
                        </div>
                        <div class="mm-foot">
                            <button type="submit" class="btn me-save"><i class="fas fa-check"></i> Update</button>
                        </div>
                    </form>
                </div>

                {{-- HAPUS --}}
                <div id="mm_hapus" style="display:none">
                    <form id="form_d" name="form_d" method="post" action="{{ route('hapus_multiple_ppic_master_so') }}" onsubmit="mmSubmitHapus(this, event)">
                        <div class="mm-filter two">
                            <div>
                                <label for="cbows_hapus">WS</label>
                                <select class="mm-s2" id="cbows_hapus" name="cbows_hapus" style="width:100%">
                                    <option value="">Pilih WS</option>
                                    @foreach ($data_ws as $dataws)
                                        <option value="{{ $dataws->isi }}">{{ $dataws->tampil }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="cbopo_hapus">No. PO</label>
                                <select class="mm-s2" id="cbopo_hapus" name="cbopo_hapus" style="width:100%">
                                    <option value="">Pilih No PO</option>
                                </select>
                            </div>
                        </div>
                        <div class="mm-bar">
                            <span class="cnt" id="mm_hapus_cnt"></span>
                            <input type="text" id="mm_hapus_q" placeholder="Cari di tabel..." oninput="mmRender('hapus')">
                        </div>
                        <div class="mm-tw">
                            <table class="mm-t">
                                <thead>
                                    <tr>
                                        <th class="c"><input type="checkbox" class="mm-cb" id="mm_all" onclick="mmAll(this)" title="Pilih semua"></th>
                                        <th>Buyer</th><th>Tgl Shipment</th><th>WS</th><th>Style</th><th>Barcode</th><th class="q">Qty PO</th>
                                        <th>No. PO</th><th>Reff</th><th>Dest</th><th>Desc</th><th>Color</th><th>Size</th><th class="q">Qty Trf</th>
                                        <th>User</th><th>Tgl Upload</th>
                                    </tr>
                                </thead>
                                <tbody id="mm_hapus_tb"></tbody>
                            </table>
                        </div>
                        <div class="mm-foot">
                            <button type="submit" class="btn me-del"><i class="fas fa-trash"></i> Hapus</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

{{-- Panduan halaman (pola sama seperti Packing List) --}}
<div class="modal fade" id="soGuide" tabindex="-1" aria-labelledby="soGuideTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header sg-head">
                <div>
                    <small class="text-uppercase fw-bold" style="letter-spacing:.6px;opacity:.75">Panduan</small>
                    <h5 class="modal-title fw-bold" id="soGuideTitle">Master SO PPIC</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body sg">
                <nav class="sg-nav">
                    <a href="#sg-peta">Peta halaman</a><a href="#sg-alur">Alur singkat</a><a href="#sg-upload">Upload</a><a href="#sg-notif">PO mendekati shipment</a>
                    <a href="#sg-kpi">Kartu KPI</a><a href="#sg-filter">Filter &amp; Cari</a><a href="#sg-tombol">Export &amp; Edit Multiple</a><a href="#sg-tabel">Tabel</a>
                    <a href="#sg-edit">Edit per baris</a><a href="#sg-tips">Tips &amp; FAQ</a>
                </nav>

                <section id="sg-peta">
                    <h6><span>Peta halaman</span> <button type="button" class="sg-show pri" data-tour="0"><i class="fa fa-play"></i> Mulai tur</button></h6>
                    <p class="sg-note">Klik <b>nomor</b> untuk membaca penjelasannya, atau klik <b>kotaknya</b> untuk melihat langsung di halaman.</p>
                    <div class="sg-map">
                        <div class="sg-map-head">
                            <b>Master SO PPIC</b>
                            <div class="sg-map-z" data-spot="#soGuideBtn"><a href="#sg-peta" class="sg-map-n tip-l" aria-label="Tombol Panduan">1<span class="sg-map-tip" role="tooltip"><b>Panduan</b>Membuka halaman bantuan ini kapan saja.<small>Klik kotak untuk melihat di halaman</small></span></a><i></i></div>
                        </div>
                        <div class="sg-map-body">
                            <div class="sg-map-z sg-map-up" data-spot="#upWrap"><a href="#sg-upload" class="sg-map-n" aria-label="Upload">2<span class="sg-map-tip" role="tooltip"><b>Upload Master SO PPIC</b>Upload file Excel, cek preview, lalu simpan.<small>Klik nomor untuk penjelasan lengkap</small></span></a></div>
                            <div class="sg-map-z sg-map-al" data-spot="#alertBox"><a href="#sg-notif" class="sg-map-n" aria-label="PO mendekati shipment">3<span class="sg-map-tip" role="tooltip"><b>PO mendekati tanggal shipment</b>PO Open yang shipment-nya 7 hari lagi atau kurang dan Packing Out belum lengkap.<small>Klik nomor untuk penjelasan lengkap</small></span></a></div>
                            <div class="sg-map-z sg-map-kp" data-spot="#kpis"><a href="#sg-kpi" class="sg-map-n" aria-label="Kartu KPI">4<span class="sg-map-tip" role="tooltip"><b>Kartu KPI</b>Total qty PO Open yang belum selesai, per tahap proses.<small>Klik nomor untuk penjelasan lengkap</small></span></a>
                                <i style="--c:#0b1f3f"></i><i style="--c:#0891b2"></i><i style="--c:#7c3aed"></i><i style="--c:#0284c7"></i><i style="--c:#16a34a"></i>
                            </div>
                            <div class="sg-map-card">
                                <div class="sg-map-frow">
                                    <div class="sg-map-z sg-map-fl" data-spot="#mso .filters"><a href="#sg-filter" class="sg-map-n" aria-label="Filter">5<span class="sg-map-tip" role="tooltip"><b>Filter &amp; Cari</b>Status order, tgl shipment, WS, style, dan No. PO.<small>Klik nomor untuk penjelasan lengkap</small></span></a><i></i><i></i><i></i><i></i><i></i><i></i></div>
                                    <div class="sg-map-z sg-map-ac" data-spot="#mso .f .acts"><a href="#sg-tombol" class="sg-map-n tip-l" aria-label="Tombol">6<span class="sg-map-tip" role="tooltip"><b>Cari, Export &amp; Edit Multiple</b>Tampilkan data, unduh Excel, atau ubah / hapus banyak baris sekaligus.<small>Klik nomor untuk penjelasan lengkap</small></span></a><i style="background:#1d4ed8"></i><i style="background:#16a34a"></i><i style="background:#d97706"></i></div>
                                </div>
                                <div class="sg-map-tbw">
                                    <div class="sg-map-z sg-map-tb" data-spot="#mso .tw"><a href="#sg-tabel" class="sg-map-n" aria-label="Tabel">7<span class="sg-map-tip" role="tooltip"><b>Tabel List Master</b>Satu baris per size / barcode, dengan qty tiap tahap dan progres.<small>Klik nomor untuk penjelasan lengkap</small></span></a>
                                        <i class="th"></i><i class="th"></i><i class="th"></i><i class="th"></i><i class="th"></i><i class="th"></i><i class="th q"></i><i class="th q"></i><i class="th q"></i><i class="th q"></i><i class="th q"></i><i class="th q"></i><i class="th"></i>
                                        <i></i><i></i><i></i><i></i><i></i><i></i><i class="q"></i><i class="q"></i><i class="q"></i><i class="q"></i><i class="q"></i><i class="q"></i><i class="ab"></i>
                                        <i></i><i></i><i></i><i></i><i></i><i></i><i class="q"></i><i class="q"></i><i class="q"></i><i class="q"></i><i class="q"></i><i class="q"></i><i class="ab"></i>
                                    </div>
                                    <div class="sg-map-z sg-map-abz" data-spot="#tb tr:first-child .ab"><a href="#sg-edit" class="sg-map-n tip-l" aria-label="Edit per baris">8<span class="sg-map-tip" role="tooltip"><b>Edit per baris</b>Ubah tgl shipment atau qty PO satu baris.<small>Klik nomor untuk penjelasan lengkap</small></span></a></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="sg-alur">
                    <h6>Alur singkat</h6>
                    <div class="sg-flow">
                        <div><b>Upload Excel</b><span>pakai format Contoh Upload</span></div><i>→</i>
                        <div><b>Cek preview</b><span>semua baris harus Ok</span></div><i>→</i>
                        <div><b>Simpan</b><span>masuk ke List Master</span></div><i>→</i>
                        <div class="ok"><b>Pantau progres</b><span>sampai Packing Out</span></div>
                    </div>
                    <ul>
                        <li>Kalau ada perubahan tgl shipment / qty PO, pakai tombol <b>Edit</b> di baris tabel atau <b>Edit Multiple</b> untuk satu PO sekaligus.</li>
                        <li>PO yang shipment-nya sudah dekat tapi belum lengkap muncul di kotak merah <b>PO mendekati tanggal shipment</b>.</li>
                    </ul>
                </section>

                <section id="sg-upload">
                    <h6><span>Upload Master SO PPIC</span> <button type="button" class="sg-show" data-spot="#upWrap"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                    <ul>
                        <li>Klik kotak <b>Upload Master SO PPIC</b> untuk membukanya, lalu tarik file Excel ke kotak atau klik <b>Pilih File</b> (.xlsx / .xls / .csv).</li>
                        <li><b>Contoh Upload</b> untuk mengunduh format Excel yang benar. <b>Master Data SO SB</b> untuk mengunduh data SO SB sebagai acuan WS, color, style, dan dest.</li>
                        <li>Upload berikutnya ditambahkan ke preview. <b>Undo / Batal Upload</b> mengosongkan seluruh preview (belum ada yang tersimpan).</li>
                    </ul>
                    <table class="table table-sm sg-t mt-2">
                        <tr><td><span class="sg-dot" style="background:#16a34a"></span>Ok</td><td>Data aman dan lengkap.</td></tr>
                        <tr><td><span class="sg-dot" style="background:#d97706"></span>Data Double</td><td>Ada baris yang sama persis di file yang diupload.</td></tr>
                        <tr><td><span class="sg-dot" style="background:#e11d48"></span>Tidak Valid</td><td>WS, color, style, atau dest tidak cocok dengan master SB, atau tgl shipment kosong.</td></tr>
                        <tr><td><span class="sg-dot" style="background:#7c3aed"></span>Sudah Terinput</td><td>Data sudah ada di sistem.</td></tr>
                    </table>
                    <ul class="mt-2">
                        <li>Tombol <b>Simpan</b> baru aktif kalau <b>semua</b> baris berstatus Ok. Hapus baris bermasalah dengan tombol tempat sampah, atau perbaiki file lalu upload ulang.</li>
                    </ul>
                </section>

                <section id="sg-notif">
                    <h6><span>PO mendekati tanggal shipment</span> <button type="button" class="sg-show" data-spot="#alertBox"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                    <ul>
                        <li>Menampilkan PO berstatus <b>Open</b> dengan tgl shipment <b>7 hari lagi atau kurang</b> (termasuk yang sudah lewat) dan <b>Packing Out</b> masih kurang dari Qty PO.</li>
                        <li>Tidak ikut filter List Master, selalu dari semua data.</li>
                        <li>Klik kotaknya untuk melihat rincian per PO. Di sana bisa dicari, difilter per rentang tgl shipment, dan klik satu PO untuk melihat kekurangan per size.</li>
                        <li>Kotak ini tidak muncul kalau tidak ada PO yang memenuhi syarat.</li>
                    </ul>
                </section>

                <section id="sg-kpi">
                    <h6><span>Kartu KPI</span> <button type="button" class="sg-show" data-spot="#kpis"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                    <ul>
                        <li>Dihitung dari semua baris <b>Open</b> yang <b>Packing Out</b>-nya masih kurang dari Qty PO, tanpa batas tanggal dan tidak ikut filter List Master.</li>
                        <li><b>Balance</b> = Qty PO dikurangi qty pada kartu tersebut.</li>
                        <li>Klik kartu untuk melihat rincian per PO. Tab <b>Yang masih kurang</b> hanya PO yang qty tahap itu belum memenuhi Qty PO, tab <b>Semua PO</b> menampilkan semuanya.</li>
                    </ul>
                </section>

                <section id="sg-filter">
                    <h6><span>Filter &amp; Cari</span> <button type="button" class="sg-show" data-spot="#mso .filters"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                    <table class="table table-sm sg-t">
                        <tr><td><b>Status Order</b></td><td>All, Open, atau Close.</td></tr>
                        <tr><td><b>Tgl Shipment Awal / Akhir</b></td><td>Awalnya terisi hari ini. Pilihan WS, Style, dan No. PO ikut menyesuaikan rentang tanggal ini.</td></tr>
                        <tr><td><b>WS / Style / No. PO</b></td><td>Saling terhubung: memilih salah satu akan menyesuaikan pilihan yang lain dan tgl shipment-nya.</td></tr>
                        <tr><td><b>Cari</b></td><td>Menampilkan data sesuai filter. Isi kedua tanggal, atau pilih WS / Style kalau tanggal dikosongkan.</td></tr>
                        <tr><td><b>Reset Filter</b></td><td>Tombol putar (sebelah Cari): mengosongkan semua filter (Status All, Tgl Shipment, WS / Style / No. PO) dan pencarian.</td></tr>
                        <tr><td><b>Filter aktif</b></td><td>Ditampilkan sebagai label di bawah filter. Klik × untuk menghapus satu filter, atau <b>Hapus semua</b>.</td></tr>
                    </table>
                </section>

                <section id="sg-tombol">
                    <h6><span>Export Excel &amp; Edit Multiple</span> <button type="button" class="sg-show" data-spot="#mso .f .acts"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                    <table class="table table-sm sg-t">
                        <tr><td><b style="color:#16a34a">Export Excel</b></td><td>Mengunduh data List Master sesuai filter yang sedang dipakai.</td></tr>
                        <tr><td><b style="color:#d97706">Edit Multiple · Edit</b></td><td>Pilih <b>WS</b> (hanya order yang masih Open) dan <b>No. PO</b>, lalu:
                            <ul class="mt-1">
                                <li><b>Update Tanggal</b>: mengubah tgl shipment <b>semua baris</b> PO tersebut ke tanggal yang dipilih.</li>
                                <li><b>Update</b> (bawah): menyimpan perubahan tgl shipment, barcode, qty PO, dan No. PO per baris di tabel. Barcode terkunci kalau sudah ada Packing Out; No. PO terkunci kalau sudah ada Transfer Garment / Packing List.</li>
                            </ul></td></tr>
                        <tr><td><b style="color:#e11d48">Edit Multiple · Hapus</b></td><td>Centang baris yang ingin dihapus lalu klik <b>Hapus</b>. Baris bergembok tidak bisa dihapus karena sudah ada Transfer Garment / Packing List.</td></tr>
                    </table>
                    <ul class="mt-2">
                        <li>User <b>admin 01</b> dan <b>it</b> bisa memilih dan mengubah data semua user. User lain hanya data yang diupload sendiri.</li>
                        <li>Setiap perubahan tgl shipment / qty PO tercatat: nilai lama, user, dan waktu update.</li>
                    </ul>
                </section>

                <section id="sg-tabel">
                    <h6><span>Tabel List Master</span> <button type="button" class="sg-show" data-spot="#mso .tw"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                    <table class="table table-sm sg-t">
                        <tr><td><b>Qty PO</b></td><td>Qty dari upload Master SO PPIC.</td></tr>
                        <tr><td><b>Packing Line</b></td><td>Output packing di line.</td></tr>
                        <tr><td><b>Transfer Garment</b></td><td>Qty yang sudah ditransfer ke packing.</td></tr>
                        <tr><td><b>Packing In</b></td><td>Qty yang sudah diterima packing (tanpa switching).</td></tr>
                        <tr><td><b>Switching Out / In</b></td><td><span style="color:#e11d48;font-weight:700">−</span> merah = keluar ke PO lain, <span style="color:#16a34a;font-weight:700">+</span> hijau = masuk dari PO lain.</td></tr>
                        <tr><td><b>Packing Out</b></td><td>Qty yang sudah di-scan Packing Out.</td></tr>
                        <tr><td><b>Progres</b></td><td>Packing Out dibagi Qty PO (maksimal 100%).</td></tr>
                        <tr><td><b>Titik tgl shipment</b></td><td><span class="sg-dot" style="background:#e11d48"></span>≤ 3 hari / lewat &nbsp; <span class="sg-dot" style="background:#d97706"></span>4–7 hari &nbsp; <span class="sg-dot" style="background:#16a34a"></span>&gt; 7 hari &nbsp; <span class="sg-dot" style="background:#c0c7d2"></span>order Close</td></tr>
                        <tr><td><b>Kotak pencarian</b></td><td>Kotak di kanan atas mencari di semua kolom; baris ketiga judul tabel menyaring per kolom.</td></tr>
                    </table>
                </section>

                <section id="sg-edit">
                    <h6><span>Edit per baris</span> <button type="button" class="sg-show" data-spot="#tb tr:first-child .ab"><i class="fa fa-crosshairs"></i> Tunjukkan di halaman</button></h6>
                    <ul>
                        <li>Tombol pensil di ujung kanan baris membuka <b>Update Data</b>: ubah <b>Tgl Shipment</b> dan/atau <b>Qty PO</b>, lalu Simpan.</li>
                        <li>Nilai lama tercatat hanya untuk yang benar-benar diubah.</li>
                    </ul>
                </section>

                <section id="sg-tips">
                    <h6>Tips &amp; FAQ</h6>
                    <dl class="sg-faq mb-0">
                        <dt>Data yang dicari tidak muncul?</dt>
                        <dd>Cek rentang Tgl Shipment dan Status Order. Kosongkan tanggal lalu pilih WS / Style untuk mencari tanpa batas tanggal.</dd>
                        <dt>Tombol Simpan upload tidak bisa diklik?</dt>
                        <dd>Masih ada baris yang bukan Ok atau ada data double. Klik kotak status di preview untuk melihat baris bermasalah.</dd>
                        <dt>Update Tanggal: "Tidak ada yang ditambahkan"?</dt>
                        <dd>PO tersebut bukan upload Anda (selain admin 01 / it), atau tanggal yang dipilih sama dengan tgl shipment sekarang.</dd>
                        <dt>WS tidak ada di Edit Multiple?</dt>
                        <dd>Hanya WS dengan order Open yang ditampilkan, dan untuk user selain admin 01 / it hanya data upload sendiri.</dd>
                        <dt>Angka KPI beda dengan total tabel?</dt>
                        <dd>KPI hanya menghitung baris Open yang Packing Out-nya belum lengkap dari semua tanggal, sedangkan total tabel mengikuti filter.</dd>
                    </dl>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom-script')
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script>
// ===== Data Master SO PPIC (query di PPIC_MasterSOController@index_new) =====
// Packing Line = count output_rfts_packing_po per po_id; Switching dari packing_central_switching.
// Qty Packing In, Switching, dan Packing Out masing-masing murni (tidak saling ditambah/kurang).
const BLN=["Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agu","Sep","Okt","Nov","Des"];
const MON_EN={Jan:1,Feb:2,Mar:3,Apr:4,May:5,Jun:6,Jul:7,Aug:8,Sep:9,Oct:10,Nov:11,Dec:12};
const TODAY=new Date();TODAY.setHours(0,0,0,0);
let DATA=[];
let LOADED=null; // range/filter yang terakhir diambil dari server
const num=v=>Number(v)||0;
const esc=v=>String(v??"").replace(/[&<>"']/g,c=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;"}[c]));
const unesc=v=>{const t=document.createElement("textarea");t.innerHTML=v||"";return t.value};
function toIso(s){const m=String(s||"").match(/^(\d{1,2})-([A-Za-z]{3})-(\d{4})$/);if(!m||!MON_EN[m[2]])return "";return m[3]+"-"+String(MON_EN[m[2]]).padStart(2,"0")+"-"+m[1].padStart(2,"0")}
function mapRow(r){
  // pakai tgl_shipment asli (YYYY-MM-DD) agar tidak bergantung bahasa nama bulan di MySQL
  const raw=String(r.tgl_shipment||"").slice(0,10);
  const iso=/^\d{4}-\d{2}-\d{2}$/.test(raw)&&raw!=="0000-00-00"?raw:toIso(r.tgl_shipment_fix);
  const days=iso?Math.round((new Date(iso+"T00:00:00")-TODAY)/864e5):9999;
  const ca=String(r.created_at||"");
  return {id:esc(r.id),buyer:esc(r.buyer),ship:iso?(([y,m,d])=>d+"-"+BLN[+m-1]+"-"+y)(iso.split("-")):esc(r.tgl_shipment_fix||"-"),iso,days,
    ws:esc(r.ws),style:esc(r.styleno),reff:esc(r.reff_no),po:esc(r.po),desc:esc(r.desc),color:esc(r.color),size:esc(r.size),dest:esc(r.dest),barcode:esc(r.barcode),
    qpo:num(r.qty_po),pline:num(r.qty_packing_line),qtr:num(r.qty_trf),pin:num(r.qty_packing_in),so:num(r.qty_switching_out),si:num(r.qty_switching_in),pout:num(r.qty_packing_out),
    user:esc(r.created_by),upl:ca.slice(0,10),uplt:ca.slice(11,19),status:r.close_order==="Y"?"close":"open"};
}
const msg=t=>{document.getElementById("tb").innerHTML=`<tr><td colspan="17" style="text-align:center;color:var(--muted);height:90px">${t}</td></tr>`};
// ===== Edit per baris (sama seperti Master SO lama) =====
function edit(id_c){
  $("#form_edit")[0].reset();
  document.getElementById("me_info").textContent="Memuat data...";
  $.ajax({url:"{{ route('show_data_ppic_master_so') }}",method:"GET",data:{id_c:id_c},dataType:"json",
    success:r=>{
      document.getElementById("me_info").textContent=["PO "+(r.po||"-"),r.ws,r.styleno,r.color,r.size].filter(Boolean).join(" · ");
      document.getElementById("txted_qty_po").value=r.qty_po;
      document.getElementById("txted_qty_po_skrg").value=r.qty_po;
      document.getElementById("txted_tgl_shipment").value=r.tgl_shipment_fix;
      document.getElementById("txted_tgl_shipment_skrg").value=r.tgl_shipment;
      document.getElementById("txtid_c").value=id_c;
    },
    error:req=>alert(req.responseText)
  });
}
// Simpan edit: setelah berhasil, data tabel dimuat ulang dengan filter yang sama (tanpa reload halaman)
function submitEdit(form,evt){
  evt.preventDefault();
  $("#loading").removeClass("d-none");
  $.ajax({url:form.getAttribute("action"),type:"POST",data:new FormData(form),processData:false,contentType:false,
    success:res=>{
      $("#loading").addClass("d-none");
      if(res.status==200){
        $("#exampleModalEdit").modal("hide");
        toast(res.message||"Data berhasil diupdate","success");
        LOADED=null;loadData();loadOpen();
      }else{
        toast(res.message||"Data gagal diupdate","error");
      }
    },
    error:req=>{$("#loading").addClass("d-none");toast((req.responseJSON&&req.responseJSON.message)||"Data gagal diupdate","error")}
  });
}

// ===== Edit Multiple (fungsi sama seperti Master SO lama) =====
const MM={edit:[],hapus:[]};
const mmSel=k=>({ws:$(k==="edit"?"#cbows_edit_tgl":"#cbows_hapus"),po:$(k==="edit"?"#cbopo_edit_tgl":"#cbopo_hapus")});
$("#modalEditMassal .mm-s2").select2({theme:"bootstrap4",width:"100%",dropdownParent:$("#modalEditMassal")});
function mmTab(t){
  $(".mm-tabs button").removeClass("on").filter("[data-tab="+t+"]").addClass("on");
  $("#mm_edit").toggle(t==="edit");$("#mm_hapus").toggle(t==="hapus");
}
// Pilih WS -> ambil daftar No. PO
function mmPO(k){
  const s=mmSel(k),ws=s.ws.val();
  s.po.html('<option value="">Pilih No PO</option>').trigger("change.select2");
  MM[k]=[];mmRender(k);
  if(!ws)return;
  $.ajax({type:"GET",url:k==="edit"?"{{ route('getpo_ppic_edit_tgl') }}":"{{ route('getpo_ppic_hapus') }}",
    data:k==="edit"?{cbows_edit_tgl:ws}:{cbows_hapus:ws},
    success:html=>{if(html)s.po.html(html).trigger("change.select2")}});
}
// Pilih No. PO -> ambil data baris
function mmList(k){
  const s=mmSel(k),po=s.po.val();
  if(!po){MM[k]=[];mmRender(k);return}
  const cols=k==="edit"?17:16;
  document.getElementById("mm_"+k+"_tb").innerHTML=`<tr><td colspan="${cols}" class="mm-empty">Memuat data...</td></tr>`;
  $.ajax({type:"GET",url:"{{ route('list_master_ppic_edit') }}",data:{ws:s.ws.val(),po:po},dataType:"json",
    success:res=>{MM[k]=res.data||[];mmRender(k)},
    error:()=>{MM[k]=[];mmRender(k);toast("Gagal memuat data","error")}});
}
function mmRender(k){
  const rows=MM[k],tb=document.getElementById("mm_"+k+"_tb"),cols=k==="edit"?17:16;
  const q=(document.getElementById("mm_"+k+"_q").value||"").toLowerCase();
  if(k==="hapus")document.getElementById("mm_all").checked=false;
  if(!rows.length){
    tb.innerHTML=`<tr><td colspan="${cols}" class="mm-empty">${mmSel(k).po.val()?"Data tidak ditemukan":"Pilih WS dan No. PO terlebih dahulu"}</td></tr>`;
    document.getElementById("mm_"+k+"_cnt").innerHTML="";return;
  }
  const n=v=>Number(v)||0;
  tb.innerHTML=rows.map(r=>{
    const hide=q&&!Object.values(r).join(" ").toLowerCase().includes(q);
    const id=esc(r.id), tr=hide?'<tr style="display:none">':'<tr>';
    if(k==="edit"){
      const bcRo=n(r.qty_packing_out)!==0, poRo=!(n(r.qty_trf)===0&&r.id_pl===null);
      return `${tr}
        <td class="mono">${esc(r.id_so_det)}<input type="hidden" name="id[${id}]" value="${id}"></td>
        <td>${esc(r.buyer)}</td>
        <td><input type="date" class="mm-in w-tgl" name="tgl_shipment[${id}]" value="${esc(r.tgl_shipment)}"></td>
        <td>${esc(r.ws)}</td><td>${esc(r.styleno)}</td>
        <td><input type="text" class="mm-in w-bc" name="barcode[${id}]" value="${esc(r.barcode)}"${bcRo?' readonly title="Sudah ada Packing Out"':''}></td>
        <td><input type="text" class="mm-in w-q" name="qty_po[${id}]" value="${esc(r.qty_po)}" inputmode="numeric"></td>
        <td><input type="text" class="mm-in w-po" name="po[${id}]" value="${esc(r.po)}"${poRo?' readonly title="Sudah ada Transfer Garment / Packing List"':''}></td>
        <td>${esc(r.reff_no)}</td><td>${esc(r.dest)}</td><td>${esc(r.desc)}</td><td>${esc(r.color)}</td><td>${esc(r.size)}</td>
        <td class="q">${fmt(n(r.qty_trf))}</td><td class="q">${fmt(n(r.qty_packing_out))}</td>
        <td>${esc(r.created_by)}</td><td>${esc(r.created_at)}</td></tr>`;
    }
    const bisa=n(r.qty_trf)===0&&n(r.qty_packing_list)===0;
    const cb=bisa?`<input type="checkbox" class="mm-cb" name="cek_data[${id}]" value="${id}" onchange="mmCnt()">`
                 :'<i class="fas fa-lock mm-lock" title="Sudah ada Transfer Garment / Packing List"></i>';
    return `${tr}
      <td class="c">${cb}</td>
      <td>${esc(r.buyer)}</td><td>${esc(r.tgl_shipment_fix)}</td><td>${esc(r.ws)}</td><td>${esc(r.styleno)}</td><td class="mono">${esc(r.barcode)}</td>
      <td class="q">${fmt(n(r.qty_po))}</td><td>${esc(r.po)}</td><td>${esc(r.reff_no)}</td><td>${esc(r.dest)}</td><td>${esc(r.desc)}</td>
      <td>${esc(r.color)}</td><td>${esc(r.size)}</td><td class="q">${fmt(n(r.qty_trf))}</td><td>${esc(r.created_by)}</td><td>${esc(r.created_at)}</td></tr>`;
  }).join("");
  mmCnt();
}
function mmCnt(){
  ["edit","hapus"].forEach(k=>{
    const el=document.getElementById("mm_"+k+"_cnt");
    if(!MM[k].length){el.innerHTML="";return}
    el.innerHTML=k==="edit"?`<b>${MM[k].length}</b> baris`
      :`<b>${$("#mm_hapus_tb .mm-cb:checked").length}</b> dipilih dari ${MM[k].length} baris · baris bergembok tidak bisa dihapus`;
  });
}
function mmAll(src){$("#mm_hapus_tb tr:visible .mm-cb").prop("checked",src.checked);mmCnt()}
$("#cbows_edit_tgl").on("change",()=>mmPO("edit"));
$("#cbopo_edit_tgl").on("change",()=>mmList("edit"));
$("#cbows_hapus").on("change",()=>mmPO("hapus"));
$("#cbopo_hapus").on("change",()=>mmList("hapus"));
// Saat modal dibuka: kosongkan pilihan seperti modul lama
$("#modalEditMassal").on("show.bs.modal",function(e){
  if(e.target!==this)return;
  mmTab("edit");$("#mm_edit_q,#mm_hapus_q").val("");
  $("#cbows_edit_tgl,#cbows_hapus").val("").trigger("change");
});
// Update Tanggal: ubah tgl shipment semua baris PO terpilih
function update_tgl(){
  const ws=$("#cbows_edit_tgl").val(),po=$("#cbopo_edit_tgl").val(),tgl=$("#tgl_ubah").val();
  if(!ws||!po){toast("Pilih WS dan No. PO terlebih dahulu","warning");return}
  $.ajax({type:"POST",url:"{{ route('update_tgl_ppic_master_so') }}",data:{cbows_edit_tgl:ws,cbopo_edit_tgl:po,tgl_ubah:tgl},
    success:res=>{
      toast(res.msg,res.icon=="salah"?"warning":"success");
      mmList("edit");LOADED=null;loadData();loadOpen();
    },
    error:()=>toast("Gagal update tanggal","error")});
}
function mmPost(form,after){
  $("#loading").removeClass("d-none");
  $.ajax({url:form.getAttribute("action"),type:"POST",data:new FormData(form),processData:false,contentType:false,
    success:res=>{
      $("#loading").addClass("d-none");
      if(res.status==200||res.status==201){
        toast(res.message||"Berhasil","success");
        after();LOADED=null;loadData();loadOpen();
      }else{
        toast(res.message||"Proses gagal","error");
      }
    },
    error:req=>{$("#loading").addClass("d-none");toast((req.responseJSON&&req.responseJSON.message)||"Proses gagal","error")}
  });
}
function mmSubmitEdit(form,evt){
  evt.preventDefault();
  if(!MM.edit.length){toast("Pilih WS dan No. PO terlebih dahulu","warning");return}
  mmPost(form,()=>mmList("edit"));
}
function mmSubmitHapus(form,evt){
  evt.preventDefault();
  const n=$("#mm_hapus_tb .mm-cb:checked").length;
  if(!n){toast("Belum ada data yang dipilih","warning");return}
  Swal.fire({icon:"warning",title:"Hapus "+n+" data?",text:"Data yang dihapus akan dicatat ke log.",showCancelButton:true,
    confirmButtonText:"Ya, hapus",cancelButtonText:"Batal",confirmButtonColor:"#e11d48"})
    .then(r=>{if(r.isConfirmed)mmPost(form,()=>mmList("hapus"))});
}

// Export Excel: ikut filter yang sedang dipakai tabel (Tgl Shipment, WS, Style, PO, Status Order)
function exportExcel(){
  const d={dateFrom:AF.d1,dateTo:AF.d2,ws:unesc(AF.ws),style:unesc(AF.style),po:unesc(AF.po),status:AF.status};
  if(!(d.dateFrom&&d.dateTo)&&!d.ws&&!d.style){toast("Isi Tgl Shipment Awal &amp; Akhir, atau pilih WS / Style","warning");return}
  Swal.fire({title:"Please Wait...",html:"Exporting Data...",didOpen:()=>Swal.showLoading(),allowOutsideClick:false});
  $.ajax({type:"GET",url:"{{ route('export_excel_master_so_new') }}",data:d,xhrFields:{responseType:"blob"},
    success:blob=>{
      Swal.close();
      const a=document.createElement("a");a.href=URL.createObjectURL(blob);
      a.download="Laporan Master SO PPIC "+(d.dateFrom||"")+" sd "+(d.dateTo||"")+".xlsx";
      document.body.appendChild(a);a.click();a.remove();setTimeout(()=>URL.revokeObjectURL(a.href),1000);
      toast("Data sudah di-export","success");
    },
    error:()=>{Swal.close();toast("Gagal export Excel","error")}
  });
}
function needFetch(){
  if(!LOADED) return true;
  if(AF.d1&&AF.d2) return !(LOADED.d1&&AF.d1>=LOADED.d1&&AF.d2<=LOADED.d2);
  return !(LOADED.ws===AF.ws&&LOADED.style===AF.style);
}
function loadData(){
  const byDate=!!(AF.d1&&AF.d2);
  if(!byDate&&!AF.ws&&!AF.style){toast("Isi Tgl Shipment Awal &amp; Akhir, atau pilih WS / Style","warning");return}
  msg("Memuat data...");
  $.ajax({url:"{{ route('master-so-new') }}",type:"GET",dataType:"json",
    data:{dateFrom:AF.d1,dateTo:AF.d2,ws:byDate?"":unesc(AF.ws),style:byDate?"":unesc(AF.style)},
    success:res=>{
      DATA=(res.data||[]).map(mapRow);
      LOADED=byDate?{d1:AF.d1,d2:AF.d2}:{ws:AF.ws,style:AF.style};
      applyFilter();autoTour();
    },
    error:()=>{msg("Gagal memuat data");toast("Gagal memuat data Master SO","error");autoTour()}
  });
}
const ST={open:"Open",close:"Close"};
const fmt=n=>n.toLocaleString("id-ID");

// KPI
function kpis(){
  // KPI tidak ikut filter: semua PO Open (tanpa batas tanggal) yang Packing Out-nya masih kurang
  if(OPEN_LOADING){
    const sp='<span class="mini-spin"></span>';
    document.getElementById("kpis").innerHTML=[["Qty PO Outstanding","#0b1f3f"],["Qty Packing Line","#0891b2"],["Qty Transfer Garment","#7c3aed"],["Qty Packing In","#0284c7"],["Qty Packing Out","#16a34a"]]
      .map(c=>`<button class="kpi" style="--c:${c[1]};cursor:progress" disabled><div class="lbl"><span class="dot" style="background:${c[1]}"></span>${c[0]}</div><div class="val">${sp}</div><div class="sub">Memuat data...</div><div class="bar"><i style="width:0"></i></div></button>`).join("");
    return;
  }
  const OPEN=OPEN_DATA.filter(r=>r.status==="open"&&r.pout<r.qpo); // outstanding: Open & Packing Out belum beres
  const t=k=>OPEN.reduce((a,r)=>a+r[k],0);
  const pl=t("pline");
  const po=t("qpo"),pin=t("pin"),pout=t("pout"),tr=t("qtr");
  const pct=(a)=>po?Math.round(a/po*100):0;
  const cards=[
    ["Qty PO Outstanding",po,"#0b1f3f",new Set(OPEN.map(r=>r.po)).size+" PO · "+OPEN.length+" baris belum selesai",100,"po"],
    ["Qty Packing Line",pl,"#0891b2",pct(pl)+"% dari PO",pct(pl),"pline",po-pl],
    ["Qty Transfer Garment",tr,"#7c3aed",pct(tr)+"% dari PO",pct(tr),"qtr",po-tr],
    ["Qty Packing In",pin,"#0284c7",pct(pin)+"% dari PO",pct(pin),"pin",po-pin],
    ["Qty Packing Out",pout,"#16a34a",pct(pout)+"% dari PO",pct(pout),"pout",po-pout],
  ];
  document.getElementById("kpis").innerHTML=cards.map(c=>`
    <button class="kpi" style="--c:${c[2]}" onclick="openDrawer('${c[5]}')"><span class="more">Lihat detail ›</span><div class="lbl"><span class="dot" style="background:${c[2]}"></span>${c[0]}</div>
    <div class="val">${fmt(c[1])}</div><div class="sub">${c[3]}${c[6]!==undefined?` · <span class="bal">Balance <b>${fmt(c[6])}</b></span>`:""}</div>
    <div class="bar"><i style="width:${c[4]}%;background:${c[2]}"></i></div></button>`).join("");
}

// search row
const sfields=["id","buyer","ship","ws|style","po|dest","color|desc","size|barcode","qpo","pline","qtr","pin","so|si","pout","","user|upl|uplt","status",""];
document.getElementById("srow").innerHTML=sfields.map((f,i)=>{
  let cls=i===0?"stk stk1":i===1?"stk stk2":"";
  if(i===7) cls+=" qfirst"; if(i===12) cls+=" qlast";
  return `<th class="${cls}">${f?`<input size="3" data-f="${f}" placeholder="cari">`:""}</th>`}).join("");

let page=1, per=10, filtered=DATA.slice();
function applyFilter(){
  const q=document.getElementById("q").value.toLowerCase();
  const cols=[...document.querySelectorAll("#srow input")].filter(i=>i.value).map(i=>[i.dataset.f,i.value.toLowerCase()]);
  const fs=AF.status;
  filtered=DATA.filter(r=>{
    if(fs && r.status!==fs) return false;
    if(AF.d1 && r.iso<AF.d1) return false;
    if(AF.d2 && r.iso>AF.d2) return false;
    if(AF.ws && r.ws!==AF.ws) return false;
    if(AF.style && r.style!==AF.style) return false;
    if(AF.po && r.po!==AF.po) return false;
    if(q && !Object.values(r).join(" ").toLowerCase().includes(q)) return false;
    for(const [f,v] of cols){ const val=f.split("|").map(x=>x==="status"?ST[r.status]:r[x]).join(" "); if(!String(val).toLowerCase().includes(v)) return false; }
    return true;
  });
  page=1; render(); drawChips();
}
// ===== Filter aktif (chip) =====
const AF={d1:document.getElementById("fd1").value,d2:document.getElementById("fd2").value,status:"",ws:"",style:"",po:""};
const isoTxt=v=>{const [y,m,d]=v.split("-");return +d+" "+BLN[+m-1]+" "+y};
function cari(){
  AF.d1=document.getElementById("fd1").value; AF.d2=document.getElementById("fd2").value;
  AF.status=document.getElementById("fstat").value; AF.ws=fws.value; AF.style=fst.value; AF.po=fpo.value;
  needFetch()?loadData():applyFilter();
}
function resetFilter(){
  document.getElementById("q").value="";
  document.querySelectorAll("#srow input").forEach(i=>i.value="");
  unChip("all");
}
function drawChips(){
  const c=[];
  if(AF.d1||AF.d2) c.push(["tgl","Shipment "+(AF.d1?isoTxt(AF.d1):"…")+" – "+(AF.d2?isoTxt(AF.d2):"…")]);
  if(AF.status) c.push(["status","Status Order: "+ST[AF.status]]);
  if(AF.ws) c.push(["ws","WS: "+AF.ws]);
  if(AF.style) c.push(["style","Style: "+AF.style]);
  if(AF.po) c.push(["po","PO: "+AF.po]);
  document.getElementById("chipL").innerHTML=c.length
    ? c.map(x=>`<span class="chip">${x[1]} <button title="Hapus filter ini" onclick="unChip('${x[0]}')">×</button></span>`).join("")+(c.length>1?`<button class="chip" style="border:0;cursor:pointer;background:#fee2e2;color:#b91c1c" onclick="unChip('all')">Hapus semua</button>`:"")
    : `<span class="muted">Tidak ada, semua data ditampilkan</span>`;
}
function unChip(k){
  if(k==="tgl"||k==="all"){AF.d1=AF.d2="";document.getElementById("fd1").value="";document.getElementById("fd2").value="";loadOpsi()}
  if(k==="status"||k==="all"){AF.status="";$("#fstat").val("").trigger("change.select2")}
  if(k==="ws"||k==="style"||k==="all"){AF.ws=AF.style=AF.po="";fillWS("");fillST("","");fillPO("","","")}
  if(k==="po"){AF.po="";fillPO(AF.ws,AF.style,"")}
  applyFilter();
}
function qcell(r,k,extra=""){
  const v=r[k]; let c="q";
  if(v===0) c+=" zero"; else if(k==="si") c+=" pos"; else if(k==="so") c+=" neg";
  return `<td class="${c} ${extra}">${v===0?"0":(k==="so"?"−":k==="si"?"+":"")+fmt(v)}</td>`;
}
function render(){
  const start=(page-1)*per, rows=filtered.slice(start,start+per);
  document.getElementById("tb").innerHTML=rows.map(r=>{
    const p=r.qpo?Math.min(100,Math.floor(r.pout/r.qpo*100)):0;
    const pc=p>=100?"var(--green)":p>=50?"var(--sky)":p>0?"var(--amber)":"#c0c7d2";
    return `<tr>
    <td class="stk stk1 mono" style="color:var(--muted)">${r.id}</td>
    <td class="stk stk2 buyer"><b>${r.buyer}</b></td>
    <td><span class="date" title="${hlabel(r.days)}"><span class="d ${r.status==="close"?"gry":r.days<=3?"red":r.days<=7?"amb":"grn"}"></span>${r.ship}</span></td>
    <td class="two"><b class="ws">${r.ws}</b><span>${r.style}</span></td>
    <td class="two"><b class="mono">${r.po}</b><span><span class="dest">${r.dest}</span></span></td>
    <td class="two"><b>${r.color}</b><span>${r.desc}</span></td>
    <td class="two"><b><span class="size">${r.size}</span></b><span class="mono">${r.barcode}</span></td>
    ${qcell(r,"qpo","qfirst")}${qcell(r,"pline")}${qcell(r,"qtr")}${qcell(r,"pin")}
    <td class="q sw"><span class="${r.so?"neg":"zero"}">${r.so?"−"+fmt(r.so):"0"}</span><span class="${r.si?"pos":"zero"}">${r.si?"+"+fmt(r.si):"0"}</span></td>
    ${qcell(r,"pout","qlast")}
    <td><div class="prog"><div class="bar"><i style="width:${p}%;background:${pc}"></i></div><small style="color:${pc}">${p}%</small></div></td>
    <td class="two"><b style="font-weight:600">${r.user}</b><span>${r.upl?(([y,m,d])=>+d+" "+BLN[+m-1]+" "+y)(r.upl.split("-"))+" · "+r.uplt.slice(0,5):"-"}</span></td>
    <td><span class="st st-${r.status}">${ST[r.status]}</span></td>
    <td><button class="ab" title="Edit" data-bs-toggle="modal" data-bs-target="#exampleModalEdit" onclick="edit('${r.id}')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg></button></td>
    </tr>`}).join("") || `<tr><td colspan="17" style="text-align:center;color:var(--muted);height:90px">Data tidak ditemukan</td></tr>`;

  const t=k=>filtered.reduce((a,r)=>a+r[k],0);
  document.getElementById("tf").innerHTML=`
    <td class="stk stk1 tl">Total</td><td class="stk stk2 tl">${filtered.length} baris</td>
    <td colspan="5"></td>
    ${["qpo","pline","qtr","pin"].map(k=>`<td class="q">${fmt(t(k))}</td>`).join("")}
    <td class="q sw"><span>−${fmt(t("so"))}</span><span>+${fmt(t("si"))}</span></td><td class="q">${fmt(t("pout"))}</td>
    <td>${t("qpo")?Math.round(t("pout")/t("qpo")*100):0}%</td><td colspan="3"></td>`;

  const n=filtered.length, pages=Math.max(1,Math.ceil(n/per));
  document.getElementById("info").innerHTML=n?`Menampilkan <b>${start+1}–${Math.min(start+per,n)}</b> dari <b>${n}</b> data`:"Tidak ada data";
  let h=`<button ${page===1?"disabled":""} onclick="go(${page-1})">‹ Sebelumnya</button>`;
  let last=0;for(let i=1;i<=pages;i++){if(i!==1&&i!==pages&&Math.abs(i-page)>2)continue;if(i-last>1)h+=`<button disabled>…</button>`;h+=`<button class="${i===page?"on":""}" onclick="go(${i})">${i}</button>`;last=i}
  h+=`<button ${page===pages?"disabled":""} onclick="go(${page+1})">Berikutnya ›</button>`;
  document.getElementById("pages").innerHTML=h;
}
function go(p){page=p;render()}
function toggleUpload(){const w=document.getElementById("upWrap");w.classList.toggle("open");document.getElementById("upTog").textContent=w.classList.contains("open")?"−":"+";if(w.classList.contains("open"))pvFetch()}
document.getElementById("q").addEventListener("input",applyFilter);
$("#fstat").on("change",cari);
document.getElementById("srow").addEventListener("input",applyFilter);
document.getElementById("per").addEventListener("change",e=>{per=+e.target.value;page=1;render()});

// ===== Upload Master SO PPIC (endpoint sama seperti Master SO lama) =====
const PVST={ok:["Ok","Data aman / lengkap"],dbl:["Double","Data yang diupload ada double"],inv:["Tidak Valid","WS, Color, Style, Dest tidak cocok master SB, atau tanggal shipment kosong"],ada:["Sudah Terinput","Data sudah ada di sistem"]};
let PV=[],pvf="all",pvDouble=0,pvName="",pvMeta="";
// keterangan dari server -> [kode status, teks keterangan]
function pvKet(k){
  k=String(k||"");
  if(k==="Ok")return["ok",""];
  if(k==="Double")return["dbl","Data yang diupload ada double"];
  if(k==="Sudah Terinput")return["ada","Data sudah ada terinput di sistem"];
  const t=[];
  if(k.includes("id_so_det"))t.push("WS, Color, Style, atau Dest tidak sesuai master SB");
  if(k.includes("tgl_shipment"))t.push("Tgl shipment belum diisi (0000-00-00)");
  return["inv",t.join("; ")||esc(k)];
}
function pvFetch(){
  $.ajax({url:"{{ route('show_tmp_ppic_so') }}",type:"GET",dataType:"json",
    success:res=>{
      PV=(res.data||[]).map(r=>{
        const [st,ket]=pvKet(r.keterangan),k=String(r.keterangan||"");
        return {id:esc(r.id_tmp),idso:esc(r.id_so_det??""),ws:esc(r.ws),style:esc(r.style),desc:esc(r.desc),po:esc(r.po),
          color:esc(r.color),size:esc(r.size),dest:esc(r.dest),barcode:esc(r.barcode),total:num(r.qty_po),buyer:esc(r.buyer),
          ship:esc(r.tgl_shipment),status:r.status,st,ket,badId:k.includes("id_so_det"),badShip:k.includes("tgl_shipment")};
      });
      pvState(PV.length>0);pvRender();
    },
    error:()=>toast("Gagal memuat preview upload","error")});
  $.ajax({url:"{{ route('data_cek_double_tmp_ppic_so') }}",type:"GET",dataType:"json",
    success:r=>{pvDouble=r?num(r.tot_cek):0;pvRender()},
    error:()=>{pvDouble=0}});
}
function pvRender(){
  const q=(document.getElementById("pvq").value||"").toLowerCase();
  const cnt=k=>PV.filter(r=>r.st===k).length;
  const boxes=[["all","Semua",PV.length,"baris di preview","c-all"],["ok","Ok",cnt("ok"),"data aman","c-ok"],["dbl","Data Double",cnt("dbl"),"baris sama persis","c-dbl"],["inv","Tidak Valid",cnt("inv"),"perlu diperbaiki","c-inv"],["ada","Sudah Terinput",cnt("ada"),"sudah ada di sistem","c-ada"]];
  document.getElementById("pvsum").innerHTML=boxes.map(b=>`<button class="sbox ${b[4]} ${pvf===b[0]?"on":""}" onclick="pvf='${b[0]}';pvRender()"><div class="t">${b[1]}</div><div class="n">${b[2]}</div><div class="d">${b[3]}</div></button>`).join("");
  const rows=PV.filter(r=>(pvf==="all"||r.st===pvf)&&(!q||Object.values(r).join(" ").toLowerCase().includes(q)));
  const cls={ok:"",dbl:"warn",inv:"bad",ada:"ada"};
  document.getElementById("pvtb").innerHTML=rows.map(r=>`<tr class="${cls[r.st]}">
    <td class="mono ${r.badId?"err":""}" style="${r.badId?"":"color:var(--muted)"}">${r.idso||"—"}</td><td class="ws">${r.ws}</td><td>${r.style}</td><td>${r.desc}</td><td class="mono">${r.po}</td>
    <td>${r.color}</td><td><span class="size">${r.size}</span></td><td><span class="dest">${r.dest||"-"}</span></td><td><span class="mono">${r.barcode}</span></td>
    <td class="q" style="font-weight:700">${fmt(r.total)}</td><td>${r.buyer}</td><td class="${r.badShip?"err":""}">${r.ship}</td>
    <td><span class="tag ${r.st}" title="${PVST[r.st][1]}">${PVST[r.st][0]}</span></td>
    <td><div class="ket">${r.ket||"<span style='color:#98a2b3'>—</span>"}</div></td>
    <td><button class="del" title="Hapus baris" onclick="pvDel('${r.id}')"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14"/></svg></button></td>
  </tr>`).join("") || `<tr><td colspan="15" class="empty">Tidak ada data</td></tr>`;
  const tot=rows.reduce((a,r)=>a+r.total,0);
  document.getElementById("pvtf").innerHTML=`<td class="tl" colspan="9">Total (${rows.length} baris)</td><td class="q">${fmt(tot)}</td><td colspan="5">PCS</td>`;
  // Aturan simpan sama seperti modul lama: tidak ada data double & semua baris berstatus Ok
  const prob=PV.filter(r=>r.st!=="ok"||r.status!=="Ok").length;
  const canSave=PV.length>0&&prob===0&&!pvDouble;
  document.querySelector(".mbtn.save").disabled=!canSave;
  const totAll=PV.reduce((a,r)=>a+r.total,0);
  document.getElementById("pvmsg").innerHTML=!PV.length?""
    :canSave?`<b>${PV.length} baris</b> (${fmt(totAll)} pcs) siap disimpan`
    :`<span style="color:var(--rose)">${prob?prob+" baris bermasalah":"Ada data double"}</span> · hapus baris bermasalah atau perbaiki file dulu sebelum simpan`;
}
function pvState(has){
  document.querySelector(".drop").classList.toggle("empty",!has);
  document.getElementById("pvFn").textContent=has?(pvName||"Data preview belum disimpan"):"Belum ada file dipilih";
  document.getElementById("pvFm").textContent=has
    ?(pvMeta||"Hasil upload sebelumnya")+" · file berikutnya akan ditambahkan ke preview"
    :"Tarik file Excel ke sini atau klik untuk memilih file (.xlsx / .xls / .csv)";
  document.getElementById("pvChg").textContent=has?"Tambah File":"Pilih File";
  document.getElementById("pvEmpty").style.display=has?"none":"flex";
  document.getElementById("pvArea").style.display=has?"":"none";
  const s1=document.getElementById("st1"),s2=document.getElementById("st2");
  s1.className="step "+(has?"done":"on");s1.querySelector("i").textContent=has?"✓":"1";
  s2.className="step "+(has?"on":"");
}
function pvUpload(file){
  if(!file)return;
  if(!/\.(xlsx|xls|csv)$/i.test(file.name)){toast("File harus Excel (.xlsx / .xls / .csv)","warning");return}
  const fd=new FormData();fd.append("file",file);
  Swal.fire({title:"Please Wait...",html:"Mengupload file...",didOpen:()=>Swal.showLoading(),allowOutsideClick:false});
  $.ajax({url:"{{ route('import-excel-so') }}",type:"POST",data:fd,processData:false,contentType:false,
    success:res=>{
      Swal.close();
      if(res&&res.status==200){
        pvName=esc(file.name);pvMeta=Math.max(1,Math.round(file.size/1024))+" KB · diunggah "+new Date().toTimeString().slice(0,5);
        pvf="all";toast("File diupload, silakan cek preview","success");pvFetch();
      }else{
        toast("Upload gagal: "+((res&&res.message)||"Periksa file Excel"),"error");
      }
    },
    error:req=>{
      Swal.close();
      const j=req.responseJSON||{};
      toast("Upload gagal: "+((j.errors&&j.errors.file&&j.errors.file[0])||j.message||"Periksa format file Excel"),"error");
    }});
}
function pvDel(id){
  $.ajax({type:"POST",url:"{{ route('hapus-data-temp-ppic-so') }}",data:{id_tmp:id},
    success:()=>{toast("Baris dihapus dari preview","success");pvFetch()},
    error:()=>toast("Gagal menghapus baris","error")});
}
function pvUndo(){
  if(!PV.length){toast("Belum ada data yang diupload","warning");return}
  document.getElementById("cfmP").innerHTML=`Semua <b>${PV.length} baris</b> di preview akan dikosongkan. Data belum ada yang tersimpan, jadi aman.`;
  document.getElementById("cfmB").innerHTML=`<button class="mbtn rose-s" onclick="cfmClose();pvClear()">Ya, batalkan seluruh upload</button><button class="mbtn" onclick="cfmClose()">Tidak jadi</button>`;
  document.getElementById("cfm").classList.add("on");
}
function cfmClose(){document.getElementById("cfm").classList.remove("on")}
function pvClear(){
  $.ajax({type:"POST",url:"{{ route('undo_tmp_ppic_so') }}",
    success:res=>{toast(res.msg,res.icon=="salah"?"warning":"success");pvName="";pvMeta="";pvFetch()},
    error:()=>toast("Gagal undo upload","error")});
}
function pvSimpan(){
  if(document.querySelector(".mbtn.save").disabled)return;
  $("#loading").removeClass("d-none");
  $.ajax({type:"POST",url:"{{ route('store_tmp_ppic_so') }}",
    success:res=>{
      $("#loading").addClass("d-none");
      if(!res||res.icon=="salah"){
        toast((res&&res.msg)||"Tidak ada yang disimpan, silahkan cek lagi","warning");
      }else{
        toast(res.msg,"success");pvName="";pvMeta="";
      }
      pvFetch();LOADED=null;loadData();loadOpen();loadOpsi();
    },
    error:()=>{$("#loading").addClass("d-none");toast("Silahkan cek lagi","warning");pvFetch()}});
}
// Master Data SO SB (export, sama seperti modul lama)
function exportMasterSB(){
  Swal.fire({title:"Please Wait...",html:"Exporting Data...",didOpen:()=>Swal.showLoading(),allowOutsideClick:false});
  $.ajax({type:"GET",url:"{{ route('export_excel_master_sb_so') }}",xhrFields:{responseType:"blob"},
    success:blob=>{
      Swal.close();
      const a=document.createElement("a");a.href=URL.createObjectURL(blob);a.download="Master SO SB.xlsx";
      document.body.appendChild(a);a.click();a.remove();setTimeout(()=>URL.revokeObjectURL(a.href),1000);
      toast("Master Data SO SB sudah di-export","success");
    },
    error:()=>{Swal.close();toast("Gagal export Master Data SO SB","error")}});
}
document.getElementById("pvFile").addEventListener("change",e=>{pvUpload(e.target.files[0]);e.target.value=""});
// Tarik & lepas file ke kotak upload
(()=>{const d=document.querySelector(".drop");
  ["dragenter","dragover"].forEach(ev=>d.addEventListener(ev,e=>{e.preventDefault();d.classList.add("drag")}));
  ["dragleave","drop"].forEach(ev=>d.addEventListener(ev,e=>{e.preventDefault();d.classList.remove("drag")}));
  d.addEventListener("drop",e=>pvUpload(e.dataTransfer.files[0]));})();
// Notifikasi: iziToast di topCenter (type: success | warning | error | info)
function toast(msg,type){iziToast[type||"info"]({message:msg,position:"topCenter"})}
document.getElementById("pvq").addEventListener("input",pvRender);
pvState(false);pvRender();

// ===== WS <-> Style saling terhubung =====
let WSST={},STWS={},POM={};
// list: [{ws,style,po}] -> isi opsi WS / Style / PO (pilihan yang masih ada tetap dipertahankan)
function rebuildOpts(list){
  const cur={ws:fws.value,style:fst.value,po:fpo.value};
  WSST={};STWS={};POM={};
  (list||DATA).forEach(x=>{if(!x.ws)return;(WSST[x.ws]=WSST[x.ws]||new Set()).add(x.style);STWS[x.style]=STWS[x.style]||x.ws;if(x.po)POM[x.po]=POM[x.po]||{ws:x.ws,style:x.style}});
  if(cur.ws&&!WSST[cur.ws])cur.ws="";if(cur.style&&!STWS[cur.style])cur.style="";if(cur.po&&!POM[cur.po])cur.po="";
  fillWS(cur.ws);fillST(cur.ws,cur.style);fillPO(cur.ws,cur.style,cur.po);
}
// Ambil opsi WS / Style / PO sesuai rentang Tgl Shipment (seperti modul lama saat tanggal diganti)
let opsiReq=0;
function loadOpsi(){
  const d1=document.getElementById("fd1").value,d2=document.getElementById("fd2").value;
  if(!!d1!==!!d2)return; // salah satu tanggal kosong: tunggu sampai keduanya diisi / dikosongkan (kosong = semua)
  const n=++opsiReq;
  $.ajax({url:"{{ route('master-so-new-opsi-filter') }}",type:"GET",dataType:"json",data:{dateFrom:d1,dateTo:d2},
    success:res=>{if(n!==opsiReq)return;rebuildOpts((res.data||[]).map(r=>({ws:esc(r.ws),style:esc(r.styleno),po:esc(r.po)})));flash(fws);flash(fst);flash(fpo)},
    error:()=>toast("Gagal memuat pilihan WS / Style","error")});
}
$("#fd1,#fd2").on("change",loadOpsi);
const fws=document.getElementById("fws"), fst=document.getElementById("fst");
const opts=(sel,ph,list,val)=>{sel.innerHTML=`<option value="">${ph}</option>`+list.map(v=>`<option ${v===val?"selected":""}>${v}</option>`).join("");$(sel).trigger("change.select2")};
function fillWS(val){opts(fws,"Pilih WS",Object.keys(WSST).sort(),val)}
function fillST(ws,val){const w=ws&&WSST[ws];opts(fst,w?`Pilih Style (${w.size} style)`:"Pilih Style",w?[...w].sort():Object.keys(STWS).sort(),val)}
const fpo=document.getElementById("fpo");
function fillPO(ws,st,val){const l=Object.keys(POM).filter(p=>(!ws||POM[p].ws===ws)&&(!st||POM[p].style===st));opts(fpo,(ws||st)?`Pilih PO (${l.length} PO)`:"Pilih PO",l.sort(),val)}
fillWS("");fillST("","");fillPO("","","");
// Select2 (standar seperti Master SO lama)
$(document).on("select2:open",()=>{const x=document.querySelector(".select2-container--open .select2-search__field");if(x)x.focus()});
$("#mso .select2bs4").select2({theme:"bootstrap4",width:"100%",containerCssClass:"form-control-sm rounded"});
function syncDates(){const ws=fws.value,st=fst.value,po=fpo.value;
  const rows=DATA.filter(r=>(!ws||r.ws===ws)&&(!st||r.style===st)&&(!po||r.po===po));
  if(!rows.length)return;const ds=rows.map(r=>r.iso).sort();
  const d1=document.getElementById("fd1"),d2=document.getElementById("fd2");
  if(d1.value!==ds[0]||d2.value!==ds[ds.length-1]){d1.value=ds[0];d2.value=ds[ds.length-1];flash(d1);flash(d2)}}
$(fws).on("change",()=>{const ws=fws.value;const list=ws&&WSST[ws]?[...WSST[ws]]:[];const st=list.length===1?list[0]:"";fillST(ws,st);fillPO(ws,st,"");flash(fst);flash(fpo);syncDates()});
$(fst).on("change",()=>{const st=fst.value;if(!st){fillPO(fws.value,"","");return}const ws=STWS[st];fillWS(ws);fillST(ws,st);fillPO(ws,st,"");flash(fws);flash(fpo);syncDates()});
$(fpo).on("change",()=>{const po=fpo.value;if(!po)return;const m=POM[po];fillWS(m.ws);fillST(m.ws,m.style);fillPO(m.ws,m.style,po);flash(fws);flash(fst);syncDates()});
function flash(el){const s2=$(el).next(".select2-container").find(".select2-selection")[0];if(s2)el=s2;el.style.transition="none";el.style.boxShadow="0 0 0 3px #0ea5a455";el.style.borderColor="var(--teal)";setTimeout(()=>{el.style.transition="box-shadow .6s,border-color .6s";el.style.boxShadow="";el.style.borderColor=""},500)}

// ===== Detail per PO (drawer) & notif mepet =====
function hlabel(d){return d<0?"Lewat "+(-d)+" hari":d===0?"Hari ini":"H-"+d}
function hcls(d){return d<0?"late":d<=3?"red":d<=7?"amb":"ok"}
function groupPO(fn,src){
  const m={};
  (fn?(src||DATA).filter(fn):(src||DATA)).forEach(r=>{const g=m[r.po]||(m[r.po]={po:r.po,style:r.style,ws:r.ws,color:r.color,desc:r.desc,dest:r.dest,ship:r.ship,days:r.days,status:r.status,rows:[],qpo:0,pline:0,qtr:0,pin:0,pout:0,so:0,si:0});
    g.rows.push(r);g.qpo+=r.qpo;g.pline+=r.pline;g.qtr+=r.qtr;g.pin+=r.pin;g.pout+=r.pout;g.so+=r.so;g.si+=r.si;});
  return Object.values(m).sort((a,b)=>a.days-b.days);
}
// Data semua PO Open (tanpa batas tanggal), tidak ikut filter List Master. Dipakai:
// - KPI & drawer per kartu: baris Open yang Packing Out < Qty PO
// - Notif PO mepet: tgl shipment 7 hari lagi atau kurang, Packing Out belum lengkap (per PO)
let OPEN_DATA=[],OPEN_LOADING=true;
const MEPET=()=>groupPO(null,OPEN_DATA).filter(g=>g.status==="open"&&g.days<=7&&g.pout<g.qpo);
function loadOpen(){
  OPEN_LOADING=true;kpis();drawAlert();
  $.ajax({url:"{{ route('master-so-new-mepet') }}",type:"GET",dataType:"json",
    success:res=>{OPEN_DATA=(res.data||[]).map(mapRow);OPEN_LOADING=false;kpis();drawAlert()},
    error:()=>{OPEN_LOADING=false;OPEN_DATA=[];kpis();drawAlert();toast("Gagal memuat data PO Open (KPI & notif)","error")}});
}
const DCFG={
  po:{t:"Qty PO Outstanding per PO",p:"PO Open yang Packing Out-nya belum beres, dari Transfer Garment sampai Packing Out.",k:"pout",lbl:"Packing Out"},
  pline:{t:"Qty Packing Line per PO",p:"PO yang Packing Line-nya masih kurang dari Qty PO.",k:"pline",lbl:"Packing Line"},
  qtr:{t:"Qty Transfer Garment per PO",p:"PO yang Transfer Garment-nya masih kurang dari Qty PO.",k:"qtr",lbl:"Transfer Garment"},
  pin:{t:"Qty Packing In per PO",p:"PO yang Packing In-nya masih kurang dari Qty PO.",k:"pin",lbl:"Packing In"},
  pout:{t:"Qty Packing Out per PO",p:"PO yang Packing Out-nya masih kurang dari Qty PO.",k:"pout",lbl:"Packing Out"},
  mepet:{t:"PO mendekati tanggal shipment",p:"Tanggal shipment 7 hari lagi atau kurang, status Open, Packing Out belum lengkap.",k:"pout",lbl:"Packing Out"},
};
let dKey="po", dOnly=true, dOpen=new Set(), dPage=1;
const D_PER=20; // PO per halaman di drawer
function openDrawer(k){if(OPEN_LOADING){toast("Data PO Open masih dimuat, tunggu sebentar","warning");return}dKey=k;dOnly=k!=="po";dOpen=new Set();dPage=1;document.getElementById("dQ").value="";document.getElementById("dRange").value="";document.querySelector('#dRange option[value="far"]').hidden=k==="mepet";const f=(k==="mepet"?MEPET():[])[0];if(f)dOpen.add(f.po);drawDrawer();document.getElementById("ov").classList.add("on");document.getElementById("drw").classList.add("on")}
function closeDrawer(){document.getElementById("ov").classList.remove("on");document.getElementById("drw").classList.remove("on")}
document.addEventListener("keydown",e=>{if(e.key==="Escape"){closeDrawer();cfmClose()}});
function drawDrawer(){
  const c=DCFG[dKey], k=c.k;
  let list=dKey==="mepet"?MEPET():groupPO(r=>r.status==="open"&&r.pout<r.qpo,OPEN_DATA);
  const all=list;
  if(dOnly) list=list.filter(g=>g[k]<g.qpo);
  document.getElementById("dT").textContent=c.t;
  document.getElementById("dP").textContent=c.p;
  document.getElementById("dTab").innerHTML=dKey==="mepet"?"":`<button class="${dOnly?"on":""}" onclick="dTab(true)">Yang masih kurang (${all.filter(g=>g[k]<g.qpo).length})</button><button class="${!dOnly?"on":""}" onclick="dTab(false)">Semua PO (${all.length})</button>`;
  // cari & filter tgl shipment
  const dq=(document.getElementById("dQ").value||"").toLowerCase(), dr=document.getElementById("dRange").value;
  if(dq) list=list.filter(g=>[g.po,g.ws,g.style,g.color,g.dest,g.desc].join(" ").toLowerCase().includes(dq));
  if(dr) list=list.filter(g=>dr==="late"?g.days<0:dr==="d3"?g.days>=0&&g.days<=3:dr==="d7"?g.days>=4&&g.days<=7:g.days>7);
  // ringkasan hanya ikut pilihan tgl shipment (tidak ikut pencarian & tab)
  const inRange=g=>!dr||(dr==="late"?g.days<0:dr==="d3"?g.days>=0&&g.days<=3:dr==="d7"?g.days>=4&&g.days<=7:g.days>7);
  const sum=all.filter(inRange);
  const tq=sum.reduce((a,g)=>a+g.qpo,0), tk=sum.reduce((a,g)=>a+g[k],0);
  document.getElementById("dS").innerHTML=`<div><small>Qty PO</small><b>${fmt(tq)}</b></div><div><small>${c.lbl}</small><b>${fmt(tk)}</b></div><div><small>Kurang</small><b style="color:var(--rose)">${fmt(tq-tk)}</b></div>`;
  // pagination
  const tot=list.length, pages=Math.max(1,Math.ceil(tot/D_PER));
  if(dPage>pages)dPage=pages;
  const st=(dPage-1)*D_PER;
  list=list.slice(st,st+D_PER);
  let pb=`<button ${dPage===1?"disabled":""} onclick="dGo(${dPage-1})">‹</button>`,last=0;
  for(let i=1;i<=pages;i++){if(i!==1&&i!==pages&&Math.abs(i-dPage)>1)continue;if(i-last>1)pb+=`<button disabled>…</button>`;pb+=`<button class="${i===dPage?"on":""}" onclick="dGo(${i})">${i}</button>`;last=i}
  pb+=`<button ${dPage===pages?"disabled":""} onclick="dGo(${dPage+1})">›</button>`;
  document.getElementById("dPg").innerHTML=tot>D_PER?`<span>Menampilkan <b>${st+1}–${Math.min(st+D_PER,tot)}</b> dari <b>${fmt(tot)}</b> PO</span><span class="pb">${pb}</span>`:"";
  document.getElementById("dB").innerHTML=list.map(g=>{
    const kur=g.qpo-g[k], p=g.qpo?Math.min(100,Math.floor(g[k]/g.qpo*100)):0;
    const pc=p>=100?"var(--green)":p>=50?"var(--sky)":p>0?"var(--amber)":"#c0c7d2";
    const sz=g.rows.map(r=>{const d=r.qpo-r[k];return d>0?`<span>${r.size} (-${fmt(d)})</span>`:`<span class="okz">${r.size} ✓</span>`}).join("");
    const op=dOpen.has(g.po);
    const det=op?`<div class="pod" onclick="event.stopPropagation()"><table><thead><tr><th>Size<small>Barcode</small></th><th class="q" style="--qc:#0b1f3f">Qty<small>PO</small></th><th class="q" style="--qc:#0891b2">Packing<small>Line</small></th><th class="q" style="--qc:#7c3aed">Transfer<small>Garment</small></th><th class="q" style="--qc:#0284c7">Packing<small>In</small></th><th class="q" style="--qc:#e11d48">Switching<small>Out / In</small></th><th class="q" style="--qc:#15803d">Packing<small>Out</small></th><th class="q" style="--qc:#e11d48">Kurang<small>pcs</small></th></tr></thead><tbody>${
      g.rows.map(r=>{const d=r.qpo-r[k];return `<tr class="${d>0?"":"okr"}"><td class="sb"><span class="size">${r.size}</span><span class="mono">${r.barcode}</span></td><td class="q">${fmt(r.qpo)}</td><td class="q">${fmt(r.pline)}</td><td class="q">${fmt(r.qtr)}</td><td class="q">${fmt(r.pin)}</td><td class="q sw2"><span class="${r.so?"swo":"z"}">${r.so?"−"+fmt(r.so):"0"}</span><span class="${r.si?"swi":"z"}">${r.si?"+"+fmt(r.si):"0"}</span></td><td class="q">${fmt(r.pout)}</td><td class="q ${d>0?"kr":"okk"}">${d>0?fmt(d):"✓"}</td></tr>`}).join("")
    }</tbody><tfoot><tr><td>Total</td><td class="q">${fmt(g.qpo)}</td><td class="q">${fmt(g.pline)}</td><td class="q">${fmt(g.qtr)}</td><td class="q">${fmt(g.pin)}</td><td class="q sw2"><span>−${fmt(g.so)}</span><span>+${fmt(g.si)}</span></td><td class="q">${fmt(g.pout)}</td><td class="q kr">${kur>0?fmt(kur):"0"}</td></tr></tfoot></table></div>`:"";
    return `<div class="porow ${op?"open":""}" onclick="togPO('${g.po}')">
      <div class="r1"><b>PO ${g.po}</b><span class="hx ${g.status==="close"?"ok":hcls(g.days)}">${hlabel(g.days)}</span><span class="st st-${g.status}">${ST[g.status]}</span><span class="sp"></span><span class="go">${op?"Tutup rincian ▴":"Lihat rincian ▾"}</span></div>
      <div class="meta">${g.ws} · ${g.style} · ${g.color} · ${g.dest} · Shipment ${g.ship}</div>
      <div class="nums"><div>Qty PO<b>${fmt(g.qpo)}</b></div><div>${c.lbl}<b>${fmt(g[k])}</b></div><div class="kur">Kurang<b>${kur>0?fmt(kur):"0"}</b></div>
        <div>Progres<div class="prog" style="margin-top:4px"><div class="bar"><i style="width:${p}%;background:${pc}"></i></div><small style="color:${pc}">${p}%</small></div></div></div>
      ${dKey==="po"?`<div class="meta" style="margin-top:8px">Transfer Garment ${fmt(g.qtr)} · Packing In ${fmt(g.pin)} · Packing Out ${fmt(g.pout)}</div>`:""}
      ${op?det:`<div class="sz">${sz}</div>`}
    </div>`}).join("") || `<div class="empty">${dq||dr?"Tidak ada PO yang cocok dengan pencarian / filter":"Semua PO sudah lengkap 🎉"}</div>`;
}
function dGo(p){dPage=p;drawDrawer();document.getElementById("dB").scrollTop=0}
let dQT;
document.getElementById("dQ").addEventListener("input",()=>{clearTimeout(dQT);dQT=setTimeout(()=>{dPage=1;drawDrawer()},200)});
document.getElementById("dRange").addEventListener("change",()=>{dPage=1;drawDrawer()});
// tab "Yang masih kurang / Semua PO" -> kembali ke halaman 1
function dTab(v){dOnly=v;dPage=1;drawDrawer()}
function togPO(po){dOpen.has(po)?dOpen.delete(po):dOpen.add(po);drawDrawer()}
function drawAlert(){
  const box=document.getElementById("alertBox");
  box.classList.toggle("mepet-loading",OPEN_LOADING);
  if(OPEN_LOADING){
    box.style.display="";
    const sp='<span class="mini-spin"></span>';
    document.getElementById("aN").innerHTML=sp;document.getElementById("aQ").innerHTML=sp;
    return;
  }
  const m=MEPET();
  box.style.display=m.length?"":"none";if(!m.length)return;
  document.getElementById("aN").textContent=m.length;
  document.getElementById("aQ").textContent=fmt(m.reduce((x,g)=>x+g.qpo-g.pout,0));
  const tk=document.getElementById("aTk");
  tk.innerHTML=m.map(g=>`<span class="ti"><em>${hlabel(g.days)}</em>PO ${g.po} · ${g.style} ${g.color} · kurang ${fmt(g.qpo-g.pout)} pcs</span>`).join("");
  const TICK_SPEED=100;
  tk.style.animationDuration=Math.max(15,tk.scrollWidth/TICK_SPEED)+"s";
}
drawChips(); kpis(); drawAlert(); loadOpsi(); loadData(); loadOpen();

// ===== Panduan: sorot elemen asli + tur langkah demi langkah (pola sama seperti Packing List) =====
const gEl=document.getElementById("soGuide"), gBs=bootstrap.Modal.getOrCreateInstance(gEl);
const TOUR_KEY="mso_new_tour_seen_{{ Auth::id() }}";
const STEPS=[
  {sel:"#soGuideBtn",sec:"sg-peta",lbl:"Panduan",d:"Tombol <b>Panduan</b> membuka halaman bantuan ini kapan saja."},
  {sel:"#upWrap",sec:"sg-upload",lbl:"Upload Master SO PPIC",d:"Klik untuk membuka, upload file Excel, cek preview. <b>Simpan</b> aktif kalau semua baris berstatus Ok."},
  {sel:"#alertBox",sec:"sg-notif",lbl:"PO mendekati tanggal shipment",d:"PO <b>Open</b> dengan shipment 7 hari lagi atau kurang dan Packing Out belum lengkap. Klik untuk rincian per PO."},
  {sel:"#kpis",sec:"sg-kpi",lbl:"Kartu KPI",d:"Total qty PO Open yang Packing Out-nya belum lengkap, per tahap. Klik kartu untuk rincian per PO."},
  {sel:"#mso .filters",sec:"sg-filter",lbl:"Filter",d:"Pilih Status Order, Tgl Shipment, WS, Style, atau No. PO, lalu klik <b>Cari</b>."},
  {sel:"#mso .f .acts",sec:"sg-tombol",lbl:"Cari, Export & Edit Multiple",d:"<b>Cari</b> menampilkan data · <b>Export</b> Excel sesuai filter · <b>Edit Multiple</b> ubah / hapus satu PO sekaligus."},
  {sel:"#mso .tw",sec:"sg-tabel",lbl:"Tabel List Master",d:"Qty per tahap dari PO sampai Packing Out, progres, dan status order. Baris ketiga judul tabel untuk mencari per kolom."},
  {sel:"#tb tr:first-child .ab",fb:"#mso .tw",sec:"sg-edit",lbl:"Edit per baris",d:"Ubah <b>Tgl Shipment</b> atau <b>Qty PO</b> satu baris. Nilai lama ikut tercatat."},
];
let spotTok=0, spotIdx=-1, tourFirst=false;
function gGo(id,smooth){const t=gEl.querySelector(id);if(t)t.scrollIntoView({behavior:smooth?"smooth":"auto",block:"start"})}
function spotClear(){
  spotTok++;
  document.querySelectorAll(".sg-spot,.sg-spot-l,.sg-spot-cv").forEach(x=>x.remove());
  removeEventListener("scroll",spotOff,true);removeEventListener("resize",spotOff);
}
// Tutup tur & kembali ke panduan di bagian langkah terakhir yang dilihat
function spotOff(){
  const s=STEPS[spotIdx], first=tourFirst;
  spotClear();spotIdx=-1;
  if(first){tourFirst=false;try{localStorage.setItem(TOUR_KEY,"1")}catch(e){}}
  gEl.addEventListener("shown.bs.modal",()=>gGo(first||!s?"#sg-peta":"#"+s.sec),{once:true});
  gBs.show();
}
const visibleEl=sel=>sel?[...document.querySelectorAll(sel)].find(x=>x.offsetParent!==null):null;
function spotGo(i,dir){
  dir=dir||1;
  if(i<0||i>=STEPS.length)return;
  const s=STEPS[i], n=STEPS.length;
  const target=visibleEl(s.sel)||visibleEl(s.fb);
  // elemen tidak tampil (mis. notif PO mepet tidak ada): lewati ke langkah berikut / sebelumnya
  if(!target){const j=i+dir;if(j>=0&&j<n){spotGo(j,dir);return}if(spotIdx<0)spotIdx=i;spotOff();return}
  spotClear();spotIdx=i;
  const tok=spotTok;
  if(document.activeElement)document.activeElement.blur();
  // scroll halaman saja (bukan scrollIntoView): scrollIntoView ikut menggeser isi kotak ber-overflow:hidden
  // seperti header .ph (lingkaran dekorasinya melebihi kotak), sehingga teks header bergeser ke atas
  {const r0=target.getBoundingClientRect();
   window.scrollTo({top:Math.max(0,scrollY+r0.top-Math.max(16,(innerHeight-r0.height)/2)),behavior:"smooth"})}
  // tunggu scroll berhenti baru pasang sorotan
  let last=null,still=0;const t0=performance.now();
  (function wait(){
    if(tok!==spotTok)return;
    const top=target.getBoundingClientRect().top;
    still=last!==null&&Math.abs(top-last)<0.5?still+1:0;last=top;
    if(still>=6||performance.now()-t0>2000)draw();else requestAnimationFrame(wait);
  })();
  function draw(){
    const r=target.getBoundingClientRect(), pad=6;
    const cv=document.createElement("div");cv.className="sg-spot-cv";cv.onclick=spotOff;
    const box=document.createElement("div");box.className="sg-spot";
    Object.assign(box.style,{left:r.left-pad+"px",top:r.top-pad+"px",width:r.width+pad*2+"px",height:r.height+pad*2+"px"});
    const l=document.createElement("div");l.className="sg-spot-l";
    l.innerHTML=`<div class="st"><span>Langkah ${i+1} dari ${n}</span><span class="dots">${STEPS.map((_,k)=>`<i class="${k===i?"on":k<i?"done":""}"></i>`).join("")}</span></div>
      <b>${esc(s.lbl)}</b><span class="d">${s.d}</span>
      <div class="nav"><button type="button" data-nav="prev" ${i?"":"disabled"}>‹ Sebelumnya</button>
        <button type="button" data-nav="next" class="pri">${i===n-1?"Selesai ✓":"Berikutnya ›"}</button></div>
      <small><kbd>←</kbd><kbd>→</kbd> pindah langkah · <kbd>Esc</kbd>, scroll, atau klik area gelap untuk kembali ke panduan</small>`;
    l.onclick=e=>{const b=e.target.closest("[data-nav]");if(!b)return;
      if(b.dataset.nav==="prev")spotGo(i-1,-1);else if(i===n-1)spotOff();else spotGo(i+1,1)};
    document.body.append(cv,box,l);
    const lh=l.offsetHeight, below=r.bottom+16+lh<innerHeight;
    l.style.left=Math.max(8,Math.min(innerWidth-l.offsetWidth-8,r.left))+"px";
    l.style.top=(below?r.bottom+16:Math.max(8,r.top-lh-16))+"px";
    const nx=l.querySelector('[data-nav="next"]');if(nx)nx.focus({preventScroll:true});
    // mulai dengarkan scroll user setelah sorotan tampil
    setTimeout(()=>{if(tok!==spotTok||!document.querySelector(".sg-spot"))return;addEventListener("scroll",spotOff,true);addEventListener("resize",spotOff)},250);
  }
}
document.getElementById("soGuideBtn").addEventListener("click",e=>{e.stopPropagation();gBs.show()});
// Klik di dalam panduan: nomor/nav -> lompat ke bagian; kotak/tombol "Tunjukkan" -> sorot di halaman
gEl.addEventListener("click",e=>{
  const a=e.target.closest(".sg-map-n,.sg-nav a");
  if(a){e.preventDefault();e.stopPropagation();gGo(a.getAttribute("href"),true);return}
  const b=e.target.closest("[data-spot],[data-tour]");
  if(!b)return;
  const idx=b.dataset.tour!==undefined?0:Math.max(0,STEPS.findIndex(x=>x.sel===b.dataset.spot));
  gEl.addEventListener("hidden.bs.modal",()=>spotGo(idx),{once:true});
  gBs.hide();
});
document.addEventListener("keydown",e=>{
  if(spotIdx<0)return;
  if(e.key==="Escape"){e.preventDefault();e.stopPropagation();spotOff();return}
  if(!document.querySelector(".sg-spot"))return;
  if(e.key==="ArrowRight"){e.preventDefault();spotIdx===STEPS.length-1?spotOff():spotGo(spotIdx+1,1)}
  else if(e.key==="ArrowLeft"){e.preventDefault();spotGo(spotIdx-1,-1)}
},true);
// Tur otomatis saat halaman pertama kali dibuka user ini (per user per browser), setelah data tabel pertama dimuat
let tourChecked=false;
function autoTour(){
  if(tourChecked)return;tourChecked=true;
  let seen=false;try{seen=localStorage.getItem(TOUR_KEY)==="1"}catch(e){seen=true}
  if(seen)return;
  setTimeout(()=>{if(spotIdx<0&&!document.querySelector(".modal.show")&&!Swal.isVisible()){tourFirst=true;spotGo(0)}},600);
}
</script>
@endsection
