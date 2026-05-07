@extends('layouts.index', ['navbar' => false, 'footer' => false])

@section('content')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap');

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body {
            background: #F0EEE9;
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
        }

        .home-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* HEADER */
        .home-header {
            background: #1A1916;
            padding: 18px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .home-header-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-icon {
            width: 36px;
            height: 36px;
            background: #fff;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .brand-name {
            font-size: 15px;
            font-weight: 600;
            color: #fff;
        }

        .brand-sub {
            font-size: 11px;
            color: rgba(255, 255, 255, .4);
            margin-top: 1px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .header-time {
            font-size: 12px;
            color: rgba(255, 255, 255, .5);
            font-family: 'DM Mono', monospace;
            letter-spacing: .05em;
        }

        .user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #333;
            border: 1.5px solid rgba(255, 255, 255, .15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            color: rgba(255, 255, 255, .8);
            font-weight: 500;
            flex-shrink: 0;
        }

        .user-info-name {
            font-size: 13px;
            font-weight: 500;
            color: #fff;
        }

        .user-info-role {
            font-size: 11px;
            color: rgba(255, 255, 255, .4);
        }

        /* GREETING */
        .greeting-strip {
            background: #fff;
            border-bottom: 1px solid #E5E3DE;
            padding: 18px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }

        .greeting-text {
            font-size: 22px;
            font-weight: 500;
            color: #1A1916;
            letter-spacing: -.02em;
        }

        .greeting-text span {
            color: #4B7FD4;
        }

        .greeting-sub {
            font-size: 13px;
            color: #888;
            margin-top: 2px;
        }

        .greeting-date-badge {
            font-size: 12px;
            color: #666;
            background: #F5F4F1;
            border: 1px solid #E5E3DE;
            border-radius: 20px;
            padding: 5px 14px;
            font-family: 'DM Mono', monospace;
        }

        /* BODY */
        .home-body {
            flex: 1;
            padding: 28px;
        }

        .section-label {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: .1em;
            text-transform: uppercase;
            color: #AAA;
            margin-bottom: 14px;
            padding-bottom: 8px;
            border-bottom: 1px solid #E5E3DE;
        }

        /* GRID */
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 12px;
        }

        /* CARD */
        .menu-card {
            background: #fff;
            border: 1px solid #E5E3DE;
            border-radius: 12px;
            padding: 20px 16px 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            cursor: pointer;
            transition: all .2s cubic-bezier(.34, 1.56, .64, 1);
            position: relative;
            overflow: hidden;
            animation: fadeInUp .3s ease both;
        }

        .menu-card:hover {
            border-color: #1A1916;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, .1);
            text-decoration: none;
        }

        .menu-card:active {
            transform: translateY(-1px) scale(.98);
        }

        .menu-card.logout-card {
            background: #FFF8F8;
            border-color: #F5D5D5;
        }

        .menu-card.logout-card:hover {
            border-color: #E24B4A;
            box-shadow: 0 8px 20px rgba(226, 75, 74, .12);
        }

        .menu-card.logout-card .menu-label {
            color: #C0392B;
        }

        .menu-arrow {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #F0EEE9;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity .2s;
        }

        .menu-arrow svg {
            width: 10px;
            height: 10px;
            color: #666;
        }

        .menu-card:hover .menu-arrow {
            opacity: 1;
        }

        .menu-icon-wrap {
            width: 60px;
            height: 60px;
            border-radius: 14px;
            background: #F5F4F1;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background .2s;
            flex-shrink: 0;
        }

        .menu-card:hover .menu-icon-wrap {
            background: #EEECEA;
        }

        .menu-icon-wrap img {
            width: 36px;
            height: 36px;
            object-fit: contain;
        }

        .menu-label {
            font-size: 12px;
            font-weight: 500;
            color: #1A1916;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: .04em;
            line-height: 1.3;
        }

        /* FOOTER */
        .home-footer {
            background: #fff;
            border-top: 1px solid #E5E3DE;
            padding: 14px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
        }

        .footer-text {
            font-size: 11px;
            color: #BBB;
        }

        .footer-version {
            font-size: 11px;
            color: #CCC;
            font-family: 'DM Mono', monospace;
        }

        /* MODAL */
        .modal-content {
            border-radius: 14px;
            border: 1px solid #E5E3DE;
            overflow: hidden;
        }

        .modal-header {
            background: #1A1916;
            border-bottom: none;
            padding: 18px 22px;
        }

        .modal-title {
            color: #fff;
            font-size: 15px;
            font-weight: 500;
        }

        .modal-header .btn-close {
            filter: invert(1);
            opacity: .6;
        }

        .modal-body {
            padding: 22px;
            background: #F5F4F1;
        }

        .modal-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
        }

        /* ANIMATION */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(12px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .menu-card:nth-child(1) {
            animation-delay: .03s
        }

        .menu-card:nth-child(2) {
            animation-delay: .06s
        }

        .menu-card:nth-child(3) {
            animation-delay: .09s
        }

        .menu-card:nth-child(4) {
            animation-delay: .12s
        }

        .menu-card:nth-child(5) {
            animation-delay: .15s
        }

        .menu-card:nth-child(6) {
            animation-delay: .18s
        }

        .menu-card:nth-child(7) {
            animation-delay: .21s
        }

        .menu-card:nth-child(8) {
            animation-delay: .24s
        }

        .menu-card:nth-child(9) {
            animation-delay: .27s
        }

        .menu-card:nth-child(10) {
            animation-delay: .30s
        }

        .menu-card:nth-child(11) {
            animation-delay: .33s
        }

        .menu-card:nth-child(12) {
            animation-delay: .36s
        }

        /* RESPONSIVE */
        @media (max-width:768px) {

            .home-header,
            .greeting-strip,
            .home-body,
            .home-footer {
                padding-left: 16px;
                padding-right: 16px;
            }

            .menu-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 10px;
            }

            .header-time {
                display: none;
            }

            .greeting-text {
                font-size: 18px;
            }

            .modal-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width:480px) {
            .menu-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>

    {{-- Arrow SVG shorthand --}}
    @php
        $arrow =
            '<div class="menu-arrow"><svg viewBox="0 0 10 10" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2 8L8 2M8 2H4M8 2v4"/></svg></div>';
    @endphp

    <div class="home-wrapper">

        <header class="home-header">
            <div class="home-header-brand">
                <div class="brand-icon">🧵</div>
                <div>
                    <div class="brand-name">GarmentOS</div>
                    <div class="brand-sub">Production Management System</div>
                </div>
            </div>
            <div class="header-right">
                <div class="header-time" id="live-clock">--:--:--</div>
                <div style="display:flex;align-items:center;gap:10px;">
                    <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                    <div>
                        <div class="user-info-name">{{ auth()->user()->name }}</div>
                        {{-- <div class="user-info-role">{{ auth()->user()->getRoleNames()->first() ?? 'User' }}</div> --}}
                    </div>
                </div>
            </div>
        </header>

        <div class="greeting-strip">
            <div>
                <div class="greeting-text">Halo, <span>{{ auth()->user()->name }}</span> 👋</div>
                <div class="greeting-sub">Pilih menu yang ingin kamu akses hari ini</div>
            </div>
            <div class="greeting-date-badge" id="live-date">--</div>
        </div>

        <div class="home-body">
            <div class="section-label">Menu Utama</div>
            <div class="menu-grid">

                @role('marker')
                    <a href="{{ route('dashboard-marker') }}" class="menu-card">
                        {!! $arrow !!}
                        <div class="menu-icon-wrap"><img src="{{ asset('dist/img/marker.png') }}" alt="Marker"></div>
                        <span class="menu-label">Marker</span>
                    </a>
                @endrole

                @role('cutting')
                    <a href="{{ route('dashboard-cutting') }}" class="menu-card">
                        {!! $arrow !!}
                        <div class="menu-icon-wrap"><img src="{{ asset('dist/img/cutting.png') }}" alt="Cutting"></div>
                        <span class="menu-label">Cutting</span>
                    </a>
                @endrole

                @role('stocker')
                    <a href="{{ route('dashboard-stocker') }}" class="menu-card">
                        {!! $arrow !!}
                        <div class="menu-icon-wrap"><img src="{{ asset('dist/img/stocker.png') }}" alt="Stocker"></div>
                        <span class="menu-label">Stocker</span>
                    </a>
                @endrole

                @role('dc')
                    <a href="{{ route('dashboard-dc') }}" class="menu-card">
                        {!! $arrow !!}
                        <div class="menu-icon-wrap"><img src="{{ asset('dist/img/distribution.jpeg') }}" alt="DC"></div>
                        <span class="menu-label">DC</span>
                    </a>
                @endrole

                @role('sewing')
                    <a href="{{ route('dashboard-sewing-eff') }}" class="menu-card">
                        {!! $arrow !!}
                        <div class="menu-icon-wrap"><img src="{{ asset('dist/img/sewingline.png') }}" alt="Sewing Line"></div>
                        <span class="menu-label">Sewing Line</span>
                    </a>
                @endrole

                @role('machine')
                    <a href="{{ route('dashboard-mut-mesin') }}" class="menu-card">
                        {!! $arrow !!}
                        <div class="menu-icon-wrap"><img src="{{ asset('dist/img/mut_mesin.png') }}" alt="Mutasi Mesin"></div>
                        <span class="menu-label">Mutasi Mesin</span>
                    </a>
                @endrole

                @role('warehouse')
                    <a href="#" class="menu-card" onclick="getmodalwarehouse()">
                        {!! $arrow !!}
                        <div class="menu-icon-wrap"><img src="{{ asset('dist/img/warehouse.png') }}" alt="Warehouse"></div>
                        <span class="menu-label">Warehouse</span>
                    </a>
                    <a href="{{ route('stock_opname') }}" class="menu-card">
                        {!! $arrow !!}
                        <div class="menu-icon-wrap"><img src="{{ asset('dist/img/stock_opname.png') }}" alt="Stock Opname">
                        </div>
                        <span class="menu-label">Stock Opname</span>
                    </a>
                    <a href="{{ route('dashboard-qc-inspect') }}" class="menu-card">
                        {!! $arrow !!}
                        <div class="menu-icon-wrap"><img src="{{ asset('dist/img/inspect.png') }}" alt="Fabric Inspection">
                        </div>
                        <span class="menu-label">Fabric Inspection</span>
                    </a>
                    @endwarehouse

                    @if (auth()->user()->username == 'acc')
                        <a href="{{ route('stock_opname') }}" class="menu-card">
                            {!! $arrow !!}
                            <div class="menu-icon-wrap"><img src="{{ asset('dist/img/stock_opname.png') }}" alt="Stock Opname">
                            </div>
                            <span class="menu-label">Stock Opname</span>
                        </a>
                    @endif

                    @role('ppic')
                        <a href="{{ route('dashboard-ppic') }}" class="menu-card">
                            {!! $arrow !!}
                            <div class="menu-icon-wrap"><img src="{{ asset('dist/img/ppic.png') }}" alt="PPIC"></div>
                            <span class="menu-label">PPIC</span>
                        </a>
                    @endrole

                    @role('packing')
                        <a href="{{ route('dashboard-packing') }}" class="menu-card">
                            {!! $arrow !!}
                            <div class="menu-icon-wrap"><img src="{{ asset('dist/img/packing.png') }}" alt="Packing"></div>
                            <span class="menu-label">Packing</span>
                        </a>
                        <a href="{{ route('dashboard_finish_good') }}" class="menu-card">
                            {!! $arrow !!}
                            <div class="menu-icon-wrap"><img src="{{ asset('dist/img/finish_good.png') }}" alt="Finish Good"></div>
                            <span class="menu-label">Finish Good Ekspedisi</span>
                        </a>
                    @endrole

                    @role('admin')
                        <a href="{{ route('dashboard-report-doc') }}" class="menu-card">
                            {!! $arrow !!}
                            <div class="menu-icon-wrap"><img src="{{ asset('dist/img/doc_report.png') }}" alt="Document Report">
                            </div>
                            <span class="menu-label">Document Report</span>
                        </a>
                        <a href="{{ route('dashboard-marketing') }}" class="menu-card">
                            {!! $arrow !!}
                            <div class="menu-icon-wrap"><img src="{{ asset('dist/img/marketing.png') }}" alt="Marketing"></div>
                            <span class="menu-label">Marketing</span>
                        </a>
                    @endrole

                    @role('ga')
                        <a href="{{ route('dashboard-ga') }}" class="menu-card">
                            {!! $arrow !!}
                            <div class="menu-icon-wrap"><img src="{{ asset('dist/img/general_affair.png') }}" alt="G.A.I.S">
                            </div>
                            <span class="menu-label">G.A.I.S</span>
                        </a>
                    @endrole

                    @role('accounting')
                        <a href="{{ route('accounting') }}" class="menu-card">
                            {!! $arrow !!}
                            <div class="menu-icon-wrap"><img src="{{ asset('dist/img/accounting_img.png') }}" alt="Accounting">
                            </div>
                            <span class="menu-label">Accounting</span>
                        </a>
                    @endrole

                    @role('export_import')
                        <a href="{{ route('dashboard-export-import') }}" class="menu-card">
                            {!! $arrow !!}
                            <div class="menu-icon-wrap"><img src="{{ asset('dist/img/exim_img.jpeg') }}" alt="Export Import">
                            </div>
                            <span class="menu-label">Export Import</span>
                        </a>
                    @endrole

                    @role('management')
                        <a href="{{ route('dashboard-mgt-report') }}" class="menu-card">
                            {!! $arrow !!}
                            <div class="menu-icon-wrap"><img src="{{ asset('dist/img/management_report.png') }}"
                                    alt="Management Report"></div>
                            <span class="menu-label">Management Report</span>
                        </a>
                        <a href="{{ route('dashboard-IE') }}" class="menu-card">
                            {!! $arrow !!}
                            <div class="menu-icon-wrap"><img src="{{ asset('dist/img/IE.png') }}" alt="Industrial Engineering">
                            </div>
                            <span class="menu-label">Industrial Engineering</span>
                        </a>
                    @endrole

                    @role('superadmin')
                        <a href="{{ route('manage-user') }}" class="menu-card">
                            {!! $arrow !!}
                            <div class="menu-icon-wrap"><img src="{{ asset('dist/img/manage-users.png') }}" alt="Manage User">
                            </div>
                            <span class="menu-label">Manage User</span>
                        </a>
                        <a href="{{ route('general-tools') }}" class="menu-card">
                            {!! $arrow !!}
                            <div class="menu-icon-wrap"><img src="{{ asset('dist/img/tools.png') }}" alt="General Tools"></div>
                            <span class="menu-label">General Tools</span>
                        </a>
                    @endrole

                    {{-- Logout --}}
                    <a href="{{ route('logout') }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                        class="menu-card logout-card">
                        <div class="menu-icon-wrap" style="background:#FFF0F0;">
                            <img src="{{ asset('dist/img/signout.png') }}" alt="Logout">
                        </div>
                        <span class="menu-label">Logout</span>
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none"
                        onsubmit="logout(this, event)">
                        @csrf
                    </form>

                </div>
            </div>

            <footer class="home-footer">
                <span class="footer-text">© {{ date('Y') }} GarmentOS · Production Management System</span>
                <span class="footer-version">v2.0.0</span>
            </footer>
        </div>

        {{-- Warehouse Modal --}}
        <div class="modal fade" id="modal-pilih-gudang" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Pilih Warehouse</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="modal-grid">
                            <a href="{{ route('dashboard-warehouse') }}" class="menu-card" style="animation:none;">
                                <div class="menu-icon-wrap"><img src="{{ asset('dist/img/whs_fabric.png') }}"
                                        alt="Fabric"></div>
                                <span class="menu-label">Fabric</span>
                            </a>
                            <a href="#" class="menu-card" style="animation:none;">
                                <div class="menu-icon-wrap"><img src="{{ asset('dist/img/whs_accs.png') }}"
                                        alt="Accessories"></div>
                                <span class="menu-label">Accessories</span>
                            </a>
                            <a href="{{ route('dashboard-fg-stock') }}" class="menu-card" style="animation:none;">
                                <div class="menu-icon-wrap"><img src="{{ asset('dist/img/whs_fg_stock.png') }}"
                                        alt="FG Stock"></div>
                                <span class="menu-label">FG Stock</span>
                            </a>
                            <a href="{{ route('dashboard-whs-soljer') }}" class="menu-card" style="animation:none;">
                                <div class="menu-icon-wrap"><img src="{{ asset('dist/img/whs-soljer.png') }}"
                                        alt="Whs Soljer"></div>
                                <span class="menu-label">Whs Soljer</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function updateClock() {
                const now = new Date();
                const time = now.toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: false
                });
                const date = now.toLocaleDateString('id-ID', {
                    weekday: 'short',
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric'
                });
                const ec = document.getElementById('live-clock');
                const ed = document.getElementById('live-date');
                if (ec) ec.textContent = time;
                if (ed) ed.textContent = date;
            }
            updateClock();
            setInterval(updateClock, 1000);
        </script>
    @endsection
