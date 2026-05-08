@extends('layouts.index', ['navbar' => false, 'footer' => false])

@section('custom-link')
<style>
    body { background: #f0f4fc; margin: 0; }

    /* Hero / Topbar - dynamic background */
    .home-hero {
        background: var(--hero-gradient, linear-gradient(135deg, var(--sb-color, #3a7bd5) 0%, #5b9cf6 100%));
        padding: .8rem 1rem 2.2rem;
        position: sticky;
        top: 0;
        z-index: 10;
        overflow: hidden;
        transition: background .8s ease;
    }

    /* decorative blobs */
    .home-hero::before {
        content: '';
        position: absolute;
        width: 260px; height: 260px;
        border-radius: 50%;
        background: rgba(255,255,255,.07);
        top: -60px; right: -40px;
    }
    .home-hero::after {
        content: '';
        position: absolute;
        width: 150px; height: 150px;
        border-radius: 50%;
        background: rgba(255,255,255,.05);
        bottom: 10px; left: 5%;
    }

    .hero-inner {
        max-width: 1140px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: relative;
        z-index: 2;
        flex-wrap: wrap;
        gap: .75rem;
    }

    /* left: avatar + greeting */
    .hero-left { display: flex; align-items: center; gap: .65rem; }

    .home-avatar {
        width: 40px; height: 40px;
        border-radius: 50%;
        background: rgba(255,255,255,.22);
        backdrop-filter: blur(6px);
        border: 2px solid rgba(255,255,255,.35);
        color: #fff;
        font-weight: 700;
        font-size: 1rem;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }

    .hero-greeting .sub {
        font-size: .65rem;
        color: rgba(255,255,255,.72);
        letter-spacing: .05em;
        text-transform: uppercase;
        display: block;
    }
    .hero-greeting .name {
        font-size: 1rem;
        font-weight: 700;
        color: #fff;
        line-height: 1.15;
    }

    /* center: logo */
    .hero-logo {
        position: absolute;
        left: 50%; transform: translateX(-50%);
        height: 42px; width: auto;
        object-fit: contain;
        filter: brightness(0) invert(1);
        opacity: .92;
        pointer-events: none;
    }

    /* right: date/time pill */
    .hero-time {
        position: relative;
        z-index: 3;
        background: rgba(255,255,255,.16);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255,255,255,.28);
        border-radius: 26px;
        padding: .35rem .9rem;
        font-size: .72rem;
        color: rgba(255,255,255,.9);
        white-space: nowrap;
    }
    .hero-time .clock {
        font-weight: 700;
        color: #fff;
        margin-left: .3rem;
    }

    .hero-right {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: .5rem;
        position: relative;
        z-index: 2;
    }

    /* ══════════════════════════════════════
       MAIN CONTENT (overlaps hero)
    ══════════════════════════════════════ */
    .home-content {
        max-width: 1140px;
        margin: 0 auto;
        padding: 1rem 1rem 2.25rem;
        position: relative;
        z-index: 2;
    }

    /* ── Search bar ── */
    .search-wrap {
        position: sticky;
        top: 0;
        z-index: 9;
        margin-bottom: 1rem;
        background: #f0f4fc;
        padding-top: .5rem;
        padding-bottom: .5rem;
        box-shadow: 0 8px 20px rgba(15,23,42,.06);
    }

    .search-wrap .search-icon {
        position: absolute;
        left: 1.1rem; top: 50%;
        transform: translateY(-50%);
        color: #aaa; font-size: .9rem;
        pointer-events: none;
        transition: color .2s;
    }

    #menu-search {
        width: 100%;
        padding: .85rem 1rem .85rem 2.8rem;
        border: 2px solid transparent;
        border-radius: 16px;
        background: #fff;
        font-size: .88rem;
        color: #333;
        box-shadow: 0 4px 20px rgba(0,0,0,.1);
        transition: border-color .2s, box-shadow .2s;
        outline: none;
    }

    #menu-search:focus {
        border-color: var(--sb-color, #3a7bd5);
        box-shadow: 0 4px 20px rgba(58,123,213,.18);
    }

    #menu-search:focus ~ .search-icon { color: var(--sb-color, #3a7bd5); }

    .search-clear {
        position: absolute;
        right: 1rem; top: 50%;
        transform: translateY(-50%);
        background: #e8eaf0;
        border: none; border-radius: 50%;
        width: 24px; height: 24px;
        font-size: .65rem; color: #666;
        display: none; align-items: center; justify-content: center;
        cursor: pointer;
        transition: background .2s;
    }
    .search-clear:hover { background: #d0d3dc; }
    .search-clear.visible { display: flex; }

    .search-empty {
        display: none;
        text-align: center;
        padding: 3rem 0;
        color: #aaa; font-size: .9rem;
    }
    .search-empty i { font-size: 2.5rem; margin-bottom: .75rem; display: block; }

    /* ── Grid ── */
    .menu-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(145px, 1fr));
        gap: .85rem;
    }

    /* ── Card ── */
    .menu-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: .65rem;
        padding: 1.75rem .75rem 1.4rem;
        background: #fff;
        border-radius: 20px;
        text-decoration: none;
        border: 1.5px solid #eaecf4;
        box-shadow: 0 2px 10px rgba(0,0,0,.06);
        transition: transform .22s cubic-bezier(.34,1.56,.64,1),
                    box-shadow .22s ease,
                    border-color .22s ease;
        position: relative;
        overflow: hidden;
        animation: cardIn .4s ease both;
    }

    .menu-card.filtered-out { display: none !important; }

    /* shimmer line top */
    .menu-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--sb-color, #3a7bd5), #7cb0ff);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform .3s ease;
    }

    .menu-card:hover {
        transform: translateY(-6px) scale(1.01);
        box-shadow: 0 16px 36px rgba(58,123,213,.14);
        border-color: rgba(58,123,213,.2);
    }
    .menu-card:hover::before { transform: scaleX(1); }

    .menu-card:active {
        transform: translateY(-2px) scale(.97) !important;
        transition-duration: .08s !important;
    }

    /* ── Image wrap ── */
    .menu-img-wrap {
        width: 88px; height: 88px;
        border-radius: 20px;
        background: linear-gradient(135deg, #f0f4ff 0%, #e8f0fe 100%);
        display: flex; align-items: center; justify-content: center;
        transition: background .25s ease, transform .25s cubic-bezier(.34,1.56,.64,1);
        flex-shrink: 0;
    }

    .menu-card:hover .menu-img-wrap {
        background: linear-gradient(135deg, #dce8ff 0%, #c8daff 100%);
        transform: scale(1.1) rotate(-2deg);
    }

    .menu-img-wrap img {
        width: 58px; height: 58px;
        object-fit: contain;
        transition: filter .25s ease;
    }

    /* ── Label ── */
    .menu-label {
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #555;
        text-align: center;
        line-height: 1.35;
        transition: color .2s;
    }
    .menu-card:hover .menu-label { color: var(--sb-color, #3a7bd5); }

    /* Logout variant */
    .menu-card.logout-card .menu-img-wrap {
        background: linear-gradient(135deg, #fff0f1 0%, #ffe4e6 100%);
    }
    .menu-card.logout-card:hover {
        box-shadow: 0 16px 36px rgba(220,53,69,.14);
        border-color: rgba(220,53,69,.2);
    }
    .menu-card.logout-card::before { background: linear-gradient(90deg, #dc3545, #ff7b86); }
    .menu-card.logout-card:hover .menu-label { color: #dc3545; }
    .menu-card.logout-card .ripple { background: rgba(220,53,69,.12); }

    /* ── Ripple ── */
    .ripple {
        position: absolute; border-radius: 50%;
        background: rgba(58,123,213,.13);
        transform: scale(0);
        animation: rippleAnim .6s ease-out forwards;
        pointer-events: none;
    }

    /* ── Warehouse Modal ── */
    #modal-pilih-gudang .modal-content {
        border: none; border-radius: 22px;
        box-shadow: 0 24px 64px rgba(0,0,0,.16);
    }
    #modal-pilih-gudang .modal-header { border-bottom: none; padding-bottom: 0; }
    .whs-menu-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: .85rem; padding: .5rem 0;
    }

    /* ── Keyframes ── */
    @keyframes cardIn {
        from { opacity: 0; transform: translateY(22px) scale(.96); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }
    @keyframes rippleAnim { to { transform: scale(5); opacity: 0; } }
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(10px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .hero-left   { animation: fadeUp .4s ease both; }
    .hero-time   { animation: fadeUp .4s .1s ease both; }

    /* ── Responsive ── */
    @media (max-width: 768px) {
        .hero-logo { display: none; }
    }
    @media (max-width: 576px) {
        .home-hero { padding: 1.2rem 1rem 4.5rem; }
        .menu-grid { grid-template-columns: repeat(3, 1fr); gap: .75rem; }
        .menu-card { padding: 1.2rem .5rem 1rem; }
        .menu-img-wrap { width: 68px; height: 68px; border-radius: 16px; }
        .menu-img-wrap img { width: 44px; height: 44px; }
        .hero-time { display: none; }
        .whs-menu-grid { grid-template-columns: repeat(2, 1fr); }
    }
</style>
@endsection

@section('content')

{{-- Hero / Topbar --}}
<div class="home-hero">
    <div class="hero-inner">
        <div class="hero-left">
            <div class="home-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div class="hero-greeting">
                <span class="sub" id="home-greeting-sub">Selamat datang,</span>
                <span class="name">{{ strtoupper(auth()->user()->name) }}</span>
            </div>
        </div>

        <img src="{{ asset('dist/img/logo-icon.png') }}" alt="Logo" class="hero-logo"
             onerror="this.style.display='none'">

        <div class="hero-right">
            <div class="hero-time">
                <span id="home-date-text"></span>
                <span class="clock" id="home-clock"></span>
            </div>
        </div>
    </div>
</div>

{{-- Main content --}}
<div class="home-content">

    {{-- Search --}}
    <div class="search-wrap">
        <i class="fas fa-search search-icon"></i>
        <input type="text" id="menu-search" placeholder="Cari modul..." autocomplete="off">
        <button class="search-clear" id="search-clear"><i class="fas fa-times"></i></button>
    </div>

    <div class="search-empty" id="search-empty">
        <i class="fas fa-search-minus"></i>
        Modul "<span id="search-keyword"></span>" tidak ditemukan.
    </div>

    {{-- Menu Grid --}}
    <div class="menu-grid" id="menu-grid">

        @role('marker')
        <a href="{{ route('dashboard-marker') }}" class="menu-card">
            <div class="menu-img-wrap"><img src="{{ asset('dist/img/marker.png') }}" alt="marker"></div>
            <span class="menu-label">Marker</span>
        </a>
        @endrole

        @role('cutting')
        <a href="{{ route('dashboard-cutting') }}" class="menu-card">
            <div class="menu-img-wrap"><img src="{{ asset('dist/img/cutting.png') }}" alt="cutting"></div>
            <span class="menu-label">Cutting</span>
        </a>
        @endrole

        @role('stocker')
        <a href="{{ route('dashboard-stocker') }}" class="menu-card">
            <div class="menu-img-wrap"><img src="{{ asset('dist/img/stocker.png') }}" alt="stocker"></div>
            <span class="menu-label">Stocker</span>
        </a>
        @endrole

        @role('dc')
        <a href="{{ route('dashboard-dc') }}" class="menu-card">
            <div class="menu-img-wrap"><img src="{{ asset('dist/img/distribution.jpeg') }}" alt="dc"></div>
            <span class="menu-label">DC</span>
        </a>
        @endrole

        @role('sewing')
        <a href="{{ route('dashboard-sewing-eff') }}" class="menu-card">
            <div class="menu-img-wrap"><img src="{{ asset('dist/img/sewingline.png') }}" alt="sewing"></div>
            <span class="menu-label">Sewing Line</span>
        </a>
        @endrole

        @role('machine')
        <a href="{{ route('dashboard-mut-mesin') }}" class="menu-card">
            <div class="menu-img-wrap"><img src="{{ asset('dist/img/mut_mesin.png') }}" alt="machine"></div>
            <span class="menu-label">Mutasi Mesin</span>
        </a>
        @endrole

        @role('warehouse')
        <a href="#" class="menu-card" onclick="getmodalwarehouse()">
            <div class="menu-img-wrap"><img src="{{ asset('dist/img/warehouse.png') }}" alt="warehouse"></div>
            <span class="menu-label">Warehouse</span>
        </a>
        <a href="{{ route('stock_opname') }}" class="menu-card">
            <div class="menu-img-wrap"><img src="{{ asset('dist/img/stock_opname.png') }}" alt="stock opname"></div>
            <span class="menu-label">Stock Opname</span>
        </a>
        <a href="{{ route('dashboard-qc-inspect') }}" class="menu-card">
            <div class="menu-img-wrap"><img src="{{ asset('dist/img/inspect.png') }}" alt="inspect"></div>
            <span class="menu-label">Fabric Inspection</span>
        </a>
        @endwarehouse

        @php if(auth()->user()->username == 'acc'): @endphp
        <a href="{{ route('stock_opname') }}" class="menu-card">
            <div class="menu-img-wrap"><img src="{{ asset('dist/img/stock_opname.png') }}" alt="stock opname"></div>
            <span class="menu-label">Stock Opname</span>
        </a>
        @php endif; @endphp

        @role('ppic')
        <a href="{{ route('dashboard-ppic') }}" class="menu-card">
            <div class="menu-img-wrap"><img src="{{ asset('dist/img/ppic.png') }}" alt="ppic"></div>
            <span class="menu-label">PPIC</span>
        </a>
        @endrole

        @role('packing')
        <a href="{{ route('dashboard-packing') }}" class="menu-card">
            <div class="menu-img-wrap"><img src="{{ asset('dist/img/packing.png') }}" alt="packing"></div>
            <span class="menu-label">Packing</span>
        </a>
        <a href="{{ route('dashboard_finish_good') }}" class="menu-card">
            <div class="menu-img-wrap"><img src="{{ asset('dist/img/finish_good.png') }}" alt="finish good"></div>
            <span class="menu-label">Finish Good Ekspedisi</span>
        </a>
        @endrole

        @role('admin')
        <a href="{{ route('dashboard-report-doc') }}" class="menu-card">
            <div class="menu-img-wrap"><img src="{{ asset('dist/img/doc_report.png') }}" alt="doc report"></div>
            <span class="menu-label">Document Report</span>
        </a>
        @endrole

        @role('ga')
        <a href="{{ route('dashboard-ga') }}" class="menu-card">
            <div class="menu-img-wrap"><img src="{{ asset('dist/img/general_affair.png') }}" alt="ga"></div>
            <span class="menu-label">G.A.I.S</span>
        </a>
        @endrole

        @role('admin')
        <a href="{{ route('dashboard-marketing') }}" class="menu-card">
            <div class="menu-img-wrap"><img src="{{ asset('dist/img/marketing.png') }}" alt="marketing"></div>
            <span class="menu-label">Marketing</span>
        </a>
        @endrole

        @role('accounting')
        <a href="{{ route('accounting') }}" class="menu-card">
            <div class="menu-img-wrap"><img src="{{ asset('dist/img/accounting_img.png') }}" alt="accounting"></div>
            <span class="menu-label">Accounting</span>
        </a>
        @endrole

        @role('purchasing')
        <a href="{{ route('dashboard-purchasing') }}" class="menu-card">
            <div class="menu-img-wrap"><img src="{{ asset('dist/img/purchasing_img.png') }}" alt="purchasing"></div>
            <span class="menu-label">Purchasing</span>
        </a>
        @endrole

        @role('export_import')
        <a href="{{ route('dashboard-export-import') }}" class="menu-card">
            <div class="menu-img-wrap"><img src="{{ asset('dist/img/exim_img.jpeg') }}" alt="export import"></div>
            <span class="menu-label">Export Import</span>
        </a>
        @endrole

        @role('management')
        <a href="{{ route('dashboard-mgt-report') }}" class="menu-card">
            <div class="menu-img-wrap"><img src="{{ asset('dist/img/management_report.png') }}" alt="mgt report"></div>
            <span class="menu-label">Management Report</span>
        </a>
        <a href="{{ route('dashboard-IE') }}" class="menu-card">
            <div class="menu-img-wrap"><img src="{{ asset('dist/img/IE.png') }}" alt="IE"></div>
            <span class="menu-label">Industrial Engineering</span>
        </a>
        @endrole

        @role('superadmin')
        <a href="{{ route('manage-user') }}" class="menu-card">
            <div class="menu-img-wrap"><img src="{{ asset('dist/img/manage-users.png') }}" alt="manage users"></div>
            <span class="menu-label">Manage User</span>
        </a>
        <a href="{{ route('general-tools') }}" class="menu-card">
            <div class="menu-img-wrap"><img src="{{ asset('dist/img/tools.png') }}" alt="tools"></div>
            <span class="menu-label">General Tools</span>
        </a>
        @endrole

        <a href="{{ route('logout') }}"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
           class="menu-card logout-card">
            <div class="menu-img-wrap"><img src="{{ asset('dist/img/signout.png') }}" alt="logout"></div>
            <span class="menu-label">Logout</span>
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none"
              onsubmit="logout(this, event)">@csrf</form>

    </div>
</div>

{{-- Warehouse Modal --}}
<div class="modal fade" id="modal-pilih-gudang">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold text-sb">Pilih Warehouse</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="whs-menu-grid">
                    <a href="{{ route('dashboard-warehouse') }}" class="menu-card">
                        <div class="menu-img-wrap"><img src="{{ asset('dist/img/whs_fabric.png') }}" alt="fabric"></div>
                        <span class="menu-label">Fabric</span>
                    </a>
                    <a href="#" class="menu-card">
                        <div class="menu-img-wrap"><img src="{{ asset('dist/img/whs_accs.png') }}" alt="accessories"></div>
                        <span class="menu-label">Accessories</span>
                    </a>
                    <a href="{{ route('dashboard-fg-stock') }}" class="menu-card">
                        <div class="menu-img-wrap"><img src="{{ asset('dist/img/whs_fg_stock.png') }}" alt="fg stock"></div>
                        <span class="menu-label">FG Stock</span>
                    </a>
                    <a href="{{ route('dashboard-whs-soljer') }}" class="menu-card">
                        <div class="menu-img-wrap"><img src="{{ asset('dist/img/whs-soljer.png') }}" alt="soljer"></div>
                        <span class="menu-label">Whs Soljer</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom-script')
<script>
    /* ── Clock ── */
    const _days   = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    const _months = ['Januari','Februari','Maret','April','Mei','Juni','Juli',
                     'Agustus','September','Oktober','November','Desember'];

    function pad(n) { return String(n).padStart(2, '0'); }

    function updateClock() {
        const now = new Date();
        document.getElementById('home-date-text').textContent =
            _days[now.getDay()] + ', ' + now.getDate() + ' ' + _months[now.getMonth()] + ' ' + now.getFullYear();
        document.getElementById('home-clock').textContent =
            pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds());
    }
    updateClock();
    setInterval(updateClock, 1000);

    /* ── Greeting ── */
    const _h = new Date().getHours();
    document.getElementById('home-greeting-sub').textContent =
        _h < 11 ? 'Selamat pagi,' : _h < 15 ? 'Selamat siang,' : _h < 18 ? 'Selamat sore,' : 'Selamat malam,';

    /* ── Staggered entrance ── */
    document.querySelectorAll('.menu-card').forEach((c, i) => {
        c.style.animationDelay = (i * 0.04) + 's';
    });

    /* ── Ripple ── */
    document.querySelectorAll('.menu-card').forEach(card => {
        card.addEventListener('mousedown', e => {
            const r    = card.getBoundingClientRect();
            const size = Math.max(r.width, r.height);
            const el   = document.createElement('span');
            el.className = 'ripple';
            el.style.cssText = `width:${size}px;height:${size}px;left:${e.clientX-r.left-size/2}px;top:${e.clientY-r.top-size/2}px`;
            card.appendChild(el);
            el.addEventListener('animationend', () => el.remove());
        });
    });

    /* ── Search ── */
    const inp   = document.getElementById('menu-search');
    const clr   = document.getElementById('search-clear');
    const empty = document.getElementById('search-empty');
    const kw    = document.getElementById('search-keyword');
    const cards = document.querySelectorAll('#menu-grid .menu-card');
    const hero  = document.querySelector('.home-hero');
    const searchWrap = document.querySelector('.search-wrap');

    function updateSearchStickyTop() {
        if (!hero || !searchWrap) return;
        searchWrap.style.top = `${Math.ceil(hero.getBoundingClientRect().height)}px`;
    }
    updateSearchStickyTop();
    window.addEventListener('resize', updateSearchStickyTop);

    function filterMenu(q) {
        q = q.trim().toLowerCase();
        let vis = 0;
        cards.forEach(c => {
            const match = !q || (c.querySelector('.menu-label')?.textContent.toLowerCase().includes(q));
            c.classList.toggle('filtered-out', !match);
            if (match) vis++;
        });
        clr.classList.toggle('visible', q.length > 0);
        empty.style.display = (!vis && q) ? 'block' : 'none';
        kw.textContent = q;
    }

    let currentActualState = null;

    function applyHeroVisual(state) {
        const heroEl = document.querySelector('.home-hero');
        let gradient;
        if (state === 'morning') {
            gradient = 'linear-gradient(135deg, #ff6b6b 0%, #ffa500 50%, #87ceeb 100%)';
        } else if (state === 'day') {
            gradient = 'linear-gradient(135deg, #2196F3 0%, #00b4db 100%)';
        } else if (state === 'afternoon') {
            gradient = 'linear-gradient(135deg, #ff7043 0%, #d946a6 100%)';
        } else {
            gradient = 'linear-gradient(135deg, #1a237e 0%, #283593 100%)';
        }
        document.documentElement.style.setProperty('--hero-gradient', gradient);
        if (heroEl) {
            heroEl.classList.remove('morning', 'day', 'afternoon', 'night');
            heroEl.classList.add(state);
        }
    }

    function getStateForHour(hour) {
        if (hour >= 5 && hour < 8) return 'morning';
        if (hour >= 8 && hour < 15) return 'day';
        if (hour >= 15 && hour < 18) return 'afternoon';
        return 'night';
    }

    inp.addEventListener('input', () => filterMenu(inp.value));
    clr.addEventListener('click', () => { inp.value = ''; filterMenu(''); inp.focus(); });
    document.addEventListener('keydown', e => {
        if (e.key === '/' && document.activeElement !== inp) { e.preventDefault(); inp.focus(); }
        if (e.key === 'Escape') { inp.value = ''; filterMenu(''); inp.blur(); }
    });

    /* ── Dynamic background by time ── */
    function setHeroGradient() {
        const hour = new Date().getHours();
        const state = getStateForHour(hour);
        currentActualState = state;
        applyHeroVisual(state);
    }
    setHeroGradient();
    setInterval(setHeroGradient, 60000); // update setiap 1 menit
</script>
@endsection
