{{-- Navbar khusus modul Marketing (gaya prototype proto/marketing-list.html). Dipakai layouts.index bila $page == 'dashboard-marketing';
     navbar modul lain (layouts.navbar) tidak berubah. Tombol tema hanya tampil di halaman yang sudah mendukung tema gelap ($mktTheme). --}}
@php
    $subPage = $subPage ?? '';
    $subPageGroup = $subPageGroup ?? '';
    $mktUser = auth()->user();
    $mktProcess = $subPageGroup == 'marketing-master' && !in_array($subPage, ['marketing-master-costing-approval', 'marketing-master-bom-approval']);
    $mktApproval = $subPageGroup == 'marketing-approval' || in_array($subPage, ['marketing-approval-center', 'marketing-master-costing-approval', 'marketing-master-bom-approval']);
    // [label, sub, url, kata kunci] untuk kotak "Cari menu".
    $mktMenus = [
        ['Dashboard Marketing', 'Marketing', route('dashboard-marketing'), 'dash dashboard ringkasan'],
        ['Marketing List', 'Marketing › Process', route('master-costing'), 'list costing so bom worksheet ws'],
        ['Katalog Style', 'Marketing › Process', route('master-marketing-catalog'), 'katalog style catalog'],
        ['Approval', 'Marketing', route('marketing-approval-center'), 'approve approval persetujuan unlock'],
        ['Panduan Marketing', 'Marketing › Bantuan', route('marketing-panduan'), 'panduan manual bantuan help user guide cara'],
        ['Approve Transfer MEMO', 'Marketing › Document Handover', route('approve-transfer-memo'), 'handover memo approve'],
        ['Transfer MEMO', 'Marketing › Document Handover', route('transfer-memo-to-exim'), 'handover memo transfer exim'],
        ['Costing vs Sales Order Detail (SB)', 'Marketing › Laporan', route('marketing_report_cvs_detail'), 'laporan report cvs costing sales order'],
        ['Home', 'NDS', route('home'), 'home beranda modul utama'],
    ];
    $chev = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>';
@endphp
<header class="mkt-nav" id="mkt-nav">
    <div class="mkt-nav-in">
        <button type="button" class="mkt-hb" id="mkt-hb" aria-label="Buka menu" aria-expanded="false"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg></button>
        <a class="mkt-logo" href="{{ route('home') }}" aria-label="Nirwana Digital Solution, kembali ke Home"><img src="{{ asset('dist/img/nds-logo-nav.png') }}" alt="Nirwana Digital Solution"></a>

        <nav class="mkt-menu" id="mkt-menu" aria-label="Menu Marketing">
            <a href="{{ route('dashboard-marketing') }}" class="{{ request()->routeIs('dashboard-marketing') ? 'on' : '' }}">Dashboard</a>
            <div class="mkt-dd">
                <button type="button" class="{{ $mktProcess ? 'on' : '' }}" aria-expanded="false" aria-haspopup="true">Process {!! $chev !!}</button>
                <div class="mkt-ddm" role="menu" hidden>
                    <a href="{{ route('master-costing') }}" role="menuitem" @if ($subPage == 'marketing-master-costing') aria-current="page" @endif><i><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg></i><span><b>Marketing List</b><small>Costing, SO, BOM dan WS</small></span></a>
                    <a href="{{ route('master-marketing-catalog') }}" role="menuitem" @if ($subPage == 'marketing-master-catalog') aria-current="page" @endif><i><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg></i><span><b>Katalog Style</b><small>Foto dan data style</small></span></a>
                </div>
            </div>
            <a href="{{ route('marketing-approval-center') }}" class="{{ $mktApproval ? 'on' : '' }}">Approval</a>
            <a href="{{ route('marketing-panduan') }}" class="{{ $subPage == 'marketing-panduan' ? 'on' : '' }}">Panduan</a>
            <div class="mkt-dd">
                <button type="button" aria-expanded="false" aria-haspopup="true">Document Handover {!! $chev !!}</button>
                <div class="mkt-ddm" role="menu" hidden>
                    <a href="{{ route('approve-transfer-memo') }}" role="menuitem"><i><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m2 13 4 4L16 7M10 17l2 2 10-10"/></svg></i><span><b>Approve Transfer MEMO</b><small>Setujui memo masuk</small></span></a>
                    <a href="{{ route('transfer-memo-to-exim') }}" role="menuitem"><i><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 2 11 13M22 2l-7 20-4-9-9-4z"/></svg></i><span><b>Transfer MEMO</b><small>Kirim memo ke Exim</small></span></a>
                </div>
            </div>
            <div class="mkt-dd">
                <button type="button" class="{{ $subPageGroup == 'marketing-report' ? 'on' : '' }}" aria-expanded="false" aria-haspopup="true">Laporan {!! $chev !!}</button>
                <div class="mkt-ddm" role="menu" hidden>
                    <a href="{{ route('marketing_report_cvs_detail') }}" role="menuitem" @if ($subPage == 'marketing-report-cvs-detail') aria-current="page" @endif><i><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/></svg></i><span><b>Costing vs Sales Order Detail (SB)</b><small>Laporan perbandingan</small></span></a>
                </div>
            </div>
        </nav>

        <span class="mkt-sp"></span>
        <label class="mkt-sr" id="mkt-sr">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
            <input type="search" placeholder="Cari menu…" aria-label="Cari menu" autocomplete="off">
            <kbd>Ctrl K</kbd>
            <div class="mkt-res" role="listbox" hidden></div>
        </label>
        @if (!empty($mktTheme))
            <button type="button" class="mkt-thm" id="mkt-thm" aria-label="Ganti tema terang atau gelap" title="Ganti tema"><svg class="moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg><svg class="sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg></button>
        @endif
        <a class="mkt-who" data-bs-toggle="offcanvas" href="#user-offcanvas" role="button" aria-controls="user-offcanvas" title="Profil">
            <span>{{ strtoupper(substr($mktUser->name ?: $mktUser->username, 0, 1)) }}</span>
            <b>{{ $mktUser->name ?: $mktUser->username }}</b>
        </a>
        <a class="mkt-out" href="{{ route('logout') }}" title="Log out" aria-label="Log out" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 4h4v16h-4"/><path d="M10 8l-4 4 4 4M6 12h10"/></svg><span>Log out</span></a>
    </div>
</header>
<script>
    (function () {
        var nav = document.getElementById('mkt-nav');
        if (!nav) return;
        var menu = document.getElementById('mkt-menu'), hb = document.getElementById('mkt-hb');

        // Dropdown Process / Document Handover / Laporan.
        var dds = Array.prototype.slice.call(nav.querySelectorAll('.mkt-dd'));
        var pillBack = function () {}; // diisi oleh pil meluncur di bawah
        function closeAll(except) {
            dds.forEach(function (d) {
                if (d === except) return;
                d.querySelector('.mkt-ddm').hidden = true;
                d.querySelector('button').setAttribute('aria-expanded', 'false');
            });
        }
        dds.forEach(function (d) {
            var b = d.querySelector('button'), m = d.querySelector('.mkt-ddm');
            b.addEventListener('click', function (e) {
                e.stopPropagation();
                closeAll(d);
                m.hidden = !m.hidden;
                b.setAttribute('aria-expanded', String(!m.hidden));
                // Pil tetap di tombol dropdown yang terbuka (atau balik ke menu aktif bila ditutup).
                pillBack();
            });
        });
        document.addEventListener('pointerdown', function (e) { if (!nav.contains(e.target)) { closeAll(); setMenu(false); pillBack(); } });

        // Menu HP / layar sempit.
        function setMenu(open) { menu.classList.toggle('open', open); hb.setAttribute('aria-expanded', String(open)); }
        hb.addEventListener('click', function () { setMenu(!menu.classList.contains('open')); });

        // Pil meluncur: pindah ke menu yang di-hover / fokus, kembali ke menu aktif saat mouse keluar.
        (function () {
            menu.classList.add('js');
            var pill = document.createElement('span');
            pill.className = 'mkt-pill';
            pill.setAttribute('aria-hidden', 'true');
            menu.prepend(pill);
            var items = Array.prototype.slice.call(menu.querySelectorAll(':scope > a, :scope > .mkt-dd > button'));
            var act = menu.querySelector(':scope > a.on, :scope > .mkt-dd > button.on');
            function go(el) {
                items.forEach(function (i) { i.classList.toggle('hl', i === el); });
                if (!el || !el.offsetWidth) { pill.style.opacity = 0; return; }
                var r = el.getBoundingClientRect(), p = menu.getBoundingClientRect();
                pill.style.width = r.width + 'px';
                pill.style.height = r.height + 'px';
                pill.style.transform = 'translate(' + (r.left - p.left) + 'px,' + (r.top - p.top) + 'px)';
                pill.style.opacity = 1;
            }
            function back() { go(menu.querySelector('.mkt-dd > button[aria-expanded="true"]') || act); }
            pill.style.transition = 'none';
            go(act);
            requestAnimationFrame(function () { requestAnimationFrame(function () { pill.style.transition = ''; }); });
            items.forEach(function (i) {
                i.addEventListener('mouseenter', function () { go(i); });
                i.addEventListener('focus', function () { go(i); });
            });
            menu.addEventListener('mouseleave', back);
            menu.addEventListener('focusout', function (e) { if (!menu.contains(e.relatedTarget)) back(); });
            pillBack = back;
            window.addEventListener('resize', function () { go(menu.querySelector('.hl') || act); });
            if (document.fonts) document.fonts.ready.then(function () { go(menu.querySelector('.hl') || act); });
        })();

        // Header kaca saat halaman digulir.
        function glass() { nav.classList.toggle('glass', window.scrollY > 8); }
        glass();
        window.addEventListener('scroll', glass, { passive: true });

        // Cari menu (Ctrl K).
        var M = @json($mktMenus);
        var sr = document.getElementById('mkt-sr'), inp = sr.querySelector('input'), box = sr.querySelector('.mkt-res'), sel = 0, cur = [];
        function esc(s) { return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }
        function hl(t, q) { var i = q ? t.toLowerCase().indexOf(q) : -1; return i < 0 ? esc(t) : esc(t.slice(0, i)) + '<mark>' + esc(t.slice(i, i + q.length)) + '</mark>' + esc(t.slice(i + q.length)); }
        function draw() {
            var q = inp.value.trim().toLowerCase();
            cur = M.filter(function (m) { return !q || (m[0] + ' ' + m[1] + ' ' + m[3]).toLowerCase().indexOf(q) >= 0; });
            if (sel >= cur.length) sel = 0;
            box.innerHTML = '<div class="hd">' + (q ? 'Menu' : 'Semua menu') + '</div>' + (cur.length
                ? cur.map(function (m, i) { return '<a href="' + esc(m[2]) + '" role="option" class="' + (i === sel ? 'act' : '') + '" data-i="' + i + '"><b>' + hl(m[0], q) + '</b><small>' + esc(m[1]) + '</small></a>'; }).join('')
                : '<div class="emp">Menu "' + esc(inp.value) + '" tidak ditemukan.</div>');
            box.hidden = false;
        }
        inp.addEventListener('focus', draw);
        inp.addEventListener('input', function () { sel = 0; draw(); });
        inp.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowDown') { e.preventDefault(); sel = Math.min(sel + 1, cur.length - 1); draw(); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); sel = Math.max(sel - 1, 0); draw(); }
            else if (e.key === 'Enter') { e.preventDefault(); if (cur[sel]) location.href = cur[sel][2]; }
            else if (e.key === 'Escape') { box.hidden = true; inp.blur(); }
        });
        inp.addEventListener('blur', function () { setTimeout(function () { box.hidden = true; }, 150); });
        document.addEventListener('keydown', function (e) {
            if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) { e.preventDefault(); inp.focus(); inp.select(); }
            if (e.key === 'Escape') { closeAll(); setMenu(false); pillBack(); }
        });

        // Tema terang / gelap (disimpan bersama halaman Login & Home: localStorage nds-theme).
        var thm = document.getElementById('mkt-thm');
        if (thm) thm.addEventListener('click', function () {
            var r = document.documentElement, n = r.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            r.setAttribute('data-theme', n);
            try { localStorage.setItem('nds-theme', n); } catch (err) {}
        });
    })();
</script>
