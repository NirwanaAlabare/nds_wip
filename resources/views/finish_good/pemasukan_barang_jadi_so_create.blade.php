@extends('layouts.index')

@section('custom-link')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        /* Semua style di-scope ke #pbj supaya tidak bentrok dengan AdminLTE / Bootstrap.
           Kelas yang namanya sama dengan Bootstrap (card, btn, btn-sm, modal, toast, row) diberi prefix pbj-. */
        #pbj {
            /* Warna utama ikut warna standar aplikasi (--sb-color), varian muda/tua diturunkan dari situ */
            --green: var(--sb-color, #082149);
            --green-dk: color-mix(in srgb, var(--green) 80%, #000);
            --green-lt: color-mix(in srgb, var(--green) 9%, #fff);
            --green-bd: color-mix(in srgb, var(--green) 30%, #fff);
            --bg: #eef1f4; --card: #fff; --ink: #1f2933; --muted: #6b7785; --line: #dde2e8;
            --input: #fff; --input-ro: #f3f5f7; --focus: color-mix(in srgb, var(--green) 20%, transparent); --err: #d93848; --err-lt: #fdecee;
            --amber: #b7791f; --amber-lt: #fdf3e1; --blue: #2f80c9; --blue-lt: #eaf3fb;
            color: var(--ink); font-size: 15px;
        }
        #pbj button, #pbj input, #pbj select { font-family: inherit; font-size: 15px; }

        /* ===== PAGE ===== */
        #pbj .head { display: flex; align-items: flex-start; justify-content: flex-start; gap: 16px; flex-wrap: wrap; margin-bottom: 16px; }
        #pbj .head h1 { font-size: 24px; font-weight: 600; margin: 0; }
        #pbj .head p { margin: 0; color: var(--muted); font-size: 14px; }
        /* Kolom BPB # lebih lebar supaya "Otomatis saat simpan" muat satu baris */
        #pbj .doc-id { display: grid; grid-template-columns: 1.5fr 1fr; gap: 10px; margin-bottom: 12px; }
        #pbj .doc-id .pill { padding: 7px 12px; min-width: 0; }
        #pbj .doc-id .pill b { display: block; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        #pbj .pill { background: var(--card); border: 1px solid var(--line); border-radius: 10px; padding: 7px 14px; line-height: 1.2; }
        #pbj .pill small { display: block; font-size: 11.5px; color: var(--muted); text-transform: uppercase; letter-spacing: .4px; }
        #pbj .pill b { font-size: 15px; }
        #pbj .pill.auto b { color: var(--muted); font-weight: 500; font-style: italic; }

        #pbj .grid { display: grid; grid-template-columns: minmax(0, 1fr) 300px; gap: 18px; align-items: start; }

        /* ===== FORM CARD ===== */
        #pbj .pbj-card { background: var(--card); border: 1px solid var(--line); border-radius: 12px; padding: 6px 20px 20px; min-width: 0; }
        #pbj .sec-h { display: flex; align-items: center; gap: 8px; margin: 16px 0 10px; }
        #pbj .sec-h h2 { font-size: 13px; margin: 0; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: var(--muted); }
        #pbj .sec-h i { flex: 1; height: 1px; background: var(--line); }
        #pbj .num { width: 20px; height: 20px; border-radius: 50%; background: #e6eaee; color: var(--muted); font-weight: 700; display: grid; place-items: center; font-size: 11.5px; flex: none; transition: .2s; }
        #pbj .sec.done .num { background: var(--green); color: #fff; }
        #pbj .sec.done .num span { display: none; }
        #pbj .sec.done .num:before { content: "✓"; }
        #pbj .sec.done .sec-h h2 { color: var(--green-dk); }
        #pbj .cnt { font-size: 12px; color: var(--muted); white-space: nowrap; }
        #pbj .sec-b { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px 14px; }
        #pbj .s2 { grid-column: span 2; }
        #pbj .s3 { grid-column: span 3; }
        #pbj .s4 { grid-column: 1/-1; }
        #pbj .sec-b.asal { grid-template-columns: 1.25fr 1.25fr .8fr; }
        #pbj .sec-b.asal .s2 { grid-column: auto; }
        #pbj .hide { display: none !important; }

        /* mode banner */
        #pbj .mode { display: flex; gap: 10px; align-items: flex-start; border-radius: 9px; padding: 9px 12px; font-size: 13.5px; margin-bottom: 12px; line-height: 1.4; }
        #pbj .mode b { font-weight: 700; }
        #pbj .mode.po { background: var(--blue-lt); color: #1d5a8f; }
        #pbj .mode.ws { background: #f3f5f7; color: #46525e; }
        #pbj .mode .ic { font-size: 16px; line-height: 1.2; }

        /* ===== FIELDS ===== */
        #pbj .f { min-width: 0; }
        #pbj .f label { display: block; font-weight: 600; font-size: 13px; margin-bottom: 4px; }
        #pbj .f label .req { color: var(--err); margin-left: 2px; }
        #pbj .f input, #pbj .f select { width: 100%; height: 38px; border: 1px solid #cfd6de; border-radius: 7px; padding: 0 11px; background: var(--input); color: var(--ink); outline: none; transition: border .15s, box-shadow .15s; }
        #pbj .f select { appearance: none; -webkit-appearance: none; background-image: linear-gradient(45deg, transparent 50%, #6b7785 50%), linear-gradient(135deg, #6b7785 50%, transparent 50%); background-position: calc(100% - 17px) 16px, calc(100% - 12px) 16px; background-size: 5px 5px; background-repeat: no-repeat; padding-right: 30px; }
        #pbj .f select.ph { color: #a3adb8; }
        #pbj .f select:disabled { background-color: var(--input-ro); cursor: not-allowed; }
        #pbj .f input:focus, #pbj .f select:focus { border-color: var(--green); box-shadow: 0 0 0 3px var(--focus); }
        #pbj .f input::placeholder { color: #a3adb8; }
        #pbj .f .hint { font-size: 12px; color: var(--muted); margin-top: 3px; }
        #pbj .f .hint:empty { display: none; }
        #pbj .f.err input, #pbj .f.err select, #pbj .f.err .seg, #pbj .f.err .ms, #pbj .fd.err .tbl-wrap { border-color: var(--err); box-shadow: 0 0 0 3px #d9384822; }
        #pbj .f.ok > input, #pbj .f.ok > select { border-color: var(--green-bd); }
        /* Select2 supplier: tinggi & sudut disamakan dengan input lain, ikut tanda error */
        #pbj .select2-container--bootstrap4 .select2-selection--single { height: 38px; border-radius: 7px; }
        #pbj .f.err .select2-selection { border-color: var(--err); box-shadow: 0 0 0 3px #d9384822; }

        /* Select2 WS (multiple): tampilan disamakan dengan desain awal (.ms + .tag + list ber-checkbox) */
        #pbj #wsF .select2-selection--multiple { min-height: 38px; border: 1px solid #cfd6de; border-radius: 7px; padding: 2px 6px; background: #fff; transition: border .15s, box-shadow .15s; }
        #pbj #wsF .select2-container--focus .select2-selection--multiple,
        #pbj #wsF .select2-container--open .select2-selection--multiple { border-color: var(--green); box-shadow: 0 0 0 3px var(--focus); }
        #pbj #wsF .select2-container--disabled .select2-selection--multiple { background: var(--input-ro); cursor: not-allowed; }
        #pbj #wsF .select2-selection__rendered { display: flex; flex-wrap: wrap; gap: 5px; align-items: center; padding: 0; margin: 0; }
        #pbj #wsF .select2-selection__choice { display: inline-flex; flex-direction: row-reverse; align-items: center; gap: 4px; margin: 0; background: var(--green-lt); color: var(--green-dk); border: 1px solid var(--green-bd); border-radius: 6px; padding: 2px 4px 2px 8px; font-size: 13.5px; font-weight: 600; line-height: 1.4; }
        #pbj #wsF .select2-selection__choice__remove { float: none; margin: 0; padding: 0 3px; border: 0; border-radius: 4px; color: var(--green-dk); font-size: 15px; line-height: 1; }
        #pbj #wsF .select2-selection__choice__remove:hover { background: var(--focus); color: var(--green-dk); }
        #pbj #wsF .select2-search--inline { flex: 1; }
        /* Kolom ketik di dalam WS: tanpa kotak sendiri (tadinya ikut style input .f) */
        #pbj #wsF .select2-search--inline .select2-search__field,
        #pbj #wsF .select2-search--inline .select2-search__field:focus { margin: 0; height: 28px !important; min-width: 140px; width: 100% !important; font-size: 15px; padding: 0 4px !important; border: 0 !important; box-shadow: none !important; background: transparent !important; border-radius: 0; outline: none; }
        #pbj #wsF .select2-selection__clear { margin-right: 4px; }
        #pbj #wsF .select2-dropdown { border: 1px solid var(--line); border-radius: 8px; box-shadow: 0 8px 24px #0002; overflow: hidden; margin-top: 4px; }
        #pbj #wsF .select2-results__options { max-height: 260px; }
        #pbj #wsF .select2-results__option { padding: 0; background: #fff; color: var(--ink); }
        #pbj #wsF .select2-results__option--highlighted { background: var(--green-lt); color: var(--ink); }
        #pbj #wsF .select2-results__message { padding: 10px 12px; color: var(--muted); font-size: 14px; }
        #pbj .ws-opt { padding: 8px 12px; display: flex; align-items: center; gap: 10px; cursor: pointer; }
        #pbj .ws-opt .t { flex: none; }
        #pbj .ws-opt small { color: var(--muted); margin-left: auto; text-align: right; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        #pbj .ws-opt .ck { width: 18px; height: 18px; border: 1.5px solid #b5bec8; border-radius: 4px; flex: none; display: grid; place-items: center; font-size: 12px; color: #fff; }
        #pbj #wsF .select2-results__option[aria-selected=true] .ck { background: var(--green); border-color: var(--green); }
        #pbj #wsF .select2-results__option[aria-selected=true] .ck:before { content: "✓"; }

        /* segmented grade */
        #pbj .seg { display: flex; height: 38px; border: 1px solid #cfd6de; border-radius: 7px; overflow: hidden; }
        #pbj .seg .chip { flex: 1; border: 0; border-right: 1px solid #cfd6de; background: #fff; cursor: pointer; font-size: 14px; color: var(--ink); padding: 0 4px; }
        #pbj .seg .chip:last-child { border-right: 0; }
        #pbj .seg .chip:hover { background: var(--green-lt); }
        #pbj .seg .chip.on { background: var(--green); color: #fff; font-weight: 600; }

        /* dropdown lists (supplier & WS) */
        #pbj .combo, #pbj .ms-wrap { position: relative; }
        #pbj .list { position: absolute; z-index: 20; left: 0; right: 0; top: calc(100% + 4px); background: #fff; border: 1px solid var(--line); border-radius: 8px; box-shadow: 0 8px 24px #0002; max-height: 260px; overflow: auto; display: none; }
        #pbj .combo.open .list, #pbj .ms-wrap.open .list { display: block; }
        #pbj .opt-i { padding: 8px 12px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; gap: 10px; }
        #pbj .opt-i small { color: var(--muted); }
        #pbj .opt-i:hover, #pbj .opt-i.act { background: var(--green-lt); }
        #pbj .opt-i .ck { width: 18px; height: 18px; border: 1.5px solid #b5bec8; border-radius: 4px; flex: none; display: grid; place-items: center; font-size: 12px; color: #fff; }
        #pbj .opt-i.sel .ck { background: var(--green); border-color: var(--green); }
        #pbj .opt-i.sel .ck:before { content: "✓"; }
        #pbj .opt-i .t { flex: 1; }
        #pbj .empty { padding: 10px 12px; color: var(--muted); font-size: 14px; }

        /* multi select WS */
        #pbj .ms { min-height: 38px; border: 1px solid #cfd6de; border-radius: 7px; background: #fff; display: flex; flex-wrap: wrap; gap: 5px; align-items: center; padding: 4px 6px; cursor: text; transition: border .15s, box-shadow .15s; }
        #pbj .ms:focus-within { border-color: var(--green); box-shadow: 0 0 0 3px var(--focus); }
        #pbj .ms.dis { background: var(--input-ro); cursor: not-allowed; }
        #pbj .ms input { border: 0 !important; box-shadow: none !important; height: 28px !important; padding: 0 4px !important; flex: 1; min-width: 140px; background: transparent !important; }
        #pbj .tag { display: inline-flex; align-items: center; gap: 4px; background: var(--green-lt); color: var(--green-dk); border: 1px solid var(--green-bd); border-radius: 6px; padding: 2px 4px 2px 8px; font-size: 13.5px; font-weight: 600; }
        #pbj .tag button { border: 0; background: transparent; color: var(--green-dk); cursor: pointer; font-size: 15px; line-height: 1; padding: 0 3px; border-radius: 4px; }
        #pbj .tag button:hover { background: var(--focus); }

        /* ===== DETAIL TABLE ===== */
        #pbj .meth { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 12px; font-size: 13.5px; font-weight: 600; }
        #pbj .meth small { font-weight: 400; color: var(--muted); font-size: 13px; }
        #pbj .mseg { height: 34px; width: 260px; }
        #pbj .mseg .chip { font-weight: 600; }
        #pbj .tb { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; margin-bottom: 10px; }
        #pbj .tb .grow { flex: 1; }
        #pbj .tb .srch { height: 34px; border: 1px solid #cfd6de; border-radius: 7px; padding: 0 10px; width: 220px; outline: none; font-size: 14px; }
        #pbj .tb .srch:focus { border-color: var(--green); box-shadow: 0 0 0 3px var(--focus); }
        #pbj .pbj-btn-sm { height: 34px; border: 1px solid #cfd6de; background: #fff; border-radius: 7px; padding: 0 12px; cursor: pointer; font-size: 13.5px; font-weight: 600; color: var(--ink); white-space: nowrap; }
        #pbj .pbj-btn-sm:hover { border-color: var(--green); color: var(--green-dk); }
        #pbj .pbj-btn-sm.pri { background: var(--green); border-color: var(--green); color: #fff; }
        #pbj .pbj-btn-sm.pri:hover { background: var(--green-dk); color: #fff; }
        #pbj .tgl { display: inline-flex; align-items: center; gap: 6px; font-size: 13.5px; color: #46525e; cursor: pointer; user-select: none; white-space: nowrap; margin: 0; font-weight: 400; }
        #pbj .tgl input { width: 16px; height: 16px; accent-color: var(--green); }
        #pbj .tbl-wrap { border: 1px solid var(--line); border-radius: 10px; overflow: auto; max-height: 540px; }
        #pbj table { border-collapse: separate; border-spacing: 0; width: 100%; font-size: 13.5px; }
        #pbj th { position: sticky; top: 0; z-index: 2; background: #f7f9fa; text-align: left; font-weight: 600; padding: 9px 10px; border-bottom: 1px solid var(--line); white-space: nowrap; font-size: 12.5px; color: #46525e; }
        #pbj td { padding: 7px 10px; border-bottom: 1px solid #eef1f4; white-space: nowrap; }
        #pbj th.r, #pbj td.r { text-align: right; }
        #pbj th.c, #pbj td.c { text-align: center; }
        #pbj tbody tr:hover td { background: #fafbfc; }
        #pbj tr.grp td { background: color-mix(in srgb, var(--green) 6%, #fff); font-size: 13px; color: #2d3b48; border-bottom: 1px solid var(--green-bd); }
        #pbj tr.grp td b { color: var(--green-dk); }
        #pbj tr.grp .meta { color: var(--muted); margin-left: 6px; }
        #pbj tr.grp .pbj-btn-sm { height: 26px; font-size: 12.5px; padding: 0 9px; }
        #pbj tr.dis td, #pbj tbody.dis td { color: #a3adb8; }
        #pbj tr.pick td, #pbj tbody.pick td { background: color-mix(in srgb, var(--green) 5%, #fff); }
        #pbj tr.pick td:first-child, #pbj tbody.pick tr:first-child td:first-child { box-shadow: inset 3px 0 0 var(--green); }
        #pbj tbody.ctn td { border-bottom: 1px solid #f3f5f7; }
        #pbj tbody.ctn tr:last-child td, #pbj tbody.ctn td[rowspan] { border-bottom: 1px solid #d6dce3; }
        #pbj tbody.ctn td.no { font-weight: 700; font-size: 15px; }
        #pbj tbody.clk:hover td { background: color-mix(in srgb, var(--green) 4%, #fff); }
        #pbj tbody.pick:hover td { background: var(--green-lt); }
        #pbj td input[type=checkbox], #pbj th input[type=checkbox] { width: 17px; height: 17px; accent-color: var(--green); cursor: pointer; vertical-align: middle; }
        #pbj td input[type=checkbox]:disabled { cursor: not-allowed; }
        #pbj tr.clk, #pbj tbody.clk { cursor: pointer; }
        #pbj .st { display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 600; border-radius: 99px; padding: 2px 9px; }
        /* Status Full = hijau sukses (bukan --green yang sebenarnya warna tema navy) */
        #pbj .st.full { background: #e6f6ec; color: #1e7d45; }
        #pbj .st.part { background: var(--amber-lt); color: var(--amber); }
        #pbj .mini { display: inline-block; width: 44px; height: 5px; background: #e6eaee; border-radius: 9px; overflow: hidden; vertical-align: middle; }
        #pbj .mini i { display: block; height: 100%; background: currentColor; }
        #pbj .qin { width: 84px; height: 32px; border: 1px solid #cfd6de; border-radius: 6px; text-align: right; padding: 0 8px; outline: none; font-size: 14px; }
        #pbj .qin:focus { border-color: var(--green); box-shadow: 0 0 0 3px var(--focus); }
        #pbj .qin.has { border-color: var(--green-bd); background: color-mix(in srgb, var(--green) 4%, #fff); font-weight: 600; }
        #pbj .qin.over { border-color: var(--err); background: var(--err-lt); color: var(--err); }
        #pbj .qin:disabled { background: var(--input-ro); border-color: #e6eaee; }
        #pbj .ro { display: inline-block; min-width: 40px; padding: 3px 8px; background: var(--input-ro); border-radius: 5px; color: #46525e; }
        #pbj tfoot td { position: sticky; bottom: 0; background: #fff; border-top: 1px solid var(--line); border-bottom: 0; font-weight: 700; padding: 10px; }
        #pbj .empty-d { border: 1.5px dashed #cfd6de; border-radius: 10px; padding: 28px 16px; text-align: center; color: var(--muted); font-size: 14px; }
        #pbj .empty-d b { display: block; color: var(--ink); font-size: 15px; margin-bottom: 2px; }

        /* ===== SIDEBAR ===== */
        #pbj .side { position: sticky; top: 16px; }
        #pbj .panel { background: var(--card); border: 1px solid var(--line); border-radius: 12px; padding: 16px; }
        #pbj .panel h3 { margin: 0 0 10px; font-size: 15px; font-weight: 700; }
        #pbj .bar { height: 8px; background: #e6eaee; border-radius: 99px; overflow: hidden; }
        #pbj .bar i { display: block; height: 100%; width: 0; background: var(--green); transition: width .25s; }
        #pbj .prog { display: flex; justify-content: space-between; font-size: 13px; color: var(--muted); margin: 6px 0 14px; }
        /* Keterangan wajib isi: baris tipis di atas form, warna mengikuti tema */
        #pbj .req-note { margin: 14px 0 -2px; padding: 7px 12px; border-radius: 8px; background: var(--green-lt); color: var(--green); font-size: 13px; }
        #pbj .req-note i { margin-right: 4px; }
        #pbj .req-note span { color: var(--err); font-weight: 700; }
        #pbj .sum { list-style: none; margin: 0; padding: 0; font-size: 13.5px; }
        #pbj .sum li { display: flex; justify-content: space-between; gap: 10px; padding: 6px 0; border-bottom: 1px dashed var(--line); }
        #pbj .sum li:last-child { border: 0; }
        #pbj .sum span { color: var(--muted); white-space: nowrap; }
        #pbj .sum b { text-align: right; font-weight: 600; overflow-wrap: anywhere; }
        #pbj .sum b.empty { color: #b5bec8; font-weight: 400; flex: none; padding: 0; font-size: inherit; }
        #pbj .tot { margin-top: 10px; background: var(--green-lt); border-radius: 9px; padding: 10px 12px; display: flex; justify-content: space-between; align-items: center; }
        #pbj .tot span { font-size: 13px; color: var(--green-dk); }
        #pbj .tot b { font-size: 18px; color: var(--green-dk); }
        #pbj .tot small { display: block; font-size: 12px; font-weight: 500; text-align: right; }
        #pbj .pbj-btn { height: 44px; border: 0; border-radius: 9px; cursor: pointer; font-weight: 600; padding: 0 18px; }
        #pbj .btn-save { width: 100%; background: var(--green); color: #fff; font-size: 16px; margin-top: 14px; }
        #pbj .btn-save:hover { background: var(--green-dk); }
        #pbj .btn-ghost { width: 100%; background: transparent; color: var(--muted); border: 1px solid var(--line); margin-top: 8px; height: 38px; font-size: 14px; }
        #pbj .btn-ghost:hover { color: var(--ink); border-color: #bcc4cd; }
        #pbj .demo-lbl { font-size: 12px; color: var(--muted); margin: 14px 0 -2px; }

        #pbj .mbar { display: none; }

        /* toast & modal */
        #pbj .pbj-toast { position: fixed; left: 50%; bottom: 24px; transform: translate(-50%, 400%); background: #1f2933; color: #fff; padding: 11px 18px; border-radius: 9px; font-size: 14px; transition: transform .25s; z-index: 1070; max-width: calc(100% - 32px); }
        #pbj .pbj-toast.show { transform: translate(-50%, 0); }
        #pbj .pbj-toast.bad { background: var(--err); }
        #pbj .pbj-modal { position: fixed; inset: 0; background: #0007; display: none; align-items: center; justify-content: center; z-index: 1060; padding: 16px; }
        #pbj .pbj-modal.show { display: flex; }
        #pbj .mbox { background: #fff; border-radius: 14px; max-width: 440px; width: 100%; padding: 24px; text-align: center; }
        #pbj .mbox .ic { width: 56px; height: 56px; border-radius: 50%; background: var(--green); color: #fff; font-size: 28px; display: grid; place-items: center; margin: 0 auto 10px; }
        #pbj .mbox h3 { margin: 0 0 4px; font-size: 19px; font-weight: 700; }
        #pbj .mbox p { margin: 0 0 14px; color: var(--muted); font-size: 14px; }
        #pbj .mbox .bpb { font-size: 22px; font-weight: 700; color: var(--green-dk); background: var(--green-lt); border-radius: 8px; padding: 8px; margin-bottom: 8px; }
        #pbj .mbox .pbj-row { display: flex; gap: 10px; }
        #pbj .mbox .pbj-row .pbj-btn { flex: 1; }
        #pbj .btn-sec { background: #eef1f4; color: var(--ink); }

        @media (max-width:1100px) and (min-width:901px) {
            #pbj .sec-b, #pbj .sec-b.asal { grid-template-columns: 1fr 1fr; }
            #pbj .sec-b.asal .f:first-child { grid-column: 1/-1; }
            #pbj .s3 { grid-column: 1/-1; }
        }
        @media (max-width:900px) {
            #pbj { padding-bottom: 70px; }
            #pbj .grid { grid-template-columns: minmax(0, 1fr); }
            #pbj .side { position: static; }
            #pbj .side .btn-save { display: none; }
            #pbj .mbar { display: flex; position: fixed; left: 0; right: 0; bottom: 0; background: #fff; border-top: 1px solid var(--line); padding: 10px 16px; gap: 12px; align-items: center; z-index: 1030; }
            #pbj .mbar .t { flex: 1; font-size: 13px; color: var(--muted); }
            #pbj .mbar .t b { color: var(--ink); font-size: 15px; }
            #pbj .mbar .pbj-btn { background: var(--green); color: #fff; }
            #pbj .pbj-toast { bottom: 84px; }
        }
        @media (max-width:600px) {
            #pbj .s2, #pbj .s3, #pbj .sec-b > .f, #pbj .sec-b.asal .f { grid-column: 1/-1; }
            #pbj .sec-b, #pbj .sec-b.asal { grid-template-columns: 1fr 1fr; }
            #pbj .pbj-card { padding: 4px 14px 16px; }
            #pbj .head h1 { font-size: 21px; }
            #pbj .tb .srch { width: 100%; }
            #pbj .tb .grow { display: none; }
            #pbj .hm { display: none; }
        }
    </style>
@endsection

@section('content')
    <div class="card card-primary">
        <div class="card-header bg-sb d-flex align-items-center">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-box"></i> Pemasukan Barang Jadi SO</h5>
            <a class="btn btn-light btn-sm ms-auto" style="margin-block: -.5rem;" href="{{ route('pemasukan_barang_jadi_so') }}">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="card-body">
            <div id="pbj">

                <div class="grid">
                    <form id="frm" class="pbj-card" autocomplete="off" onsubmit="return false">
                        <div class="req-note"><i class="fas fa-info-circle"></i> Kolom bertanda <span>*</span> wajib diisi.</div>

                        <!-- 1. ASAL BARANG -->
                        <div class="sec" data-sec="1">
                            <div class="sec-h"><span class="num"><span>1</span></span>
                                <h2>Asal Barang</h2><i></i><span class="cnt" data-cnt></span>
                            </div>
                            <div class="sec-b asal">
                                <div class="f" data-req="supplier">
                                    <label>Supplier<span class="req">*</span></label>
                                    <select id="supplier" class="form-control select2bs4" style="width: 100%;">
                                        <option value="">Pilih Supplier</option>
                                        @foreach ($suppliers as $s)
                                            <option value="{{ $s->id_supplier }}">{{ $s->supplier }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="f" data-req="jenisPemasukan">
                                    <label>Jenis Pemasukan<span class="req">*</span></label>
                                    <select id="jenisPemasukan" class="form-control select2bs4" style="width: 100%;">
                                        <option value="">Pilih Jenis Pemasukan</option>
                                        @foreach ($jenisPemasukan as $j)
                                            <option value="{{ $j->nama_trans }}">{{ $j->nama_trans }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="f" data-req="jenisDokumen">
                                    <label>Jenis Dokumen<span class="req">*</span></label>
                                    <select id="jenisDokumen" class="form-control select2bs4" style="width: 100%;">
                                        <option value="">Pilih Jenis Dokumen</option>
                                        @foreach ($jenisDokumen as $d)
                                            <option value="{{ $d->nama_pilihan }}">{{ $d->nama_pilihan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- 2. REFERENSI ORDER -->
                        <div class="sec" data-sec="2">
                            <div class="sec-h"><span class="num"><span>2</span></span>
                                <h2>Referensi Order</h2><i></i><span class="cnt" data-cnt></span>
                            </div>
                            <div class="mode hide" id="modeInfo"></div>
                            <div class="sec-b">
                                <div class="f" id="refF">
                                    <label>Pilih Berdasarkan</label>
                                    <div class="seg" id="refBy">
                                        <button type="button" class="chip" data-v="po">PO</button><button type="button" class="chip" data-v="ws">WS</button>
                                    </div>
                                    <div class="hint" id="refHint"></div>
                                </div>
                                <div class="f hide" data-req="po" id="poF">
                                    <label>PO<span class="req">*</span></label>
                                    <select id="po" class="form-control select2bs4" style="width: 100%;"></select>
                                    <div class="hint" id="poHint"></div>
                                </div>
                                <div class="f s4" data-req="ws" id="wsF">
                                    <label>WS<span class="req">*</span> <span id="wsMulti" style="font-weight:400;color:var(--muted)">(bisa pilih lebih dari satu)</span></label>
                                    <select id="ws" class="form-control select2bs4" multiple style="width: 100%;"></select>
                                </div>
                                <div class="f" data-req="invoice">
                                    <label>Invoice<span class="req">*</span></label>
                                    <input id="invoice" placeholder="INV/NAG/2611/0087">
                                </div>
                                <div class="f" data-req="reff">
                                    <label>No. Reff<span class="req">*</span></label>
                                    <input id="reff" placeholder="No. surat jalan">
                                </div>
                                <div class="f" data-req="grade">
                                    <label>Grade<span class="req">*</span></label>
                                    <div class="seg" id="grade">
                                        <button type="button" class="chip" data-v="A">A</button><button type="button" class="chip" data-v="B">B</button><button type="button" class="chip" data-v="C">C</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3. DETAIL -->
                        <div class="sec" data-sec="3">
                            <div class="sec-h"><span class="num"><span>3</span></span>
                                <h2 id="detTitle">Detail Barang</h2><i></i><span class="cnt" data-cnt></span>
                            </div>
                            <div class="fd" data-req="detail" id="detail"></div>
                        </div>
                    </form>

                    <aside class="side">
                        <div class="doc-id">
                            <div class="pill auto"><small>BPB #</small><b id="bpbNo">Otomatis saat simpan</b></div>
                            <div class="pill"><small>BPB Date</small><b id="bpbDate">{{ date('d M Y') }}</b></div>
                        </div>
                        <div class="panel">
                            <h3>Ringkasan</h3>
                            <div class="bar"><i id="barFill"></i></div>
                            <div class="prog"><span id="progTxt"></span><span id="progPct">0%</span></div>
                            <ul class="sum" id="sum"></ul>
                            <div class="tot" id="tot"></div>
                            <button class="pbj-btn btn-save" id="btnSave" type="button">Simpan</button>
                            <button class="pbj-btn btn-ghost" id="btnReset" type="button">Kosongkan form</button>
                        </div>
                    </aside>
                </div>

                <div class="mbar">
                    <div class="t"><b id="mProg">0/0</b> wajib terisi</div>
                    <button class="pbj-btn" id="btnSaveM" type="button">Simpan</button>
                </div>

                <div class="pbj-modal" id="modal">
                    <div class="mbox">
                        <div class="ic">✓</div>
                        <h3>Data berhasil disimpan</h3>
                        <p>Pemasukan barang jadi tercatat dengan nomor:</p>
                        <div class="bpb" id="mBpb"></div>
                        <p id="mInfo" style="margin-bottom:16px"></p>
                        <div class="pbj-row">
                            <button class="pbj-btn btn-sec" id="mClose" type="button">Lihat data</button>
                            <button class="pbj-btn btn-save" style="margin:0" id="mNew" type="button">Input baru</button>
                        </div>
                    </div>
                </div>
                <div class="pbj-toast" id="toast"></div>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        // Dibungkus IIFE supaya $ / $$ lokal di sini tidak menimpa jQuery milik layout
        (function() {
            const $ = s => document.querySelector(s);
            const $$ = s => [...document.querySelectorAll(s)];
            const fmt = n => n.toLocaleString("id-ID");
            const esc = s => String(s).replace(/[&<>"]/g, c => ({"&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;"}[c]));

            // Supplier NAG = id_supplier 435 (sama dengan isNag() di controller)
            const isNagSup = () => state.supplierId === "435";
            const DOC_SUGGEST = {"Hasil Produksi": "INHOUSE", "Hasil Perbaikan Produksi": "INHOUSE", "Pembelian": "BC 4.0", "Retur dari Subkontraktor CMT": "BC 2.6.2", "Retur dari Subkontraktor Jasa": "BC 2.6.2"};

            // Alur NAG: info WS (kode, style, color, size) & baris qty per id_cost, diisi dari server (buildNag)
            const WS = {};
            const SCANLINES = {};

            // Alur NAG: bangun SCANLINES dari data server (get_detail_by_ws, satu baris per PO + so_det, tanpa karton).
            // Qty PO = total packing list, Sudah Discan = jumlah scan packing out, Sudah Masuk = total BPB FG per so_det.
            function buildNag(rows) {
                for (const w of state.ws) {
                    const [code, ...rest] = String(WS_TEXT[w] || w).split("|");
                    WS[w] = {code, style: rest.join("|"), colors: [], sizes: []}; SCANLINES[w] = [];
                }
                const sl = {};
                for (const r of rows) {
                    const w = String(r.id_cost), d = WS[w]; if (!d) continue;
                    d.style = r.styleno || d.style;
                    if (!d.colors.includes(r.color)) d.colors.push(r.color);
                    if (!d.sizes.includes(r.size)) d.sizes.push(r.size);
                    // Satu baris per PO + WS + color + size (pono = teks PO, po = qty PO dari packing list)
                    const k = w + "|" + r.po + "|" + r.color + "|" + r.size;
                    if (!sl[k]) { sl[k] = {id: "P|" + k, ws: w, pono: r.po, color: r.color, size: r.size, po: 0, scan: 0, masuk: 0, bal: 0}; SCANLINES[w].push(sl[k]); }
                    sl[k].po += Number(r.qty_pl) || 0; sl[k].scan += Number(r.qty_scan) || 0; sl[k].masuk += Number(r.qty_masuk) || 0;
                    // Data lama bisa sudah masuk > sudah discan (sisa minus): angka tetap ditampilkan, baris dianggap habis
                    sl[k].bal = sl[k].scan - sl[k].masuk;
                }
            }
            const wsCode = w => WS[w]?.code || w; // kode WS untuk ditampilkan

            /* =========================================================
               STATE
               ========================================================= */
            const state = {supplier: "", supplierId: "", jenisPemasukan: "", jenisDokumen: "", po: "", ws: [], invoice: "", reff: "", grade: "", refBy: "ws",
                qty: {}, fullCols: false, q: "",
                lines: [], linesLoading: false}; // lines = detail so_det dari server untuk WS terpilih
            let mode = "ws"; // "po" = internal + hasil produksi, "ws" = selain itu
            const LABEL = {supplier: "Supplier", jenisPemasukan: "Jenis Pemasukan", jenisDokumen: "Jenis Dokumen", po: "PO", ws: "WS", invoice: "Invoice", reff: "No. Reff", grade: "Grade", detail: "Detail"};
            function reqKeys() { return ["supplier", "jenisPemasukan", "jenisDokumen", ...(state.refBy === "po" ? ["po"] : []), "ws", "invoice", "reff", "grade", "detail"]; }
            function totals() {
                if (mode === "po") { let q = 0, over = 0; for (const w of state.ws) for (const r of SCANLINES[w] || []) { const v = state.qty[r.id] || 0; q += v; if (v > Math.max(0, r.bal)) over++; } return {q, over}; }
                let q = 0; for (const r of state.lines) q += state.qty[r.id] || 0; return {q, over: 0}; // non-NAG: tidak dibatasi Bal (sama dengan ERP)
            }
            function filled(k) {
                if (k === "ws") return state.ws.length > 0;
                if (k === "detail") { const t = totals(); return t.q > 0 && !t.over; }
                return !!state[k];
            }

            /* =========================================================
               SUPPLIER (Select2, sama seperti halaman lain)
               ========================================================= */
            const $sup = jQuery("#supplier");
            $sup.select2({theme: "bootstrap4", placeholder: "Pilih Supplier", allowClear: true});
            jQuery(document).on("select2:open", () => document.querySelector(".select2-container--open .select2-search__field")?.focus());
            $sup.on("change", () => {
                const id = $sup.val() || "";
                state.supplierId = id;
                state.supplier = id ? $sup.find("option:selected").text().trim() : "";
                checkMode(); update();
            });

            /* =========================================================
               SELECT, GRADE, TEXT
               ========================================================= */
            function setSel(key, v) {
                const el = $("#" + key); el.value = v; el.classList.toggle("ph", !v); state[key] = v;
                // Kalau select-nya Select2, tampilannya ikut diperbarui (tanpa memicu handler change lagi)
                if (el.classList.contains("select2-hidden-accessible")) jQuery(el).trigger("change.select2");
            }
            function setChip(key, v) { $$("#" + key + " .chip").forEach(x => x.classList.toggle("on", x.dataset.v === v)); state[key] = v; }
            // Jenis Pemasukan pakai Select2 (data dari mastertransaksi FG)
            jQuery("#jenisPemasukan").select2({theme: "bootstrap4", placeholder: "Pilih Jenis Pemasukan", allowClear: true});
            // Jenis Dokumen pakai Select2 (data dari masterpilihan, sesuai status perusahaan)
            jQuery("#jenisDokumen").select2({theme: "bootstrap4", placeholder: "Pilih Jenis Dokumen", allowClear: true});
            ["jenisPemasukan", "jenisDokumen"].forEach(k => {
                setSel(k, "");
                // Pakai jQuery .on: event change dari Select2 tidak sampai ke addEventListener biasa
                jQuery("#" + k).on("change", e => {
                    setSel(k, e.target.value);
                    if (k === "jenisPemasukan") { if (DOC_SUGGEST[e.target.value]) setSel("jenisDokumen", DOC_SUGGEST[e.target.value]); checkMode(); }
                    update();
                });
            });
            $("#grade").addEventListener("click", e => { const c = e.target.closest(".chip"); if (c) { setChip("grade", c.dataset.v); update(); } });
            ["invoice", "reff"].forEach(id => $("#" + id).addEventListener("input", e => { state[id] = e.target.value.trim(); update(); }));

            /* =========================================================
               MODE: PO (internal + hasil produksi) vs WS
               ========================================================= */
            function checkMode() {
                // Supplier NAG (cukup supplier, tanpa syarat jenis pemasukan) -> alur qty per PO
                const m = isNagSup() ? "po" : "ws";
                const info = $("#modeInfo");
                if (state.supplier && state.jenisPemasukan) {
                    info.className = "mode " + m;
                    info.innerHTML = m === "po"
                        ? `<span class="ic">ⓘ</span><div><b>Hasil produksi internal.</b> Pilih berdasarkan <b>PO</b> (semua WS di dalamnya terpilih otomatis) atau langsung <b>WS</b>. Isi <b>qty per size</b> (maksimal sudah discan − sudah masuk).</div>`
                        : `<span class="ic">ⓘ</span><div>Pilih berdasarkan <b>PO</b> (satu PO bisa berisi beberapa WS) atau langsung pilih satu/beberapa <b>WS</b>, lalu isi <b>Qty BPB</b> per baris di tabel detail.</div>`;
                } else info.className = "mode hide";
                if (m === mode) return;
                mode = m; state.refBy = m === "po" ? "po" : "ws"; state.po = ""; state.ws = []; state.qty = {}; state.q = "";
                setupRef(); renderDetail();
            }
            /* ---------- PO & WS (Select2, data dari SB) ----------
               Berdasarkan WS : pilih WS langsung dari act_costing (sama dengan ERP bpb_so.php)
               Berdasarkan PO : Supplier -> PO milik supplier -> WS terfilter dari PO (ERP bpb_so_po.php) */
            const ALL_WS = @json($wsList);
            const WS_TEXT = {}; ALL_WS.forEach(w => WS_TEXT[w.id] = w.text);
            const $po = jQuery("#po"), $ws = jQuery("#ws");
            $po.select2({theme: "bootstrap4", placeholder: "Pilih PO", allowClear: true});
            // WS tetap Select2, tapi tampilannya disamakan dengan desain awal (tag hijau-tema + list ber-checkbox)
            const wsParts = t => { const [code, ...rest] = String(t || "").split("|"); return {code, style: rest.join("|")}; };
            $ws.select2({
                theme: "bootstrap4", placeholder: "Cari & pilih WS", allowClear: true,
                closeOnSelect: false, dropdownParent: jQuery("#wsF"),
                templateSelection: d => wsParts(d.text).code,
                templateResult: d => {
                    if (!d.id) return d.text;
                    const p = wsParts(d.text);
                    return jQuery(`<div class="ws-opt"><span class="ck"></span><span class="t">${esc(p.code)}</span><small>${esc(p.style)}</small></div>`);
                },
            });

            // Isi ulang opsi Select2 tanpa memicu handler change
            function fillSelect($el, items, selected) {
                $el.empty();
                if (!$el.prop("multiple")) $el.append(new Option("", "", false, false));
                items.forEach(it => {
                    WS_TEXT[it.id] = WS_TEXT[it.id] || it.text;
                    const sel = (selected || []).includes(String(it.id));
                    $el.append(new Option(it.text, it.id, sel, sel));
                });
                $el.trigger("change.select2");
            }
            function clearDetailState() { state.qty = {}; state.q = ""; state.lines = []; state.linesLoading = false; }

            function setupRef() {
                const isPo = state.refBy === "po";
                setChip("refBy", state.refBy);
                $("#refHint").textContent = isPo ? "Pilih supplier, lalu PO-nya" : "Pilih WS langsung";
                $("#poF").classList.toggle("hide", !isPo);
                $("#wsF").className = "f " + (isPo ? "s2" : "s3");
                $("#wsMulti").textContent = isPo ? "(otomatis dari PO, bisa dikurangi)" : "(bisa pilih lebih dari satu)";
                $("#poHint").textContent = "";
                state.po = ""; state.ws = [];
                fillSelect($po, []);
                if (isPo) { fillSelect($ws, []); $ws.prop("disabled", true); loadPo(); }
                else loadWs(); // pilih WS: daftar WS per supplier (semua PO)
            }

            // Daftar WS per supplier (NAG: dari packing list, lainnya: dari PO supplier), respons lama diabaikan
            let wsReq = 0;
            function loadWs() {
                const seq = ++wsReq;
                state.ws = []; fillSelect($ws, []); $ws.prop("disabled", true);
                if (!state.supplierId) { $("#refHint").textContent = "Pilih supplier dulu"; return; }
                $("#refHint").textContent = "Memuat WS…";
                jQuery.get("{{ route('pbj_so_get_ws_by_supplier') }}", {id_supplier: state.supplierId}).done(rows => {
                    if (seq !== wsReq || state.refBy !== "ws") return;
                    fillSelect($ws, rows); $ws.prop("disabled", false);
                    $("#refHint").textContent = rows.length ? `${rows.length} WS tersedia` : "Supplier ini tidak punya WS";
                }).fail(() => { if (seq === wsReq) $("#refHint").textContent = "Gagal memuat WS"; });
            }

            // Daftar PO tergantung supplier: NAG -> PO packing out, lainnya -> PO ERP milik supplier
            function loadPo() {
                if (state.refBy !== "po") return;
                state.po = ""; state.ws = []; fillSelect($po, []); fillSelect($ws, []); $ws.prop("disabled", true);
                if (!state.supplierId) { $("#poHint").textContent = "Pilih supplier dulu"; return; }
                $("#poHint").textContent = "Memuat PO…";
                jQuery.get("{{ route('pbj_so_get_po') }}", {id_supplier: state.supplierId}).done(rows => {
                    fillSelect($po, rows);
                    $("#poHint").textContent = rows.length ? `${rows.length} PO tersedia` : "Supplier ini tidak punya PO";
                }).fail(() => { $("#poHint").textContent = "Gagal memuat PO"; });
            }

            // Pilih PO -> ambil WS-nya, semua langsung terpilih (bisa dikurangi)
            $po.on("change", () => {
                state.po = $po.val() || ""; state.ws = []; clearDetailState();
                fillSelect($ws, []); $ws.prop("disabled", true);
                if (!state.po) { $("#poHint").textContent = ""; renderDetail(); update(); return; }
                $("#poHint").textContent = "Memuat WS…";
                jQuery.get("{{ route('pbj_so_get_ws_by_po') }}", {id_po: state.po, id_supplier: state.supplierId}).done(rows => {
                    const ids = rows.map(r => String(r.id));
                    fillSelect($ws, rows, ids); $ws.prop("disabled", false);
                    $("#poHint").textContent = rows.length ? `Berisi ${rows.length} WS` : "PO ini tidak punya WS";
                    setWs(ids);
                }).fail(() => { $("#poHint").textContent = "Gagal memuat WS"; });
            });

            $ws.on("change", () => setWs(($ws.val() || []).map(String)));

            // WS berubah -> ambil detail so_det dari server (sekali per perubahan, respons lama diabaikan)
            let linesReq = 0;
            function setWs(ids) {
                state.ws = ids; clearDetailState(); state.lines = [];
                ids.forEach(w => { delete SCANLINES[w]; }); // buang data NAG lama (PO/WS sebelumnya)
                const seq = ++linesReq;
                state.linesLoading = ids.length > 0;
                renderDetail(); update();
                if (!state.linesLoading) return;
                const params = {ws_ids: ids, id_supplier: state.supplierId}; // NAG: data packing out, lainnya: ERP
                if (state.refBy === "po" && state.po) params.po = state.po; // batasi detail ke PO terpilih
                jQuery.get("{{ route('pbj_so_get_detail_by_ws') }}", params).done(rows => {
                    if (seq !== linesReq) return;
                    if (mode === "po") { buildNag(rows); return; } // NAG: qty per PO + color + size
                    state.lines = rows.map(r => ({
                        ...r, id: String(r.id), id_cost: String(r.id_cost),
                        qty: Number(r.qty) || 0,
                        bal: (Number(r.qtyprev) || 0) - (Number(r.qtyout) || 0),
                    }));
                }).fail(() => {
                    if (seq === linesReq) toast("Gagal memuat detail barang", true);
                }).always(() => {
                    if (seq !== linesReq) return;
                    state.linesLoading = false; renderDetail(); update();
                });
            }

            $("#refBy").addEventListener("click", e => {
                const c = e.target.closest(".chip"); if (!c || c.dataset.v === state.refBy) return;
                state.refBy = c.dataset.v; clearDetailState();
                setupRef(); renderDetail(); update();
            });
            // Ganti supplier -> daftar PO dimuat ulang (NAG: PO packing out, lainnya: PO ERP)
            // Pilih WS langsung -> daftar WS dimuat ulang per supplier (NAG: packing list, lainnya: PO supplier)
            $sup.on("change", () => {
                // Ganti supplier -> daftar PO / WS dimuat ulang sesuai supplier, pilihan WS lama dibuang
                clearDetailState();
                if (state.refBy === "po") loadPo(); else loadWs();
                renderDetail(); update();
            });

            /* =========================================================
               DETAIL
               ========================================================= */
            const det = $("#detail");
            function renderDetail() {
                if (!state.ws.length) {
                    det.innerHTML = `<div class="empty-d"><b>Belum ada data</b>${state.refBy === "po" ? "Pilih PO untuk menampilkan" : "Pilih WS untuk menampilkan"} detail barang.</div>`;
                    return;
                }
                if (mode !== "po") {
                    if (state.linesLoading) { det.innerHTML = `<div class="empty-d"><b>Memuat detail…</b>Mengambil data SO untuk ${state.ws.length} WS.</div>`; return; }
                    if (!state.lines.length) { det.innerHTML = `<div class="empty-d"><b>Tidak ada detail</b>WS terpilih tidak punya baris SO.</div>`; return; }
                    renderLines(); return;
                }
                // Alur NAG: langsung qty per size, data dari packing list + packing out scan
                if (state.linesLoading) { det.innerHTML = `<div class="empty-d"><b>Memuat detail…</b>Mengambil data packing untuk ${state.ws.length} WS.</div>`; return; }
                if (state.ws.some(w => !SCANLINES[w])) { det.innerHTML = `<div class="empty-d"><b>Tidak ada data</b>Gagal memuat data packing.</div>`; return; }
                renderPoQty();
            }
            function syncTitle() { $("#detTitle").textContent = mode !== "po" ? "Detail Barang" : "Detail Qty"; }
            function match(o) { const q = state.q.toLowerCase(); return !q || Object.values(o).join(" ").toLowerCase().includes(q); }

            /* ---- Alur PO: qty manual ---- */
            function renderPoQty() {
                let body = "";
                for (const w of state.ws) {
                    const d = WS[w];
                    d.colors.forEach(color => {
                        const rows = (SCANLINES[w] || []).filter(r => r.color === color && match({pono: r.pono, ws: wsCode(w), style: d.style, color: r.color, size: r.size}));
                        if (!rows.length) return;
                        const sisa = rows.reduce((a, r) => a + Math.max(0, r.bal), 0); // sisa minus (data lama) tidak mengurangi
                        body += `<tr class="grp"><td colspan="9"><b>${esc(wsCode(w))}</b><span class="meta">${d.style} · ${color} · bisa masuk ${fmt(sisa)} pcs</span></td>
                            <td class="r">${sisa ? `<button type="button" class="pbj-btn-sm" data-fill="${w}|${color}">Isi = Sisa</button>` : ""}</td></tr>`;
                        body += rows.map(r => {
                            const v = state.qty[r.id] ?? "";
                            return `<tr><td>${esc(r.pono)}</td><td>${esc(wsCode(w))}</td><td class="hm">${esc(d.style)}</td><td>${r.color}</td><td><b style="font-weight:600">${r.size}</b></td>
                                <td class="r">${fmt(r.po)}</td><td class="r">${fmt(r.scan)}</td><td class="r">${fmt(r.masuk)}</td>
                                <td class="r"><b style="font-weight:600;color:${r.bal > 0 ? 'var(--green-dk)' : 'var(--muted)'}">${fmt(r.bal)}</b></td>
                                <td class="r"><input class="qin${v ? ' has' : ''}${v > r.bal ? ' over' : ''}" type="number" min="0" max="${Math.max(0, r.bal)}" data-q="${r.id}" data-bal="${Math.max(0, r.bal)}" value="${v}" ${r.bal > 0 ? "" : "disabled placeholder='Habis'"}></td></tr>`;
                        }).join("");
                    });
                }
                if (!body) body = `<tr><td colspan="10" style="color:var(--muted)">Tidak ada baris yang cocok</td></tr>`;
                const all = state.ws.flatMap(w => SCANLINES[w] || []), t = totals();
                det.innerHTML = `
                    <div class="tb">
                        <input class="srch" id="dq" placeholder="Cari PO, WS, style, color, size…" value="${esc(state.q)}">
                        <span class="grow"></span>
                        <button type="button" class="pbj-btn-sm pri" id="fillAll">Isi semua = Sisa</button>
                        <button type="button" class="pbj-btn-sm" id="clrAll">Kosongkan qty</button>
                    </div>
                    <div class="tbl-wrap"><table>
                        <thead><tr><th>PO</th><th>WS</th><th class="hm">Style</th><th>Color</th><th>Size</th><th class="r">Qty PO</th><th class="r">Sudah Discan</th><th class="r">Sudah Masuk</th><th class="r" title="Sudah discan − sudah masuk">Sisa Bisa Masuk</th><th class="r">Qty BPB</th></tr></thead>
                        <tbody>${body}</tbody>
                        <tfoot><tr><td colspan="5" id="ftL">${t.over ? `<span style="color:var(--err)">${t.over} baris melebihi sisa</span>` : "Total"}</td>
                            <td class="r">${fmt(all.reduce((a, r) => a + r.po, 0))}</td><td class="r">${fmt(all.reduce((a, r) => a + r.scan, 0))}</td><td class="r">${fmt(all.reduce((a, r) => a + r.masuk, 0))}</td><td class="r">${fmt(all.reduce((a, r) => a + r.bal, 0))}</td><td class="r" id="ftQ">${fmt(t.q)}</td></tr></tfoot>
                    </table></div>`;
                bindSearch();
                $("#fillAll").onclick = () => { all.forEach(r => { if (r.bal > 0) state.qty[r.id] = r.bal; }); renderDetail(); update(); };
                $("#clrAll").onclick = () => { all.forEach(r => delete state.qty[r.id]); renderDetail(); update(); };
                det.querySelectorAll("[data-fill]").forEach(b => b.onclick = () => {
                    const [w, c] = b.dataset.fill.split("|"); SCANLINES[w].filter(r => r.color === c && r.bal > 0).forEach(r => state.qty[r.id] = r.bal);
                    renderDetail(); update();
                });
                bindQty("melebihi sisa", "Maksimal");
            }

            /* ---- Alur WS: baris SO ---- */
            function renderLines() {
                const F = state.fullCols;
                const head = `<th class="hm">SO</th><th class="hm">WS</th><th class="hm">Style</th><th class="hm">Product</th>${F ? "<th class=hm>Product Desc</th><th class=hm>Buyer PO</th>" : ""}<th class="hm">Dest</th><th>Color</th><th>Size</th>${F ? "<th>Reff No</th><th>Style No Prod</th><th>SKU</th>" : ""}<th class="r">Qty SO</th><th class="hm">Unit SO</th><th class="r" title="Sisa yang belum masuk">Bal</th><th class="r">Qty BPB</th>`;
                const ncol = F ? 16 : 11;
                const e = v => esc(v ?? "");
                // Kelompokkan per WS + color (urutan dari server: kpno, color)
                const groups = [];
                for (const r of state.lines) {
                    // Semua kolom teks bisa dicari (tanpa angka qty: Qty SO, Bal, Qty BPB)
                    if (!match({so: r.so_no, ws: r.kpno, style: r.styleno, product: r.product_group, desc: r.product_item, po: r.buyerno, dest: r.dest, color: r.color, size: r.size, reff: r.reff_no, prod: r.styleno_prod, sku: r.sku, unit: r.unit})) continue;
                    const key = r.id_cost + "|" + r.color;
                    let g = groups[groups.length - 1];
                    if (!g || g.key !== key) { g = {key, r, rows: []}; groups.push(g); }
                    g.rows.push(r);
                }
                let body = "";
                groups.forEach((g, gi) => {
                    const sisa = g.rows.reduce((a, r) => a + Math.max(0, r.bal), 0);
                    body += `<tr class="grp"><td colspan="${ncol - 1}"><b>${e(g.r.kpno)}</b><span class="meta">${e(g.r.styleno)} · ${e(g.r.color)} · sisa ${fmt(sisa)} pcs</span></td>
                        <td class="r">${sisa ? `<button type="button" class="pbj-btn-sm" data-fill="${gi}">Isi = Bal</button>` : ""}</td></tr>`;
                    body += g.rows.map(r => {
                        const v = state.qty[r.id] ?? "";
                        return `<tr><td class="hm">${e(r.so_no)}</td><td class="hm">${e(r.kpno)}</td><td class="hm">${e(r.styleno)}</td><td class="hm">${e(r.product_group)}</td>${F ? `<td class="hm">${e(r.product_item)}</td><td class="hm">${e(r.buyerno)}</td>` : ""}
                            <td class="hm">${e(r.dest)}</td><td>${e(r.color)}</td><td><b style="font-weight:600">${e(r.size)}</b></td>${F ? `<td>${e(r.reff_no) || "-"}</td><td>${e(r.styleno_prod) || "-"}</td><td>${e(r.sku) || "-"}</td>` : ""}
                            <td class="r">${fmt(r.qty)}</td><td class="hm"><span class="ro">${e(r.unit) || "PCS"}</span></td><td class="r">${r.bal > 0 ? fmt(r.bal) : `<span style="color:var(--muted)">${fmt(r.bal)}</span>`}</td>
                            <td class="r"><input class="qin${v ? ' has' : ''}" type="number" min="0" data-q="${r.id}" value="${v}"></td></tr>`; // sama dengan ERP: Bal hanya info, qty tidak dibatasi
                    }).join("");
                });
                if (!body) body = `<tr><td colspan="${ncol}" style="color:var(--muted)">Tidak ada baris yang cocok</td></tr>`;
                const t = totals(), tq = state.lines;
                const shown = groups.flatMap(g => g.rows); // baris yang lolos filter cari, untuk total footer
                det.innerHTML = `
                    <div class="tb">
                        <input class="srch" id="dq" placeholder="Cari SO, WS, style, PO, color, size, SKU…" value="${esc(state.q)}">
                        <label class="tgl"><input type="checkbox" id="fullCols" ${F ? "checked" : ""}> Tampilkan semua kolom</label>
                        <span class="grow"></span>
                        <button type="button" class="pbj-btn-sm pri" id="fillAll">Isi semua = Bal</button>
                        <button type="button" class="pbj-btn-sm" id="clrAll">Kosongkan qty</button>
                    </div>
                    <div class="tbl-wrap"><table>
                        <thead><tr>${head}</tr></thead>
                        <tbody>${body}</tbody>
                        <tfoot><tr><td colspan="${ncol - 4}" id="ftL">${t.over ? `<span style="color:var(--err)">${t.over} baris melebihi Bal</span>` : "Total"}</td>
                            <td class="r">${fmt(shown.reduce((a, r) => a + r.qty, 0))}</td><td></td><td class="r">${fmt(shown.reduce((a, r) => a + Math.max(0, r.bal), 0))}</td><td class="r" id="ftQ">${fmt(t.q)}</td></tr></tfoot>
                    </table></div>`;
                bindSearch();
                $("#fullCols").onchange = ev => { state.fullCols = ev.target.checked; renderDetail(); };
                $("#fillAll").onclick = () => { tq.forEach(r => { if (r.bal > 0) state.qty[r.id] = r.bal; }); renderDetail(); update(); };
                $("#clrAll").onclick = () => { state.qty = {}; renderDetail(); update(); };
                det.querySelectorAll("[data-fill]").forEach(b => b.onclick = () => {
                    groups[+b.dataset.fill].rows.filter(r => r.bal > 0).forEach(r => state.qty[r.id] = r.bal);
                    renderDetail(); update();
                });
                bindQty("melebihi Bal", "Melebihi Bal");
            }

            // Input qty: hitung ulang total, tandai baris yang melebihi batas, Enter/↑/↓ pindah baris
            function bindQty(overTxt, titleTxt) {
                const ins = [...det.querySelectorAll(".qin:not(:disabled)")];
                ins.forEach((inp, i) => {
                    inp.oninput = () => {
                        const v = parseInt(inp.value, 10), bal = +inp.dataset.bal;
                        if (isNaN(v) || v <= 0) delete state.qty[inp.dataset.q]; else state.qty[inp.dataset.q] = v;
                        inp.classList.toggle("has", v > 0); inp.classList.toggle("over", v > bal);
                        inp.title = v > bal ? `${titleTxt} (${bal})` : "";
                        const t = totals(); $("#ftQ").textContent = fmt(t.q);
                        $("#ftL").innerHTML = t.over ? `<span style="color:var(--err)">${t.over} baris ${overTxt}</span>` : "Total";
                        update();
                    };
                    inp.onkeydown = e => {
                        if (e.key === "ArrowDown") { e.preventDefault(); ins[i + 1]?.focus(); ins[i + 1]?.select(); }
                        if (e.key === "ArrowUp") { e.preventDefault(); ins[i - 1]?.focus(); ins[i - 1]?.select(); }
                    };
                });
            }
            function bindSearch() {
                const dq = $("#dq");
                dq.oninput = () => { state.q = dq.value; const pos = dq.selectionStart; renderDetail(); const n = $("#dq"); n.focus(); n.setSelectionRange(pos, pos); };
            }

            /* =========================================================
               RINGKASAN & PROGRES
               ========================================================= */
            function update() {
                const R = reqKeys(), done = R.filter(filled).length, pct = Math.round(done / R.length * 100);
                $("#barFill").style.width = pct + "%"; $("#progPct").textContent = pct + "%";
                $("#progTxt").textContent = `${done} dari ${R.length} wajib terisi`; $("#mProg").textContent = `${done}/${R.length}`;
                const rows = [["Supplier", state.supplier], ["Jenis Pemasukan", state.jenisPemasukan], ["Jenis Dokumen", state.jenisDokumen]];
                if (state.refBy === "po") rows.push(["PO", state.po ? $po.find("option:selected").text() : ""]);
                rows.push(["WS", state.ws.map(w => (WS_TEXT[w] || w).split("|")[0]).join(", ")], ["Invoice", state.invoice], ["No. Reff", state.reff], ["Grade", state.grade]);
                $("#sum").innerHTML = rows.map(([l, v]) => `<li><span>${l}</span><b class="${v ? '' : 'empty'}">${v ? esc(v) : '—'}</b></li>`).join("");
                const t = totals();
                syncTitle();
                $("#tot").innerHTML = `<span>Total Qty BPB</span><div><b>${fmt(t.q)} pcs</b>${t.over ? `<small style="color:var(--err)">${t.over} baris melebihi ${mode === "po" ? "sisa" : "Bal"}</small>` : ""}</div>`;
                $$("#pbj .sec").forEach(sec => {
                    const reqs = [...sec.querySelectorAll("[data-req]")].map(f => f.dataset.req).filter(k => R.includes(k));
                    const n = reqs.filter(filled).length, cnt = sec.querySelector("[data-cnt]");
                    cnt.textContent = sec.dataset.sec === "3" ? `${fmt(t.q)} pcs` : `${n}/${reqs.length} wajib`;
                    sec.classList.toggle("done", n === reqs.length);
                });
                $$("#pbj [data-req]").forEach(f => { const ok = filled(f.dataset.req); if (ok) f.classList.remove("err"); f.classList.toggle("ok", ok); });
            }

            /* =========================================================
               SIMPAN (alur NAG -> insert bpb SB)
               ========================================================= */
            function toast(msg, bad) { const t = $("#toast"); t.textContent = msg; t.className = "pbj-toast show" + (bad ? " bad" : ""); clearTimeout(t._h); t._h = setTimeout(() => t.className = "pbj-toast", 3000); }
            function save() {
                const R = reqKeys(), miss = R.filter(k => !filled(k));
                $$("#pbj [data-req]").forEach(f => f.classList.toggle("err", miss.includes(f.dataset.req)));
                if (miss.length) {
                    const first = document.querySelector(`#pbj [data-req="${miss[0]}"]`);
                    first.scrollIntoView({behavior: "smooth", block: "center"});
                    const inp = first.querySelector("input:not(:disabled),select"); if (inp && miss[0] !== "detail") setTimeout(() => inp.focus(), 300);
                    const t = totals();
                    const lbl = miss.map(k => k === "detail" ? (t.over ? `Qty BPB melebihi ${mode === "po" ? "sisa" : "Bal"}` : "isi Qty BPB") : LABEL[k]);
                    iziToast.warning({title: "Belum lengkap", message: esc(lbl.join(", ")), position: "topCenter"}); return;
                }
                const lines = [];
                if (mode === "po") {
                    // Alur NAG: kirim qty per baris (PO + WS + color + size), server bagi ke so_det & insert bpb
                    for (const w of state.ws) for (const r of SCANLINES[w] || []) {
                        const q = state.qty[r.id] || 0;
                        if (q > 0) lines.push({po: r.pono, id_cost: r.ws, color: r.color, size: r.size, qty: q});
                    }
                } else {
                    // Alur ERP (s_bpb_so.php): qty per so_det
                    for (const r of state.lines) {
                        const q = state.qty[r.id] || 0;
                        if (q > 0) lines.push({id_so_det: r.id, id_cost: r.id_cost, qty: q});
                    }
                }
                const t = totals(), btns = [$("#btnSave"), $("#btnSaveM")];
                btns.forEach(b => { b.disabled = true; b.textContent = "Menyimpan…"; });
                // Loading selama proses simpan (tidak bisa ditutup sampai selesai)
                Swal.fire({
                    title: "Menyimpan data...",
                    html: `${fmt(t.q)} pcs dari ${state.ws.length} WS`,
                    allowOutsideClick: false, allowEscapeKey: false,
                    didOpen: () => Swal.showLoading(),
                });
                jQuery.ajax({
                    url: "{{ route('pbj_so_store') }}", type: "POST",
                    data: {_token: "{{ csrf_token() }}", id_supplier: state.supplierId, jenis_pemasukan: state.jenisPemasukan, jenis_dokumen: state.jenisDokumen,
                        invoice: state.invoice, reff: state.reff, grade: state.grade, lines},
                }).done(res => {
                    Swal.close();
                    const no = res.bpbno_int;
                    $("#bpbNo").textContent = no; $("#bpbNo").parentElement.classList.remove("auto");
                    $("#mBpb").textContent = no;
                    $("#mInfo").textContent = `${fmt(Number(res.qty) || t.q)} pcs dari ${state.ws.length} WS`;
                    $("#modal").classList.add("show");
                }).fail(xhr => {
                    Swal.close();
                    const j = xhr.responseJSON || {};
                    const msg = j.errors ? Object.values(j.errors).flat().join("<br>") : (j.message || "Gagal menyimpan data");
                    iziToast.error({title: "Gagal simpan", message: msg, position: "topCenter"});
                }).always(() => btns.forEach(b => { b.disabled = false; b.textContent = "Simpan"; }));
            }
            $("#btnSave").onclick = save; $("#btnSaveM").onclick = save;
            // Tutup modal -> detail dimuat ulang supaya Sudah Masuk & sisa terbaru
            $("#mClose").onclick = () => { $("#modal").classList.remove("show"); if (state.ws.length) setWs(state.ws.slice()); };
            $("#mNew").onclick = () => { $("#modal").classList.remove("show"); reset(); };

            /* =========================================================
               RESET
               ========================================================= */
            function reset() {
                Object.assign(state, {supplier: "", supplierId: "", jenisPemasukan: "", jenisDokumen: "", po: "", ws: [], invoice: "", reff: "", grade: "", qty: {}, q: ""});
                ["invoice", "reff"].forEach(id => $("#" + id).value = "");
                $sup.val("").trigger("change.select2");
                $$("#pbj .chip.on").forEach(x => x.classList.remove("on"));
                setSel("jenisPemasukan", ""); setSel("jenisDokumen", "");
                $("#bpbNo").textContent = "Otomatis saat simpan"; $("#bpbNo").parentElement.classList.add("auto");
                $$("#pbj [data-req]").forEach(f => f.classList.remove("err"));
                mode = ""; checkMode(); update(); window.scrollTo({top: 0, behavior: "smooth"});
            }
            $("#btnReset").onclick = reset;

            mode = ""; checkMode(); update();
        })();
    </script>
@endsection
