<!DOCTYPE html>
{{-- Home NDS tampilan baru (dari prototype proto/beranda.html), HTML + JS biasa tanpa React, semua aset lokal.
     Menu & aturan role sama dengan home lama (resources/views/home.blade.php, route home-lama). --}}
@php
    use Illuminate\Support\Facades\Blade;

    $role = fn (...$r) => Blade::check('role', ...$r);
    $user = auth()->user();
    // [nama, keterangan, ikon, hue, kata kunci, url, besar, tampil]
    $modules = [
        ['Marketing', 'Costing, Sales Order, BOM, dan Worksheet dalam satu alur.', 'marketing', 225, 'costing so bom ws buyer order', route('dashboard-marketing'), 1, $role('admin')],
        ['Purchasing', 'PO ke supplier untuk material dan barang general.', 'purchasing', 262, 'po beli supplier pembelian request', route('dashboard-purchasing'), 0, $role('purchasing')],
        ['Warehouse', 'Penerimaan dan pengeluaran bahan.', 'warehouse', 200, 'gudang stok bpb terima bahan fabric accessories fg soljer', '#warehouse', 0, $role('warehouse')],
        ['Fabric Inspection', 'Cek cacat dan shading kain per roll.', 'inspect', 172, 'qc kain defect inspeksi 4 point', route('dashboard-qc-inspect'), 0, $role('warehouse')],
        ['Marker', 'Marker dan efisiensi pemakaian kain.', 'marker', 235, 'pola efisiensi layout', route('dashboard-marker'), 0, $role('marker')],
        ['Cutting', 'Gelar dan potong kain sesuai marker.', 'cutting', 14, 'potong spreading lay', route('dashboard-cutting'), 0, $role('cutting')],
        ['Stocker', 'Bundling dan nomor tumpukan potongan.', 'stocker', 38, 'bundle tiket nomor', route('dashboard-stocker'), 0, $role('stocker')],
        ['DC', 'Distribusi potongan ke line jahit.', 'dc', 190, 'distribution center supermarket', route('dashboard-dc'), 0, $role('dc')],
        ['Sewing Line', 'Output, target, dan efisiensi tiap line.', 'sewing', 45, 'jahit line output operator', route('dashboard-sewing-eff'), 1, $role('sewing')],
        ['Packing', 'Lipat, polybag, dan karton.', 'packing', 25, 'karton polybag carton', route('dashboard-packing'), 0, $role('packing')],
        ['Finish Good Ekspedisi', 'Barang jadi dan pengiriman ke buyer.', 'fg', 150, 'fg kirim shipment ekspedisi', route('dashboard_finish_good'), 1, $role('packing')],
        ['Export Import', 'Dokumen ekspor dan impor.', 'exim', 205, 'ekspor impor peb pib dokumen', route('dashboard-export-import'), 0, $role('export_import')],
        ['Master Data', 'Item, buyer, supplier, satuan.', 'master', 248, 'master item buyer supplier unit content', route('dashboard-master-data'), 0, $role('master_data')],
        ['PPIC', 'Rencana produksi dan jadwal line.', 'ppic', 282, 'planning jadwal rencana', route('dashboard-ppic'), 0, $role('ppic')],
        ['Industrial Engineering', 'SMV, layout, line balancing.', 'ie', 330, 'smv layout balancing waktu', route('dashboard-IE'), 0, $role('ie')],
        ['Accounting', 'Hutang, piutang, dan jurnal.', 'acc', 140, 'keuangan jurnal hutang piutang invoice', route('accounting'), 0, $role('accounting')],
        ['G.A.I.S', 'General affair dan layanan internal.', 'gais', 212, 'ga umum general affair request general', route('dashboard-ga'), 0, $role('ga')],
        ['Mutasi Mesin', 'Perpindahan mesin antar line.', 'mutasi', 6, 'mesin pindah transfer', route('mutasi_mesin'), 0, $role('machine')],
        ['Management Report', 'Ringkasan angka untuk direksi.', 'mrep', 268, 'laporan direksi kpi', route('dashboard-mgt-report'), 1, $role('management')],
        ['Asset Management', 'Daftar dan kondisi aset.', 'asset', 180, 'aset inventaris', route('dashboard-asset'), 0, $role('asset')],
        ['Helpdesk', 'Tiket bantuan dan kendala sistem.', 'help', 350, 'bantuan tiket error it', route('dashboard-helpdesk'), 0, $role('helpdesk')],
        ['Stock Opname', 'Hitung fisik stok berkala.', 'opname', 160, 'stok hitung fisik opname', route('stock_opname'), 0, $user->username == 'acc' || $role('warehouse')],
        ['Document Report', 'Status dan riwayat dokumen.', 'doc', 222, 'dokumen laporan riwayat handover', route('dashboard-report-doc'), 0, $role('admin')],
        ['Report BC', 'Laporan untuk bea cukai.', 'bc', 300, 'bea cukai kawasan berikat it inventory', route('index-report-bc'), 0, $role('export_import', 'accounting')],
        ['General Tools', 'Alat bantu administrasi sistem.', 'tools', 255, 'tools tool utilitas setting', route('general-tools'), 0, $role('superadmin')],
        ['Manage User', 'Akun, role, dan hak akses user.', 'users', 320, 'user akun role akses password', route('manage-user'), 0, $role('superadmin')],
    ];
    $modules = array_values(array_filter($modules, fn ($m) => $m[7]));
    $quick = array_values(array_filter($modules, fn ($m) => in_array($m[0], ['Marketing', 'Purchasing', 'Warehouse', 'Cutting'])));
    $helpdesk = collect($modules)->firstWhere(0, 'Helpdesk');
    $icons = [
        'marketing' => '<path d="M3 3v18h18"/><path d="m7 14 4-4 3 3 6-7"/><path d="M16 6h4v4"/>',
        'purchasing' => '<path d="M3 4h2l2.4 11h11.2L21 8H7"/><circle cx="9.5" cy="19.5" r="1.5"/><circle cx="17.5" cy="19.5" r="1.5"/>',
        'warehouse' => '<path d="M3 21V9l9-5 9 5v12"/><path d="M7 21v-8h10v8"/><path d="M7 17h10"/>',
        'ppic' => '<rect x="3" y="4" width="18" height="17" rx="2"/><path d="M3 9h18M8 2v4M16 2v4"/><path d="M7 13h4v4H7z"/>',
        'exim' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c2.8 3 2.8 15 0 18M12 3c-2.8 3-2.8 15 0 18"/>',
        'inspect' => '<path d="M4 4h10l6 6v10H4z"/><path d="M14 4v6h6"/><circle cx="11" cy="15" r="2.6"/><path d="m13 17 2.5 2.5"/>',
        'marker' => '<path d="m15 4 5 5-11 11H4v-5z"/><path d="m13 6 5 5"/>',
        'cutting' => '<circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><path d="M8.1 8.1 20 20M8.1 15.9 20 4"/>',
        'stocker' => '<rect x="4" y="3" width="16" height="18" rx="1.5"/><path d="M4 9h16M4 15h16M10 6h4M10 12h4M10 18h4"/>',
        'dc' => '<circle cx="12" cy="12" r="2.5"/><circle cx="5" cy="5" r="2"/><circle cx="19" cy="5" r="2"/><circle cx="5" cy="19" r="2"/><circle cx="19" cy="19" r="2"/><path d="M6.5 6.5l3.7 3.7M17.5 6.5l-3.7 3.7M6.5 17.5l3.7-3.7M17.5 17.5l-3.7-3.7"/>',
        'sewing' => '<path d="M4 20h16"/><path d="M6 20V8a3 3 0 0 1 3-3h9v4h-4v11"/><path d="M14 13h4"/><path d="M18 9v4"/>',
        'packing' => '<path d="m12 3 8 4.5v9L12 21l-8-4.5v-9z"/><path d="m4 7.5 8 4.5 8-4.5M12 12v9"/>',
        'fg' => '<path d="M2 7h11v9H2zM13 10h4l4 4v2h-8"/><circle cx="6" cy="18" r="2"/><circle cx="17" cy="18" r="2"/>',
        'master' => '<ellipse cx="12" cy="5.5" rx="8" ry="2.5"/><path d="M4 5.5v13c0 1.4 3.6 2.5 8 2.5s8-1.1 8-2.5v-13M4 12c0 1.4 3.6 2.5 8 2.5s8-1.1 8-2.5"/>',
        'ie' => '<circle cx="12" cy="13" r="8"/><path d="M12 13V8.5M12 13l3 2M9 2h6"/>',
        'acc' => '<rect x="4" y="2" width="16" height="20" rx="2"/><path d="M8 6h8v3H8z"/><path d="M8 13h.01M12 13h.01M16 13h.01M8 17h.01M12 17h.01M16 17h.01"/>',
        'gais' => '<circle cx="12" cy="7" r="3"/><circle cx="5" cy="17" r="2.5"/><circle cx="19" cy="17" r="2.5"/><path d="M10.3 9.5 6.4 15M13.7 9.5l3.9 5.5M7.5 17h9"/>',
        'mutasi' => '<path d="M4 9h13l-3-3M20 15H7l3 3"/>',
        'asset' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 13h18"/>',
        'opname' => '<path d="M9 4h6v3H9z"/><path d="M7 5H5v16h14V5h-2"/><path d="m9 14 2 2 4-4"/>',
        'help' => '<circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.5 2.5 0 1 1 3.5 2.3c-.6.3-1 .9-1 1.6v.6M12 17h.01"/>',
        'mrep' => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
        'doc' => '<path d="M14 3H6v18h12V7z"/><path d="M14 3v4h4M9 12h6M9 16h6"/>',
        'bc' => '<path d="M4 4h16v16H4z"/><path d="M8 4v16M4 9h4M4 14h4M12 9h5M12 13h5M12 17h3"/>',
        'tools' => '<path d="M14.7 6.3a4 4 0 0 0-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 0 0 5.4-5.4l-2.5 2.5-2.4-.6-.6-2.4z"/>',
        'users' => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-3.6 3-5.5 6.5-5.5s6.5 1.9 6.5 5.5"/><path d="M16 4.6a3.5 3.5 0 0 1 0 6.8M18 14.8c2.2.6 3.5 2.3 3.5 5.2"/>',
    ];
    $firstName = \Illuminate\Support\Str::title(strtok(trim($user->name), ' ') ?: $user->name);
@endphp
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Home - NDS</title>
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('dist/img/tabicon.png') }}">
    <script>try{history.scrollRestoration="manual"}catch(e){}try{var t=localStorage.getItem("nds-theme");if(t==="dark"||t==="light")document.documentElement.setAttribute("data-theme",t)}catch(e){}</script>
    <link rel="stylesheet" href="{{ asset('dist/css/home-nds.css') }}?v={{ @filemtime(public_path('assets/dist/css/home-nds.css')) }}">
</head>

<body>
    <nav class="nav" id="nav">
        <div class="wrap">
            <a class="logo" href="#top" aria-label="Nirwana Digital Solution"><img class="nlg nd" src="{{ asset('dist/img/nds-logo-nav.png') }}" alt="Nirwana Digital Solution"></a>
            <div class="nl"><a href="#modul">Modul</a><a href="#bantuan">Bantuan</a></div>
            <span class="sp"></span>
            <button type="button" class="thm" aria-label="Ganti tema terang atau gelap" title="Ganti tema"><svg class="moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg><svg class="sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg></button>
            <div class="me"><span>{{ strtoupper(substr($user->name, 0, 1)) }}</span><b>{{ $user->name }}</b></div>
            <a class="lo" href="{{ route('logout') }}" title="Log out" aria-label="Log out" onclick="event.preventDefault();document.getElementById('logout-form').submit();"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 4h4v16h-4"/><path d="M10 8l-4 4 4 4M6 12h10"/></svg><span>Log out</span></a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" hidden>@csrf</form>
        </div>
    </nav>

    <header class="hero" id="top">
        <div class="aurora" aria-hidden="true"><i></i><i></i><i></i><i></i></div>
        <div class="dots" aria-hidden="true"></div>
        <div class="blobs" aria-hidden="true"><i></i><i></i><i></i></div>
        @include('home_nds._threads')
        @include('home_nds._mark')
        <div class="wrap">
            <span class="eb"><i></i><span id="home-clock"></span></span>
            <h1 id="home-title" data-name="{{ $firstName }}"></h1>
            <p class="lead">Semua proses Nirwana Alabare Garment ada di sini. Ketik nama modul atau langsung pilih di bawah.</p>
            <label class="hs">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                <input id="home-search" type="search" placeholder="Cari modul… cutting, gudang, PO" aria-label="Cari modul" autocomplete="off">
                <button type="button" id="home-search-go">Cari modul</button>
            </label>
            @if (count($quick))
                <div class="qk">Sering dibuka:
                    @foreach ($quick as $m)
                        <button type="button" data-href="{{ $m[5] }}">{{ $m[0] }}</button>
                    @endforeach
                </div>
            @endif
        </div>
        <a class="cue" href="#modul"><i></i>{{ count($modules) }} MODUL</a>
    </header>

    <section class="sec" id="modul">
        <div class="wrap">
            <div class="st">
                <div><small>/ MODUL</small><h2>{{ count($modules) }} modul. Satu pintu masuk.</h2></div>
                <p>Seluruh modul saling terhubung dalam satu alur data. Pilih modul untuk mulai bekerja.</p>
            </div>
            <div class="grid" id="home-grid">
                @foreach ($modules as $i => [$name, $desc, $icon, $hue, $kw, $url, $big])
                    <a href="{{ $url }}" class="mc{{ $big ? ' big' : '' }}" style="--h:{{ $hue }}" data-kw="{{ strtolower($name . ' ' . $desc . ' ' . $kw) }}">
                        @if ($big)<span class="art"></span>@endif
                        <span class="no">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="go"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg></span>
                        <span class="ic"><svg width="{{ $big ? 30 : 26 }}" height="{{ $big ? 30 : 26 }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons[$icon] !!}</svg></span>
                        <h3 data-text="{{ $name }}">{{ $name }}</h3>
                        <p data-text="{{ $desc }}">{{ $desc }}</p>
                    </a>
                @endforeach
            </div>
            <div class="empty" id="home-empty"><b>Modul nggak ketemu.</b>Coba kata lain, misal "gudang" atau "jahit".</div>
        </div>
    </section>

    <footer id="bantuan">
        <div class="wrap">
            <span>© {{ date('Y') }} PT Nirwana Alabare Garment · Nirwana Digital Solution</span>
            @if ($helpdesk)
                <span>Ada kendala? <a href="{{ $helpdesk[5] }}">Buka Helpdesk</a></span>
            @endif
        </div>
    </footer>

    {{-- Pilih warehouse (sama dengan modal home lama) --}}
    <div class="whs-modal" id="whs-modal" hidden>
        <div class="whs-box" role="dialog" aria-modal="true" aria-labelledby="whs-title">
            <div class="whs-head"><b id="whs-title">Pilih Warehouse</b><button type="button" class="whs-x" data-close aria-label="Tutup">&times;</button></div>
            <div class="whs-grid">
                <a href="{{ route('dashboard-warehouse') }}"><img src="{{ asset('dist/img/whs_fabric.png') }}" alt="">Fabric</a>
                <a href="#"><img src="{{ asset('dist/img/whs_accs.png') }}" alt="">Accessories</a>
                <a href="{{ route('dashboard-fg-stock') }}"><img src="{{ asset('dist/img/whs_fg_stock.png') }}" alt="">FG Stock</a>
                <a href="{{ route('dashboard-whs-soljer') }}"><img src="{{ asset('dist/img/whs-soljer.png') }}" alt="">Whs Soljer</a>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var $ = function (id) { return document.getElementById(id); };

            // Jam + sapaan (judul hero dianimasikan per kata seperti prototype).
            function words(text, offset) {
                return text.split(' ').map(function (w, i) {
                    var s = document.createElement('span');
                    s.className = 'w';
                    s.style.animationDelay = ((offset + i) * 90 + 120) + 'ms';
                    s.textContent = w;
                    return s;
                });
            }
            (function title() {
                var h = new Date().getHours(), h1 = $('home-title');
                var sal = h < 11 ? 'Selamat pagi,' : h < 15 ? 'Selamat siang,' : h < 19 ? 'Selamat sore,' : 'Selamat malam,';
                var gr = document.createElement('span');
                gr.className = 'gr';
                var parts = words(sal, 0).concat(words(h1.dataset.name + '.', 2));
                parts.forEach(function (p) { h1.appendChild(p); h1.appendChild(document.createTextNode(' ')); });
                words('Mau mulai dari mana hari ini?', 3).forEach(function (p) { gr.appendChild(p); gr.appendChild(document.createTextNode(' ')); });
                h1.appendChild(gr);
            })();
            function tick() {
                var d = new Date();
                $('home-clock').textContent = d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }).toUpperCase()
                    + ' · ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }).replace('.', ':');
            }
            tick();
            setInterval(tick, 15000);

            // Navbar solid setelah scroll.
            function solid() { $('nav').classList.toggle('solid', scrollY > 40); }
            solid();
            addEventListener('scroll', solid, { passive: true });

            // Kartu modul: muncul saat terlihat + efek sorot/miring mengikuti pointer.
            var cards = Array.prototype.slice.call(document.querySelectorAll('#home-grid .mc'));
            if ('IntersectionObserver' in window) {
                var io = new IntersectionObserver(function (entries) {
                    entries.forEach(function (e) {
                        if (!e.isIntersecting) return;
                        e.target.style.transitionDelay = (cards.indexOf(e.target) % 4) * 70 + 'ms';
                        e.target.classList.add('in');
                        io.unobserve(e.target);
                    });
                }, { threshold: .12 });
                cards.forEach(function (c) { io.observe(c); });
            } else {
                cards.forEach(function (c) { c.classList.add('in'); });
            }
            cards.forEach(function (c) {
                function move(e) {
                    var r = c.getBoundingClientRect(), px = (e.clientX - r.left) / r.width - .5, py = (e.clientY - r.top) / r.height - .5;
                    c.style.setProperty('--x', (e.clientX - r.left) + 'px');
                    c.style.setProperty('--y', (e.clientY - r.top) + 'px');
                    c.style.setProperty('--rx', (-py * 7).toFixed(2) + 'deg');
                    c.style.setProperty('--ry', (px * 7).toFixed(2) + 'deg');
                }
                c.addEventListener('pointermove', move);
                c.addEventListener('pointerenter', move);
                c.addEventListener('pointerleave', function () { c.style.setProperty('--rx', '0deg'); c.style.setProperty('--ry', '0deg'); });
                c.addEventListener('click', function (e) {
                    if (c.getAttribute('href') !== '#warehouse') return;
                    e.preventDefault();
                    openWhs();
                });
            });

            // Cari modul: filter kartu + tandai teks yang cocok.
            var search = $('home-search');
            function esc(s) { return s.replace(/[&<>"]/g, function (ch) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[ch]; }); }
            function mark(el, q) {
                var t = el.dataset.text, i = q ? t.toLowerCase().indexOf(q) : -1;
                el.innerHTML = i < 0 ? esc(t) : esc(t.slice(0, i)) + '<mark>' + esc(t.slice(i, i + q.length)) + '</mark>' + esc(t.slice(i + q.length));
            }
            function filter() {
                var q = search.value.trim().toLowerCase(), shown = 0;
                cards.forEach(function (c) {
                    var ok = !q || c.dataset.kw.indexOf(q) >= 0;
                    c.hidden = !ok;
                    c.classList.toggle('big', !q && c.querySelector('.art') !== null);
                    if (q) c.classList.add('in');
                    if (ok) shown++;
                    c.querySelectorAll('[data-text]').forEach(function (el) { mark(el, q); });
                });
                $('home-empty').style.display = shown ? 'none' : 'block';
            }
            function goModules() { $('modul').scrollIntoView({ behavior: 'smooth' }); }
            search.addEventListener('input', filter);
            search.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    var visible = cards.filter(function (c) { return !c.hidden; });
                    if (visible.length === 1) visible[0].click(); else goModules();
                }
            });
            $('home-search-go').addEventListener('click', goModules);
            document.addEventListener('keydown', function (e) {
                if (e.key === '/' && document.activeElement !== search) { e.preventDefault(); search.focus(); }
                if (e.key === 'Escape') { closeWhs(); }
            });

            document.querySelectorAll('.qk button[data-href]').forEach(function (b) {
                b.addEventListener('click', function () {
                    if (b.dataset.href === '#warehouse') openWhs(); else location.href = b.dataset.href;
                });
            });

            // Modal pilih warehouse.
            function openWhs() { $('whs-modal').hidden = false; }
            function closeWhs() { $('whs-modal').hidden = true; }
            $('whs-modal').addEventListener('click', function (e) {
                if (e.target === this || e.target.closest('[data-close]')) closeWhs();
            });

            // Ganti tema terang / gelap.
            document.addEventListener('click', function (e) {
                if (!e.target.closest('.thm')) return;
                var r = document.documentElement;
                var cur = r.getAttribute('data-theme') || (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                var n = cur === 'dark' ? 'light' : 'dark';
                r.setAttribute('data-theme', n);
                try { localStorage.setItem('nds-theme', n); } catch (err) {}
            });
        })();
    </script>
</body>

</html>
