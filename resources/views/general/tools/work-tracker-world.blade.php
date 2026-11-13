@extends('layouts.index')

@section('custom-link')
    <style>
        .ww { position: relative; height: calc(100vh - 120px); min-height: 560px; overflow: hidden; border-radius: 16px; background: #9fd3f5; color: #fff; font-size: 12.5px; user-select: none; }
        .ww:fullscreen { height: 100vh; border-radius: 0; }
        .ww > canvas#ww-canvas { display: block; width: 100%; height: 100%; outline: none; cursor: grab; }
        .ww > canvas#ww-canvas.dragging { cursor: grabbing; }
        .ww-panel { position: absolute; z-index: 5; padding: 10px 12px; border: 1px solid rgba(255, 255, 255, .18); border-radius: 14px; background: rgba(8, 33, 73, .72); box-shadow: 0 8px 24px rgba(4, 16, 40, .3); backdrop-filter: blur(6px); }
        .ww-hud { top: 12px; left: 12px; width: 270px; }
        .ww-title { font-size: 15px; font-weight: 800; letter-spacing: .01em; }
        .ww-sub { color: #bcd0ee; font-size: 12px; }
        .ww-conn { display: flex; align-items: center; gap: 6px; margin-top: 6px; font-size: 11.5px; color: #dbe7fb; }
        .ww-conn .dot { width: 8px; height: 8px; border-radius: 50%; background: #f0a429; box-shadow: 0 0 0 3px rgba(240, 164, 41, .25); }
        .ww-conn.live .dot { background: #2fd27a; box-shadow: 0 0 0 3px rgba(47, 210, 122, .25); animation: ww-blink 1.6s infinite; }
        .ww-conn.off .dot { background: #e5484d; box-shadow: 0 0 0 3px rgba(229, 72, 77, .25); }
        @keyframes ww-blink { 50% { opacity: .45; } }
        .ww-hud { width: 300px; }
        .ww-event { margin-top: 9px; padding-top: 9px; border-top: 1px solid rgba(255, 255, 255, .16); }
        .ww-event-title { display: inline-block; max-width: 100%; padding: 3px 11px; border-radius: 12px; color: #fff; font-size: 13.5px; font-weight: 800; text-shadow: 0 1px 3px rgba(0, 0, 0, .45); background-size: 200% 100% !important; animation: ww-shimmer 4s linear infinite; }
        @keyframes ww-shimmer { 0% { background-position: 0 0; } 100% { background-position: 200% 0; } }
        .ww-event-sub { margin-top: 5px; color: #e8eefc; font-size: 12px; }
        .ww-event-also { margin-top: 4px; color: #bcd0ee; font-size: 11.5px; }
        .ww-quote { margin-top: 7px; padding: 6px 9px; border-left: 3px solid #ffd36b; border-radius: 6px; background: rgba(255, 255, 255, .08); color: #fff6d6; font-size: 12px; font-style: italic; }
        .ww-actions { display: flex; flex-wrap: wrap; gap: 5px; margin-top: 9px; }
        .ww-actions button, .ww-actions a { padding: 3px 9px; border: 1px solid rgba(255, 255, 255, .28); border-radius: 14px; background: rgba(255, 255, 255, .08); color: #fff; font-size: 11.5px; font-weight: 600; text-decoration: none; }
        .ww-actions button:hover, .ww-actions a:hover { background: rgba(255, 255, 255, .2); color: #fff; }
        .ww-online { top: 12px; right: 12px; width: 220px; max-height: 42%; overflow: auto; }
        .ww-online h6 { margin: 0 0 6px; font-size: 12px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
        .ww-online ul { margin: 0; padding: 0; list-style: none; }
        .ww-online li { display: flex; align-items: center; gap: 7px; padding: 3px 0; font-weight: 600; }
        .ww-online li .av { display: inline-grid; place-items: center; width: 20px; height: 20px; border-radius: 50%; font-size: 9px; font-weight: 800; color: #fff; flex: none; }
        .ww-online li small { margin-left: auto; color: #bcd0ee; font-weight: 500; }
        .ww-chat { left: 12px; bottom: 12px; width: 360px; padding: 8px; }
        .ww-chat-log { max-height: 170px; overflow-y: auto; padding: 2px 4px 6px; font-size: 12.5px; line-height: 1.45; }
        .ww-chat-log div { word-break: break-word; }
        .ww-chat-log b { font-weight: 800; }
        .ww-chat-log .sys { color: #bcd0ee; font-style: italic; }
        .ww-chat-log time { color: #8fa6cc; font-size: 10.5px; margin-right: 4px; }
        .ww-chat input { width: 100%; padding: 6px 10px; border: 1px solid rgba(255, 255, 255, .25); border-radius: 10px; background: rgba(255, 255, 255, .1); color: #fff; outline: none; }
        .ww-chat input::placeholder { color: #a9bddc; }
        .ww-chat input:focus { background: rgba(255, 255, 255, .18); border-color: #7fb2ff; }
        .ww-minimap { position: absolute; right: 12px; bottom: 12px; z-index: 5; width: 190px; height: 190px; border: 2px solid rgba(255, 255, 255, .6); border-radius: 50%; background: rgba(8, 33, 73, .55); box-shadow: 0 8px 24px rgba(4, 16, 40, .3); }
        .ww-card { position: absolute; left: 50%; bottom: 16px; z-index: 6; width: min(420px, calc(100% - 24px)); transform: translateX(-50%); padding: 14px 16px; border-radius: 16px; background: #fff; color: #1b2536; box-shadow: 0 14px 36px rgba(4, 16, 40, .35); animation: ww-pop .18s ease-out; }
        @keyframes ww-pop { from { opacity: 0; transform: translate(-50%, 10px); } }
        .ww-card h5 { margin: 0 0 2px; font-size: 15px; font-weight: 800; }
        .ww-card .meta { color: #5f6b80; font-size: 12px; }
        .ww-card .chip { display: inline-block; margin-right: 4px; padding: 1px 8px; border-radius: 10px; font-size: 11px; font-weight: 700; }
        .ww-card .bar { height: 8px; margin: 8px 0 4px; overflow: hidden; border-radius: 6px; background: #e4e9f2; }
        .ww-card .bar span { display: block; height: 100%; border-radius: 6px; }
        .ww-card .list { max-height: 180px; margin-top: 6px; overflow: auto; }
        .ww-card .list a { display: block; padding: 5px 0; border-top: 1px solid #eef1f6; color: #1b2536; text-decoration: none; }
        .ww-card .list a:hover { color: #1b5fc1; }
        .ww-card .close { position: absolute; top: 8px; right: 10px; border: 0; background: none; color: #8a97ad; font-size: 16px; }
        .ww-card .go { display: inline-block; margin-top: 8px; padding: 5px 12px; border-radius: 10px; background: #082149; color: #fff; font-size: 12px; font-weight: 700; text-decoration: none; }
        .ww-card .go:hover { background: #123a78; color: #fff; }
        .ww-help { position: absolute; left: 50%; top: 12px; z-index: 5; transform: translateX(-50%); padding: 6px 14px; border-radius: 20px; background: rgba(8, 33, 73, .62); font-size: 12px; white-space: nowrap; }
        .ww-help kbd { padding: 1px 5px; border-radius: 4px; background: rgba(255, 255, 255, .2); color: #fff; font-size: 11px; }
        .ww-loading { position: absolute; inset: 0; z-index: 10; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 14px; background: linear-gradient(160deg, #0b2a5c, #1c5bb8 60%, #22c1c3); font-size: 15px; font-weight: 700; transition: opacity .5s; }
        .ww-loading.hide { opacity: 0; pointer-events: none; }
        .ww-loading .cube { width: 54px; height: 54px; border-radius: 12px; background: linear-gradient(135deg, #fff, #9cc4ff); animation: ww-spin 1.4s infinite ease-in-out; box-shadow: 0 10px 30px rgba(0, 0, 0, .25); }
        @keyframes ww-spin { 0% { transform: rotate(0) scale(1); } 50% { transform: rotate(180deg) scale(.8); border-radius: 50%; } 100% { transform: rotate(360deg) scale(1); } }
        .ww-loading small { font-weight: 500; color: #d5e4ff; }
        .ww-toast { position: absolute; left: 50%; top: 52px; z-index: 7; transform: translateX(-50%); padding: 6px 14px; border-radius: 12px; background: rgba(8, 33, 73, .85); font-weight: 600; opacity: 0; transition: opacity .3s; pointer-events: none; }
        .ww-toast.show { opacity: 1; }
        @media (max-width: 900px) {
            .ww-online, .ww-help { display: none; }
            .ww-chat { width: calc(100% - 24px); bottom: 210px; }
        }
    </style>
@endsection

@section('content')
    <div class="ww" id="ww">
        <canvas id="ww-canvas" tabindex="0" aria-label="Dunia 3D Work Tracker"></canvas>

        <div class="ww-loading" id="ww-loading">
            <div class="cube"></div>
            <div id="ww-loading-text">Membangun kampus...</div>
            <small>Pulau, gedung divisi, dan Jalan Waktu sedang disiapkan</small>
        </div>

        <div class="ww-panel ww-hud">
            <div class="ww-title"><i class="fa-solid fa-cube"></i> Kampus Work Tracker</div>
            <div class="ww-sub" id="ww-date"></div>
            <div class="ww-conn" id="ww-conn"><span class="dot"></span><span id="ww-conn-text">Menghubungkan...</span></div>
            <div class="ww-event" id="ww-event"></div>
            <div class="ww-actions">
                <button type="button" data-go="plaza"><i class="fa-solid fa-fountain"></i> Plaza</button>
                <button type="button" data-go="today"><i class="fa-solid fa-road"></i> Jalan Waktu</button>
                <button type="button" data-go="desk" id="ww-go-desk" class="d-none"><i class="fa-solid fa-computer"></i> Mejaku</button>
                <button type="button" id="ww-fullscreen"><i class="fa-solid fa-expand"></i> Layar penuh</button>
                <a href="{{ route('work-tracker-dashboard') }}"><i class="fa-solid fa-arrow-left"></i> Dashboard</a>
            </div>
        </div>

        <div class="ww-help" id="ww-help"><kbd>W A S D</kbd> jalan · <kbd>Shift</kbd> lari · <kbd>Spasi</kbd> lompat · tarik mouse putar kamera · scroll zoom · <kbd>Enter</kbd> chat</div>

        <div class="ww-panel ww-online">
            <h6><i class="fa-solid fa-signal"></i> Online <span id="ww-online-count">1</span></h6>
            <ul id="ww-online-list"></ul>
        </div>

        <div class="ww-panel ww-chat">
            <div class="ww-chat-log" id="ww-chat-log"></div>
            <form id="ww-chat-form" autocomplete="off"><input type="text" id="ww-chat-input" maxlength="200" placeholder="Tekan Enter untuk chat..." aria-label="Pesan chat"></form>
        </div>

        <canvas class="ww-minimap" id="ww-minimap" width="380" height="380" aria-label="Peta mini"></canvas>
        <div class="ww-card d-none" id="ww-card"></div>
        <div class="ww-toast" id="ww-toast"></div>
    </div>
@endsection

@section('custom-script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <script>
        (function () {
            const ME = @json($me);
            const COMPANIONS = @json($companions);
            const ROUTES = {
                data: @json(route('data-work-tracker')),
                presence: @json(route('presence-work-tracker-world')),
                chat: @json(route('chat-work-tracker-world')),
                tracker: @json(route('work-tracker')),
                socket: 'http://' + window.location.hostname + ':' + @json((string) config('redis.echo_port')) + '/socket.io/socket.io.js',
                echo: @json(config('redis.redis_url_public') . '/js/laravel-echo-setup.js'),
            };
            const CSRF = $('meta[name="csrf-token"]').attr('content');

            const STATUS_LABEL = { baru: 'Baru', dikerjakan: 'Dikerjakan', selesai: 'Selesai', ditolak: 'Ditolak' };
            const HEALTH_LABEL = { aman: 'Aman', mepet: 'Mepet', telat: 'Telat', selesai: 'Selesai', selesai_telat: 'Selesai telat', ditolak: 'Ditolak', belum_dinilai: 'Belum dinilai' };
            const HEALTH_COLOR = { aman: '#2fb36d', mepet: '#f0a429', telat: '#e5484d', selesai: '#14a3a3', selesai_telat: '#5f9ea8', ditolak: '#8a97ad', belum_dinilai: '#8a97ad' };
            const BUILDING_COLORS = ['#3b82f6', '#8b5cf6', '#f97316', '#10b981', '#ec4899', '#0ea5e9', '#eab308'];

            // Ukuran dunia (satuan kira-kira meter)
            const ISLAND_R = 110;
            const ROAD_Z = 46;
            const ROAD_W = 8;
            const DAY = 3.2;
            const PAST = 30;
            const FUTURE = 45;
            const LANE_GAP = 2.4;

            const $ww = $('#ww');
            const canvas = document.getElementById('ww-canvas');

            if (!window.THREE) {
                $('#ww-loading-text').text('Library 3D gagal dimuat');
                $('#ww-loading small').text('Pastikan komputer terhubung ke internet (cdnjs.cloudflare.com), lalu muat ulang halaman.');
                return;
            }

            // ---------- Utilitas ----------
            function esc(value) {
                return $('<div>').text(value === null || value === undefined ? '' : value).html().replace(/"/g, '&quot;');
            }
            function parseDate(iso) {
                const p = String(iso).slice(0, 10).split('-').map(Number);
                return new Date(p[0], p[1] - 1, p[2]);
            }
            function days(a, b) {
                return Math.round((parseDate(b) - parseDate(a)) / 86400000);
            }
            function addDays(iso, n) {
                const d = parseDate(iso);
                d.setDate(d.getDate() + n);
                return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
            }
            function fmt(iso, withYear) {
                if (!iso) return '-';
                const o = { day: 'numeric', month: 'short' };
                if (withYear) o.year = 'numeric';
                return parseDate(iso).toLocaleDateString('id-ID', o);
            }
            function initials(name) {
                return (name || '?').trim().split(/\s+/).slice(0, 2).map(function (w) { return w[0]; }).join('').toUpperCase();
            }
            function hashColor(id) {
                const palette = ['#2f80ed', '#9b51e0', '#f2994a', '#27ae60', '#eb5757', '#00a3a3', '#d6336c', '#5c6ac4'];
                return palette[Math.abs(Number(id) || 0) % palette.length];
            }
            function trunc(text, n) {
                text = String(text || '');
                return text.length > n ? text.slice(0, n - 1) + '…' : text;
            }
            function rng(seed) {
                return function () {
                    seed = (seed * 1664525 + 1013904223) % 4294967296;
                    return seed / 4294967296;
                };
            }
            function toast(text) {
                $('#ww-toast').text(text).addClass('show');
                clearTimeout(toast.t);
                toast.t = setTimeout(function () { $('#ww-toast').removeClass('show'); }, 2200);
            }
            function angleLerp(a, b, t) {
                let d = b - a;
                while (d > Math.PI) d -= Math.PI * 2;
                while (d < -Math.PI) d += Math.PI * 2;
                return a + d * t;
            }

            // ---------- Tekstur dari canvas ----------
            function canvasTexture(w, h, draw) {
                const c = document.createElement('canvas');
                c.width = w;
                c.height = h;
                draw(c.getContext('2d'), w, h);
                const t = new THREE.CanvasTexture(c);
                t.encoding = THREE.sRGBEncoding;
                t.anisotropy = 4;
                return t;
            }
            function roundRect(g, x, y, w, h, r) {
                g.beginPath();
                g.moveTo(x + r, y);
                g.arcTo(x + w, y, x + w, y + h, r);
                g.arcTo(x + w, y + h, x, y + h, r);
                g.arcTo(x, y + h, x, y, r);
                g.arcTo(x, y, x + w, y, r);
                g.closePath();
            }
            // Label melayang: baris teks dengan latar membulat
            function labelSprite(lines, opt) {
                opt = Object.assign({ size: 34, bg: 'rgba(8,33,73,.82)', color: '#fff', height: 0.5, onTop: true, pad: 16, sub: '#cfe0ff' }, opt || {});
                const probe = document.createElement('canvas').getContext('2d');
                const fonts = lines.map(function (l, i) { return (i === 0 ? '800 ' : '600 ') + Math.round(opt.size * (i === 0 ? 1 : .78)) + 'px "Segoe UI", Arial, sans-serif'; });
                let w = 0;
                lines.forEach(function (l, i) { probe.font = fonts[i]; w = Math.max(w, probe.measureText(l).width); });
                const lineH = opt.size * 1.25;
                const cw = Math.ceil(w + opt.pad * 2);
                const ch = Math.ceil(lines.length * lineH + opt.pad * 1.1);
                const tex = canvasTexture(cw, ch, function (g) {
                    roundRect(g, 1, 1, cw - 2, ch - 2, Math.min(22, ch / 2.2));
                    g.fillStyle = opt.bg;
                    g.fill();
                    if (opt.border) {
                        g.lineWidth = 4;
                        g.strokeStyle = opt.border;
                        g.stroke();
                    }
                    g.textAlign = 'center';
                    g.textBaseline = 'middle';
                    lines.forEach(function (l, i) {
                        g.font = fonts[i];
                        g.fillStyle = i === 0 ? opt.color : opt.sub;
                        g.fillText(l, cw / 2, opt.pad * .55 + lineH * (i + .5));
                    });
                });
                const mat = new THREE.SpriteMaterial({ map: tex, transparent: true, depthTest: !opt.onTop, depthWrite: false });
                const sprite = new THREE.Sprite(mat);
                const scale = opt.height * lines.length;
                sprite.scale.set(scale * cw / ch, scale, 1);
                if (opt.onTop) sprite.renderOrder = 20;
                return sprite;
            }
            // Material bersama (cache) dan geometri sprite dipakai banyak objek, jadi tidak ikut dibuang
            function disposeObject(obj) {
                obj.traverse(function (o) {
                    if (o.geometry && !o.isSprite) o.geometry.dispose();
                    if (o.material) {
                        (Array.isArray(o.material) ? o.material : [o.material]).forEach(function (m) {
                            if (m.userData.cached) return;
                            if (m.map) m.map.dispose();
                            m.dispose();
                        });
                    }
                });
            }

            // ---------- Renderer, scene, kamera ----------
            const renderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: true });
            renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.5));
            renderer.outputEncoding = THREE.sRGBEncoding;
            renderer.shadowMap.enabled = true;
            renderer.shadowMap.type = THREE.PCFSoftShadowMap;

            const scene = new THREE.Scene();
            scene.fog = new THREE.Fog(0xbfe3ff, 120, 420);
            const camera = new THREE.PerspectiveCamera(55, 1, 0.1, 1200);

            const hemi = new THREE.HemisphereLight(0xdff1ff, 0x5f8f4a, 0.75);
            scene.add(hemi);
            const sun = new THREE.DirectionalLight(0xfff2d6, 1.05);
            sun.castShadow = true;
            sun.shadow.mapSize.set(2048, 2048);
            Object.assign(sun.shadow.camera, { left: -55, right: 55, top: 55, bottom: -55, near: 1, far: 220 });
            sun.shadow.bias = -0.0006;
            scene.add(sun, sun.target);

            const STD = {};
            function mat(color, extra) {
                const key = color + JSON.stringify(extra || {});
                if (!STD[key]) {
                    STD[key] = new THREE.MeshStandardMaterial(Object.assign({ color: color, roughness: .85, metalness: 0 }, extra || {}));
                    STD[key].userData.cached = true;
                }
                return STD[key];
            }
            function box(w, h, d, color, extra) {
                const m = new THREE.Mesh(new THREE.BoxGeometry(w, h, d), mat(color, extra));
                m.castShadow = true;
                m.receiveShadow = true;
                return m;
            }
            function cyl(rt, rb, h, color, seg, extra) {
                const m = new THREE.Mesh(new THREE.CylinderGeometry(rt, rb, h, seg || 16), mat(color, extra));
                m.castShadow = true;
                m.receiveShadow = true;
                return m;
            }

            // Bangunan, meja, dan benda yang bisa didekati dicatat di sini
            const interactables = [];
            const clickables = [];
            const obstacles = [];
            const animated = [];
            const world = { board: null, today: null, buildings: [], desks: {}, workGroup: null, items: [], notice: null };

            // ---------- Langit ----------
            function buildSky() {
                const geo = new THREE.SphereGeometry(900, 32, 16);
                const skyMat = new THREE.ShaderMaterial({
                    side: THREE.BackSide,
                    depthWrite: false,
                    fog: false,
                    uniforms: { top: { value: new THREE.Color(0x3d8be0) }, bottom: { value: new THREE.Color(0xd8efff) } },
                    vertexShader: 'varying vec3 vPos; void main(){ vPos = normalize(position); gl_Position = projectionMatrix * modelViewMatrix * vec4(position,1.0); }',
                    fragmentShader: 'uniform vec3 top; uniform vec3 bottom; varying vec3 vPos; void main(){ float h = clamp(vPos.y * 1.6 + 0.08, 0.0, 1.0); gl_FragColor = vec4(mix(bottom, top, pow(h, 0.8)), 1.0); }',
                });
                scene.add(new THREE.Mesh(geo, skyMat));

                const glow = new THREE.Sprite(new THREE.SpriteMaterial({
                    map: canvasTexture(256, 256, function (g) {
                        const r = g.createRadialGradient(128, 128, 10, 128, 128, 128);
                        r.addColorStop(0, 'rgba(255,250,220,1)');
                        r.addColorStop(.25, 'rgba(255,240,190,.8)');
                        r.addColorStop(1, 'rgba(255,240,190,0)');
                        g.fillStyle = r;
                        g.fillRect(0, 0, 256, 256);
                    }),
                    transparent: true, depthWrite: false, fog: false,
                }));
                glow.position.set(-260, 260, -480);
                glow.scale.set(160, 160, 1);
                scene.add(glow);

                // Awan berarak
                const cloudMat = new THREE.MeshLambertMaterial({ color: 0xffffff, flatShading: true });
                const r = rng(7);
                for (let i = 0; i < 16; i++) {
                    const cloud = new THREE.Group();
                    const puffs = 4 + Math.floor(r() * 4);
                    for (let p = 0; p < puffs; p++) {
                        const s = new THREE.Mesh(new THREE.IcosahedronGeometry(4 + r() * 5, 1), cloudMat);
                        s.position.set(p * 5 - puffs * 2.5, r() * 3, (r() - .5) * 6);
                        s.scale.y = .65;
                        cloud.add(s);
                    }
                    cloud.position.set((r() - .5) * 600, 55 + r() * 40, (r() - .5) * 600);
                    cloud.userData.speed = 1.5 + r() * 2.5;
                    scene.add(cloud);
                    animated.push(function (t, dt) {
                        cloud.position.x += cloud.userData.speed * dt;
                        if (cloud.position.x > 320) cloud.position.x = -320;
                    });
                }
            }

            // ---------- Pulau dan air ----------
            let waterTex;
            function buildIsland() {
                waterTex = canvasTexture(256, 256, function (g) {
                    g.fillStyle = '#2f9fd8';
                    g.fillRect(0, 0, 256, 256);
                    const r = rng(3);
                    for (let i = 0; i < 140; i++) {
                        g.strokeStyle = 'rgba(255,255,255,' + (0.08 + r() * 0.18) + ')';
                        g.lineWidth = 1 + r() * 2;
                        const x = r() * 256, y = r() * 256, l = 8 + r() * 22;
                        g.beginPath();
                        g.moveTo(x, y);
                        g.quadraticCurveTo(x + l / 2, y - 3, x + l, y);
                        g.stroke();
                    }
                });
                waterTex.wrapS = waterTex.wrapT = THREE.RepeatWrapping;
                waterTex.repeat.set(40, 40);
                const water = new THREE.Mesh(new THREE.PlaneGeometry(1600, 1600), new THREE.MeshStandardMaterial({ map: waterTex, roughness: .35, metalness: .1, transparent: true, opacity: .96 }));
                water.rotation.x = -Math.PI / 2;
                water.position.y = -1.2;
                water.receiveShadow = true;
                scene.add(water);
                animated.push(function (t) {
                    waterTex.offset.set(t * 0.006, t * 0.004);
                    water.position.y = -1.2 + Math.sin(t * .6) * .05;
                });

                const sand = cyl(ISLAND_R + 7, ISLAND_R + 11, 2.2, '#ecd9a6', 72);
                sand.position.y = -1.35;
                sand.castShadow = false;
                scene.add(sand);

                // Rumput dengan sedikit variasi warna per titik
                const grassGeo = new THREE.CylinderGeometry(ISLAND_R, ISLAND_R + 5, 2, 96, 1);
                const colors = [];
                const pos = grassGeo.attributes.position;
                const base = new THREE.Color('#7cc35f');
                const r = rng(11);
                for (let i = 0; i < pos.count; i++) {
                    const c = base.clone().offsetHSL((r() - .5) * .03, 0, (r() - .5) * .08);
                    colors.push(c.r, c.g, c.b);
                }
                grassGeo.setAttribute('color', new THREE.Float32BufferAttribute(colors, 3));
                const grass = new THREE.Mesh(grassGeo, new THREE.MeshStandardMaterial({ vertexColors: true, roughness: 1 }));
                grass.position.y = -1;
                grass.receiveShadow = true;
                scene.add(grass);
            }

            // ---------- Plaza, air mancur, bangku, lampu ----------
            function buildPlaza() {
                const plaza = cyl(15, 15, .3, '#d9d4c7', 48);
                plaza.position.y = .15;
                plaza.castShadow = false;
                scene.add(plaza);
                const ring = new THREE.Mesh(new THREE.RingGeometry(9, 9.6, 48), mat('#b9b1a0'));
                ring.rotation.x = -Math.PI / 2;
                ring.position.y = .31;
                scene.add(ring);

                // Air mancur
                const basin = cyl(4.4, 4.8, 1, '#c9c2b2', 32);
                basin.position.y = .8;
                scene.add(basin);
                const pool = new THREE.Mesh(new THREE.CylinderGeometry(3.9, 3.9, .2, 32), new THREE.MeshStandardMaterial({ color: 0x4fb6e8, roughness: .15, metalness: .2, transparent: true, opacity: .9 }));
                pool.position.y = 1.25;
                scene.add(pool);
                const pillar = cyl(.45, .6, 2.6, '#cfc8b8', 16);
                pillar.position.y = 2;
                scene.add(pillar);
                const bowl = cyl(1.6, .5, .5, '#cfc8b8', 24);
                bowl.position.y = 3.3;
                scene.add(bowl);
                obstacles.push({ x: 0, z: 0, r: 5.2 });

                const count = 420;
                const dropGeo = new THREE.BufferGeometry();
                const dropPos = new Float32Array(count * 3);
                const seeds = [];
                for (let i = 0; i < count; i++) seeds.push({ a: Math.random() * Math.PI * 2, s: .6 + Math.random() * .6, o: Math.random() });
                dropGeo.setAttribute('position', new THREE.BufferAttribute(dropPos, 3));
                const drops = new THREE.Points(dropGeo, new THREE.PointsMaterial({ color: 0xcdeeff, size: .18, transparent: true, opacity: .85 }));
                scene.add(drops);
                animated.push(function (t) {
                    for (let i = 0; i < count; i++) {
                        const sd = seeds[i];
                        const life = ((t * .55 + sd.o) % 1);
                        const rad = life * 3.2 * sd.s;
                        dropPos[i * 3] = Math.cos(sd.a) * rad;
                        dropPos[i * 3 + 1] = 3.6 + life * 4.2 - life * life * 6.4;
                        dropPos[i * 3 + 2] = Math.sin(sd.a) * rad;
                    }
                    dropGeo.attributes.position.needsUpdate = true;
                    pool.position.y = 1.25 + Math.sin(t * 3) * .02;
                });

                // Bangku dan lampu di sekeliling plaza
                for (let i = 0; i < 8; i++) {
                    const a = i / 8 * Math.PI * 2 + Math.PI / 8;
                    if (i % 2 === 0) {
                        const bench = new THREE.Group();
                        const seat = box(2.6, .15, .8, '#9a6b43');
                        seat.position.y = .55;
                        const back = box(2.6, .7, .12, '#9a6b43');
                        back.position.set(0, .95, -.36);
                        const l1 = box(.12, .55, .7, '#4a4f5a');
                        l1.position.set(-1.1, .27, 0);
                        const l2 = l1.clone();
                        l2.position.x = 1.1;
                        bench.add(seat, back, l1, l2);
                        bench.position.set(Math.cos(a) * 12, .3, Math.sin(a) * 12);
                        bench.lookAt(0, .3, 0);
                        scene.add(bench);
                    } else {
                        addLamp(Math.cos(a) * 13.2, Math.sin(a) * 13.2);
                    }
                }

                // Jalan setapak plaza ke Jalan Waktu
                const path = box(5, .12, ROAD_Z - ROAD_W / 2 - 15, '#d9d4c7');
                path.position.set(0, .07, 15 + (ROAD_Z - ROAD_W / 2 - 15) / 2);
                path.castShadow = false;
                scene.add(path);

                // Papan petunjuk
                const sign = new THREE.Group();
                const post = cyl(.12, .12, 3.2, '#6b4a2f', 8);
                post.position.y = 1.6;
                sign.add(post);
                // Tiap sisi papan hanya terbaca dari arah datangnya: dari plaza terlihat "Ke Jalan Waktu"
                const arrows = [['Ke Gedung Divisi', '#3b82f6', 0], ['Ke Jalan Waktu', '#f0a429', Math.PI]];
                arrows.forEach(function (a) {
                    const tex = canvasTexture(512, 128, function (g) {
                        g.fillStyle = a[1];
                        roundRect(g, 4, 4, 504, 120, 20);
                        g.fill();
                        g.fillStyle = '#fff';
                        g.font = '800 58px "Segoe UI", Arial';
                        g.textAlign = 'center';
                        g.textBaseline = 'middle';
                        g.fillText(a[0], 256, 66);
                    });
                    const board = new THREE.Mesh(new THREE.PlaneGeometry(2.6, .65), new THREE.MeshStandardMaterial({ map: tex }));
                    board.position.set(0, 2.75, a[2] ? -.03 : .03);
                    board.rotation.y = a[2];
                    sign.add(board);
                });
                const signBack = box(2.7, .75, .04, '#6b4a2f');
                signBack.position.y = 2.75;
                sign.add(signBack);
                sign.position.set(-4.5, 0, 34);
                scene.add(sign);
            }

            function addLamp(x, z) {
                const g = new THREE.Group();
                const pole = cyl(.09, .13, 4.2, '#2d3442', 8);
                pole.position.y = 2.1;
                const head = cyl(.35, .2, .35, '#2d3442', 8);
                head.position.y = 4.3;
                const bulb = new THREE.Mesh(new THREE.SphereGeometry(.26, 12, 8), new THREE.MeshStandardMaterial({ color: 0xfff1c1, emissive: 0xffd36b, emissiveIntensity: .9 }));
                bulb.position.y = 4.05;
                g.add(pole, head, bulb);
                g.position.set(x, 0, z);
                scene.add(g);
            }

            // ---------- Pohon, semak, bunga (instanced supaya ringan) ----------
            function buildNature() {
                const r = rng(21);
                const spots = [];
                const tooClose = function (x, z) {
                    if (Math.hypot(x, z) < 20) return true;
                    if (z > ROAD_Z - 26 && z < ROAD_Z + 26) return true;
                    if (Math.abs(x) < 5 && z > 0 && z < ROAD_Z) return true;
                    for (const b of world.buildings) {
                        if (Math.hypot(x - b.x, z - b.z) < 26) return true;
                    }
                    for (const s of spots) {
                        if (Math.hypot(x - s.x, z - s.z) < 4.2) return true;
                    }
                    return false;
                };
                for (let tries = 0; spots.length < 120 && tries < 4000; tries++) {
                    const a = r() * Math.PI * 2;
                    const d = Math.sqrt(r()) * (ISLAND_R - 6);
                    const x = Math.cos(a) * d, z = Math.sin(a) * d;
                    if (!tooClose(x, z)) spots.push({ x: x, z: z, s: .8 + r() * .7, pine: r() < .35, hue: r() });
                }

                const trunkGeo = new THREE.CylinderGeometry(.22, .32, 2.2, 6);
                const leafGeo = new THREE.IcosahedronGeometry(1.7, 0);
                const pineGeo = new THREE.ConeGeometry(1.5, 3.8, 7);
                const trunks = new THREE.InstancedMesh(trunkGeo, mat('#7a5233'), spots.length);
                const leaves = new THREE.InstancedMesh(leafGeo, new THREE.MeshStandardMaterial({ roughness: .9, flatShading: true }), spots.filter(function (s) { return !s.pine; }).length);
                const pines = new THREE.InstancedMesh(pineGeo, new THREE.MeshStandardMaterial({ roughness: .9, flatShading: true }), spots.filter(function (s) { return s.pine; }).length);
                const m = new THREE.Matrix4();
                const q = new THREE.Quaternion();
                const color = new THREE.Color();
                let li = 0, pi = 0;
                spots.forEach(function (s, i) {
                    m.compose(new THREE.Vector3(s.x, 1.1 * s.s, s.z), q, new THREE.Vector3(s.s, s.s, s.s));
                    trunks.setMatrixAt(i, m);
                    if (s.pine) {
                        m.compose(new THREE.Vector3(s.x, (2.2 + 1.6) * s.s, s.z), q, new THREE.Vector3(s.s, s.s, s.s));
                        pines.setMatrixAt(pi, m);
                        pines.setColorAt(pi++, color.set('#2f8f4e').offsetHSL((s.hue - .5) * .05, 0, (s.hue - .5) * .1));
                    } else {
                        q.setFromEuler(new THREE.Euler(0, s.hue * 6, 0));
                        m.compose(new THREE.Vector3(s.x, 3 * s.s, s.z), q, new THREE.Vector3(s.s * 1.1, s.s, s.s * 1.1));
                        leaves.setMatrixAt(li, m);
                        leaves.setColorAt(li++, color.set(s.hue < .15 ? '#f29fb5' : '#4caf50').offsetHSL((s.hue - .5) * .06, 0, (s.hue - .5) * .12));
                        q.identity();
                    }
                    obstacles.push({ x: s.x, z: s.z, r: .6 * s.s });
                });
                [trunks, leaves, pines].forEach(function (im) {
                    im.castShadow = true;
                    im.receiveShadow = true;
                    scene.add(im);
                });

                // Bunga warna-warni dan semak
                const flowerCount = 500;
                const flowers = new THREE.InstancedMesh(new THREE.SphereGeometry(.16, 6, 4), new THREE.MeshStandardMaterial({ roughness: .6 }), flowerCount);
                const flowerColors = ['#ff6b9a', '#ffd23f', '#ffffff', '#b37bff', '#ff8c42'];
                let fi = 0;
                for (let tries = 0; fi < flowerCount && tries < 8000; tries++) {
                    const a = r() * Math.PI * 2;
                    const d = 16 + r() * (ISLAND_R - 20);
                    const x = Math.cos(a) * d, z = Math.sin(a) * d;
                    if (z > ROAD_Z - 7 && z < ROAD_Z + 7) continue;
                    if (Math.abs(x) < 3.5 && z > 0 && z < ROAD_Z) continue;
                    m.compose(new THREE.Vector3(x, .15, z), new THREE.Quaternion(), new THREE.Vector3(1, 1, 1));
                    flowers.setMatrixAt(fi, m);
                    flowers.setColorAt(fi++, color.set(flowerColors[Math.floor(r() * flowerColors.length)]));
                }
                scene.add(flowers);

                const bushCount = 90;
                const bushes = new THREE.InstancedMesh(new THREE.IcosahedronGeometry(.9, 0), new THREE.MeshStandardMaterial({ roughness: 1, flatShading: true }), bushCount);
                let bi = 0;
                for (let tries = 0; bi < bushCount && tries < 4000; tries++) {
                    const a = r() * Math.PI * 2;
                    const d = 18 + r() * (ISLAND_R - 22);
                    const x = Math.cos(a) * d, z = Math.sin(a) * d;
                    if (z > ROAD_Z - 8 && z < ROAD_Z + 24) continue;
                    // Jalan setapak plaza ke Jalan Waktu (tempat pemain muncul) dibiarkan lapang
                    if (Math.abs(x) < 10 && z > 0 && z < ROAD_Z) continue;
                    if (world.buildings.some(function (b) { return Math.hypot(x - b.x, z - b.z) < 22; })) continue;
                    m.compose(new THREE.Vector3(x, .5, z), new THREE.Quaternion(), new THREE.Vector3(1 + r() * .6, .7 + r() * .4, 1 + r() * .6));
                    bushes.setMatrixAt(bi, m);
                    bushes.setColorAt(bi++, color.set('#3f9b4a').offsetHSL(0, 0, (r() - .5) * .1));
                }
                bushes.castShadow = true;
                scene.add(bushes);

                // Batu-batu kecil di pantai
                for (let i = 0; i < 26; i++) {
                    const a = r() * Math.PI * 2;
                    const rock = new THREE.Mesh(new THREE.DodecahedronGeometry(.8 + r() * 1.6, 0), mat('#9aa1a9', { flatShading: true }));
                    rock.position.set(Math.cos(a) * (ISLAND_R + 3), -.4, Math.sin(a) * (ISLAND_R + 3));
                    rock.rotation.set(r() * 3, r() * 3, 0);
                    rock.castShadow = true;
                    if (Math.abs(rock.position.z - ROAD_Z) > 9) scene.add(rock);
                }
            }

            // ---------- Hewan: burung, kupu-kupu, kucing kantor ----------
            function buildCritters() {
                const birdMat = new THREE.MeshBasicMaterial({ color: 0x2b3442, side: THREE.DoubleSide });
                for (let i = 0; i < 7; i++) {
                    const bird = new THREE.Group();
                    const wingGeo = new THREE.BufferGeometry();
                    wingGeo.setAttribute('position', new THREE.Float32BufferAttribute([0, 0, 0, 1.4, 0, -.4, 0, 0, -.7], 3));
                    const left = new THREE.Mesh(wingGeo, birdMat);
                    const right = new THREE.Mesh(wingGeo, birdMat);
                    right.scale.x = -1;
                    bird.add(left, right);
                    scene.add(bird);
                    const radius = 30 + i * 9, height = 28 + (i % 3) * 6, speed = .12 + (i % 4) * .03, phase = i * 1.3;
                    animated.push(function (t) {
                        const a = t * speed + phase;
                        bird.position.set(Math.cos(a) * radius, height + Math.sin(t * .7 + i) * 2, Math.sin(a) * radius);
                        bird.rotation.y = -a;
                        const flap = Math.sin(t * 9 + i) * .6;
                        left.rotation.z = flap;
                        right.rotation.z = -flap;
                    });
                }

                const flyColors = [0xff7eb6, 0xffd23f, 0x7ec8ff, 0xffffff, 0xb37bff];
                for (let i = 0; i < 12; i++) {
                    const fly = new THREE.Group();
                    const wing = new THREE.Mesh(new THREE.CircleGeometry(.16, 6), new THREE.MeshBasicMaterial({ color: flyColors[i % flyColors.length], side: THREE.DoubleSide }));
                    wing.position.x = .14;
                    const wing2 = wing.clone();
                    wing2.position.x = -.14;
                    fly.add(wing, wing2);
                    scene.add(fly);
                    const cx = (i % 2 ? 1 : -1) * (16 + i * 2.5), cz = -6 + (i % 5) * 4;
                    animated.push(function (t) {
                        const a = t * (.5 + i * .04) + i;
                        fly.position.set(cx + Math.cos(a) * 3, 1.2 + Math.sin(t * 2 + i) * .5, cz + Math.sin(a * 1.3) * 3);
                        fly.rotation.y = a;
                        wing.rotation.y = Math.sin(t * 18 + i) * .9;
                        wing2.rotation.y = -wing.rotation.y;
                    });
                }

                // Si Oyen, kucing kantor yang jalan-jalan di sekitar plaza
                const cat = new THREE.Group();
                const fur = '#f39c3d';
                const body = box(.5, .42, .9, fur);
                body.position.y = .45;
                const head = box(.46, .4, .4, fur);
                head.position.set(0, .78, .5);
                const earL = new THREE.Mesh(new THREE.ConeGeometry(.09, .2, 4), mat(fur));
                earL.position.set(-.14, 1.05, .5);
                const earR = earL.clone();
                earR.position.x = .14;
                const eyeL = box(.07, .07, .02, '#1b2536');
                eyeL.position.set(-.1, .82, .71);
                const eyeR = eyeL.clone();
                eyeR.position.x = .1;
                const tail = box(.1, .1, .6, fur);
                tail.position.set(0, .75, -.6);
                tail.rotation.x = .9;
                const legs = [];
                [[-.17, .3], [.17, .3], [-.17, -.3], [.17, -.3]].forEach(function (p) {
                    const leg = box(.12, .3, .12, '#e08a2c');
                    leg.position.set(p[0], .15, p[1]);
                    legs.push(leg);
                    cat.add(leg);
                });
                cat.add(body, head, earL, earR, eyeL, eyeR, tail);
                const catLabel = labelSprite(['Si Oyen 🐾'], { size: 30, height: .32 });
                catLabel.position.y = 1.55;
                cat.add(catLabel);
                cat.position.set(8, 0, 5);
                scene.add(cat);
                const catState = { target: new THREE.Vector3(8, 0, 5), wait: 2 };
                animated.push(function (t, dt) {
                    const to = catState.target.clone().sub(cat.position);
                    to.y = 0;
                    const dist = to.length();
                    if (dist < .3) {
                        catState.wait -= dt;
                        tail.rotation.z = Math.sin(t * 3) * .4;
                        if (catState.wait <= 0) {
                            const a = Math.random() * Math.PI * 2, d = 7 + Math.random() * 16;
                            catState.target.set(Math.cos(a) * d, 0, Math.sin(a) * d);
                            catState.wait = 2 + Math.random() * 5;
                        }
                        legs.forEach(function (l) { l.rotation.x = 0; });
                    } else {
                        to.normalize();
                        cat.position.addScaledVector(to, Math.min(dist, 1.6 * dt));
                        cat.rotation.y = angleLerp(cat.rotation.y, Math.atan2(to.x, to.z), .15);
                        legs.forEach(function (l, i) { l.rotation.x = Math.sin(t * 12 + (i % 2) * Math.PI) * .5; });
                    }
                });
                interactables.push({ type: 'cat', get x() { return cat.position.x; }, get z() { return cat.position.z; }, r: 2.4 });
                body.userData.interact = { type: 'cat' };
                clickables.push(body, head);
            }

            // ---------- Karakter ----------
            function makeCharacter(color, name, sub) {
                const g = new THREE.Group();
                const shirt = color || '#5c6ac4';
                const hip = new THREE.Group();
                hip.position.y = .78;
                const legGeo = new THREE.BoxGeometry(.22, .74, .24);
                legGeo.translate(0, -.37, 0);
                const legL = new THREE.Mesh(legGeo, mat('#2b3a55'));
                legL.position.x = -.14;
                const legR = new THREE.Mesh(legGeo, mat('#2b3a55'));
                legR.position.x = .14;
                [legL, legR].forEach(function (l) { l.castShadow = true; });
                hip.add(legL, legR);
                const torso = box(.62, .74, .36, shirt);
                torso.position.y = 1.15;
                const armGeo = new THREE.BoxGeometry(.17, .64, .18);
                armGeo.translate(0, -.3, 0);
                const armL = new THREE.Mesh(armGeo, mat(shirt));
                armL.position.set(-.41, 1.48, 0);
                const armR = new THREE.Mesh(armGeo, mat(shirt));
                armR.position.set(.41, 1.48, 0);
                [armL, armR].forEach(function (a) { a.castShadow = true; });
                const head = box(.5, .5, .48, '#f2c9a0');
                head.position.y = 1.8;
                const hair = box(.54, .2, .52, '#3b2a20');
                hair.position.y = 2.06;
                const hairBack = box(.54, .3, .12, '#3b2a20');
                hairBack.position.set(0, 1.9, -.22);
                const eyeL = box(.07, .09, .02, '#1b2536');
                eyeL.position.set(-.11, 1.83, .245);
                const eyeR = eyeL.clone();
                eyeR.position.x = .11;
                const smile = box(.16, .03, .02, '#9a4a3a');
                smile.position.set(0, 1.68, .245);
                const shadow = new THREE.Mesh(new THREE.CircleGeometry(.45, 16), new THREE.MeshBasicMaterial({ color: 0x000000, transparent: true, opacity: .18, depthWrite: false }));
                shadow.rotation.x = -Math.PI / 2;
                shadow.position.y = .03;
                g.add(hip, torso, armL, armR, head, hair, hairBack, eyeL, eyeR, smile, shadow);
                // Topi pesta saat event meriah (misalnya Jumat Ceria, Tahun Baru)
                if (world.partyHats) {
                    const hat = new THREE.Mesh(new THREE.ConeGeometry(.2, .45, 12), mat(shirt, { emissive: shirt, emissiveIntensity: .15 }));
                    hat.position.set(.06, 2.36, 0);
                    hat.rotation.z = -.15;
                    const pom = new THREE.Mesh(new THREE.SphereGeometry(.07, 8, 6), mat('#ffffff'));
                    pom.position.set(.12, 2.6, 0);
                    g.add(hat, pom);
                }
                const label = labelSprite(sub ? [name, sub] : [name], { size: 32, height: .3, border: shirt });
                label.position.y = sub ? 2.75 : 2.6;
                g.add(label);
                g.userData = { legL: legL, legR: legR, armL: armL, armR: armR, label: label, bubble: null, phase: Math.random() * 6, body: [torso, head] };
                return g;
            }
            function animateCharacter(ch, t, moving, speed) {
                const u = ch.userData;
                if (moving) {
                    const s = Math.sin(t * (speed > 6 ? 13 : 9) + u.phase) * (speed > 6 ? .9 : .65);
                    u.legL.rotation.x = s;
                    u.legR.rotation.x = -s;
                    u.armL.rotation.x = -s * .8;
                    u.armR.rotation.x = s * .8;
                } else {
                    u.legL.rotation.x *= .8;
                    u.legR.rotation.x *= .8;
                    u.armL.rotation.x = Math.sin(t * 1.6 + u.phase) * .05;
                    u.armR.rotation.x = -Math.sin(t * 1.6 + u.phase) * .05;
                }
            }
            function setBubble(ch, text) {
                if (ch.userData.bubble) {
                    ch.remove(ch.userData.bubble);
                    disposeObject(ch.userData.bubble);
                }
                const bubble = labelSprite([trunc(text, 46)], { size: 34, height: .36, bg: 'rgba(255,255,255,.96)', color: '#1b2536', border: '#7fb2ff' });
                bubble.position.y = 3.25;
                ch.add(bubble);
                ch.userData.bubble = bubble;
                ch.userData.bubbleUntil = performance.now() + 7000;
            }

            // ---------- Gedung divisi dan meja anggota ----------
            function divisionsOf(board) {
                const fromMembers = [];
                board.programmers.forEach(function (p) { if (fromMembers.indexOf(p.division) === -1) fromMembers.push(p.division); });
                return fromMembers.length ? fromMembers : (board.divisions || []).slice(0, 3);
            }
            function buildBuildings(board) {
                const divs = divisionsOf(board);
                const radius = 60;
                const spread = Math.min(38, 150 / Math.max(1, divs.length - 1));
                divs.forEach(function (d, i) {
                    const deg = -90 + (i - (divs.length - 1) / 2) * spread;
                    const a = deg * Math.PI / 180;
                    const x = Math.cos(a) * radius, z = Math.sin(a) * radius;
                    const color = BUILDING_COLORS[i % BUILDING_COLORS.length];
                    const g = new THREE.Group();
                    g.position.set(x, 0, z);
                    g.lookAt(0, 0, 0);
                    scene.add(g);

                    // Gedung menghadap plaza (sumbu +z lokal)
                    const W = 18, H = 8, D = 10;
                    const windows = canvasTexture(512, 256, function (gx) {
                        gx.fillStyle = '#eef2f8';
                        gx.fillRect(0, 0, 512, 256);
                        gx.fillStyle = color;
                        gx.fillRect(0, 0, 512, 22);
                        const r = rng(i + 5);
                        for (let row = 0; row < 2; row++) {
                            for (let col = 0; col < 7; col++) {
                                gx.fillStyle = r() < .55 ? '#ffe9a8' : '#9cc4e8';
                                gx.fillRect(22 + col * 70, 50 + row * 100, 48, 60);
                                gx.fillStyle = 'rgba(255,255,255,.5)';
                                gx.fillRect(22 + col * 70, 50 + row * 100, 48, 6);
                            }
                        }
                    });
                    const walls = [
                        new THREE.MeshStandardMaterial({ color: '#e9edf3' }), new THREE.MeshStandardMaterial({ color: '#e9edf3' }),
                        new THREE.MeshStandardMaterial({ color: color }), new THREE.MeshStandardMaterial({ color: '#d5dbe5' }),
                        new THREE.MeshStandardMaterial({ map: windows }), new THREE.MeshStandardMaterial({ color: '#e9edf3' }),
                    ];
                    const main = new THREE.Mesh(new THREE.BoxGeometry(W, H, D), walls);
                    main.position.y = H / 2;
                    main.castShadow = true;
                    main.receiveShadow = true;
                    g.add(main);
                    const roof = box(W + .8, .5, D + .8, color);
                    roof.position.y = H + .25;
                    g.add(roof);
                    const door = box(3, 3.6, .3, '#1e3a5f');
                    door.position.set(0, 1.8, D / 2 + .05);
                    const doorGlass = box(2.4, 3, .1, '#9cd2ff', { emissive: '#3a6ea5', emissiveIntensity: .25 });
                    doorGlass.position.set(0, 1.8, D / 2 + .22);
                    const awning = box(5, .2, 1.8, color);
                    awning.position.set(0, 3.9, D / 2 + .9);
                    g.add(door, doorGlass, awning);

                    // Papan nama divisi di atap
                    const signTex = canvasTexture(1024, 200, function (gx) {
                        gx.fillStyle = '#082149';
                        roundRect(gx, 4, 4, 1016, 192, 30);
                        gx.fill();
                        gx.strokeStyle = color;
                        gx.lineWidth = 10;
                        gx.stroke();
                        gx.fillStyle = '#fff';
                        gx.font = '900 110px "Segoe UI", Arial';
                        gx.textAlign = 'center';
                        gx.textBaseline = 'middle';
                        gx.fillText(String(d).toUpperCase(), 512, 106);
                    });
                    const sign = new THREE.Mesh(new THREE.PlaneGeometry(12, 2.35), new THREE.MeshStandardMaterial({ map: signTex, emissive: '#ffffff', emissiveMap: signTex, emissiveIntensity: .35 }));
                    sign.position.set(0, H + 1.9, D / 2 - 1);
                    const signBack = box(12.3, 2.6, .25, '#2d3442');
                    signBack.position.set(0, H + 1.9, D / 2 - 1.15);
                    g.add(signBack, sign);

                    // Teras di depan gedung
                    const patio = box(W + 4, .14, 18, '#cdd6e2');
                    patio.position.set(0, .07, D / 2 + 9);
                    patio.castShadow = false;
                    g.add(patio);
                    // Bendera berkibar di samping gedung
                    const flagPole = cyl(.08, .08, 9, '#cfd6e0', 8);
                    flagPole.position.set(W / 2 + 1.5, 4.5, D / 2 + 1);
                    const flagGeo = new THREE.PlaneGeometry(2.4, 1.4, 12, 1);
                    flagGeo.translate(1.2, 0, 0);
                    const flag = new THREE.Mesh(flagGeo, new THREE.MeshStandardMaterial({ color: color, side: THREE.DoubleSide }));
                    flag.position.set(W / 2 + 1.55, 8.2, D / 2 + 1);
                    g.add(flagPole, flag);
                    const fp = flagGeo.attributes.position;
                    const baseX = [];
                    for (let k = 0; k < fp.count; k++) baseX.push(fp.getX(k));
                    animated.push(function (t) {
                        for (let k = 0; k < fp.count; k++) fp.setZ(k, Math.sin(baseX[k] * 2.4 - t * 5 + i) * .18 * baseX[k] / 2.4);
                        fp.needsUpdate = true;
                    });

                    // Jalan setapak dari plaza ke gedung
                    const len = radius - 15 - (D / 2 + 18);
                    const path = box(4, .12, len, '#d9d4c7');
                    path.castShadow = false;
                    path.position.set(0, .06, D / 2 + 18 + len / 2);
                    g.add(path);

                    const wp = new THREE.Vector3();
                    main.getWorldPosition(wp);
                    world.buildings.push({ name: d, color: color, x: x, z: z, group: g, angle: a });
                    obstacles.push({ x: x, z: z, r: 9.5 });

                    // Meja anggota di teras: maksimal 4 per baris
                    const members = board.programmers.filter(function (p) { return p.division === d; });
                    members.forEach(function (p, k) {
                        const cols = Math.min(4, members.length);
                        const col = k % 4, row = Math.floor(k / 4);
                        const dx = (col - (cols - 1) / 2) * 4.4;
                        const dz = D / 2 + 5 + row * 5.5;
                        buildDesk(g, p, dx, dz);
                    });
                });
            }

            function deskScreenTexture(p) {
                const items = world.board.items.filter(function (i) { return Number(i.programmer_id) === p.id && ['baru', 'dikerjakan'].includes(i.status); })
                    .sort(function (a, b) { return String(a.target_date || '9999').localeCompare(String(b.target_date || '9999')); });
                return canvasTexture(512, 320, function (g) {
                    g.fillStyle = '#0d1b33';
                    g.fillRect(0, 0, 512, 320);
                    g.fillStyle = p.color;
                    g.fillRect(0, 0, 512, 64);
                    g.fillStyle = '#fff';
                    g.font = '800 30px "Segoe UI", Arial';
                    g.textBaseline = 'middle';
                    g.fillText(trunc(p.name, 22), 18, 33);
                    g.font = '600 20px "Segoe UI", Arial';
                    g.textAlign = 'right';
                    g.fillText(items.length + ' aktif', 494, 33);
                    g.textAlign = 'left';
                    if (!items.length) {
                        g.fillStyle = '#9fb3d6';
                        g.font = '600 24px "Segoe UI", Arial';
                        g.fillText('Tidak ada kerjaan aktif ☕', 18, 170);
                        return;
                    }
                    items.slice(0, 4).forEach(function (it, n) {
                        const y = 84 + n * 58;
                        g.fillStyle = '#e8eefc';
                        g.font = '700 21px "Segoe UI", Arial';
                        g.fillText(trunc(it.title, 32), 18, y + 12);
                        g.fillStyle = '#25385c';
                        roundRect(g, 18, y + 30, 380, 12, 6);
                        g.fill();
                        g.fillStyle = HEALTH_COLOR[it.health] || '#8a97ad';
                        roundRect(g, 18, y + 30, Math.max(12, 380 * Number(it.progress) / 100), 12, 6);
                        g.fill();
                        g.fillStyle = '#9fb3d6';
                        g.font = '600 17px "Segoe UI", Arial';
                        g.fillText(it.progress + '% · ' + fmt(it.target_date), 408, y + 37);
                    });
                    if (items.length > 4) {
                        g.fillStyle = '#9fb3d6';
                        g.font = '600 17px "Segoe UI", Arial';
                        g.fillText('+' + (items.length - 4) + ' lainnya', 18, 312);
                    }
                });
            }

            function buildDesk(parent, p, dx, dz) {
                const g = new THREE.Group();
                g.position.set(dx, 0, dz);
                const top = box(3.4, .14, 1.6, '#c89b6d');
                top.position.y = 1.02;
                g.add(top);
                [[-1.55, -.65], [1.55, -.65], [-1.55, .65], [1.55, .65]].forEach(function (l) {
                    const leg = box(.1, 1, .1, '#4a4f5a');
                    leg.position.set(l[0], .5, l[1]);
                    g.add(leg);
                });
                const stand = box(.12, .55, .12, '#2d3442');
                stand.position.set(-.4, 1.35, -.35);
                const frame = box(2.1, 1.35, .1, '#1b2536');
                frame.position.set(-.4, 2.1, -.35);
                const screen = new THREE.Mesh(new THREE.PlaneGeometry(1.98, 1.24), new THREE.MeshStandardMaterial({ map: deskScreenTexture(p), emissive: '#ffffff', emissiveIntensity: .55 }));
                screen.material.emissiveMap = screen.material.map;
                screen.position.set(-.4, 2.1, -.29);
                const mug = cyl(.1, .1, .22, p.color, 10);
                mug.position.set(1.2, 1.2, .2);
                const plant = new THREE.Mesh(new THREE.IcosahedronGeometry(.22, 0), mat('#3f9b4a', { flatShading: true }));
                plant.position.set(1.3, 1.35, -.45);
                const pot = cyl(.14, .1, .22, '#b5653f', 8);
                pot.position.set(1.3, 1.18, -.45);
                const lamp = new THREE.Mesh(new THREE.SphereGeometry(.09, 10, 8), new THREE.MeshStandardMaterial({ color: 0x8a97ad, emissive: 0x000000 }));
                lamp.position.set(.65, 1.15, .55);
                const chair = new THREE.Group();
                const seat = box(.8, .12, .8, '#2d3442');
                seat.position.y = .62;
                const back = box(.8, .9, .1, '#2d3442');
                back.position.set(0, 1.1, -.38);
                const post = cyl(.05, .05, .6, '#4a4f5a', 6);
                post.position.y = .3;
                chair.add(seat, back, post);
                chair.position.set(.7, 0, -2.1);
                g.add(stand, frame, screen, mug, plant, pot, lamp, chair);

                // Anggota yang sedang offline "berjaga" di mejanya, supaya kampus tidak sepi
                const npc = makeCharacter(p.color, p.name, 'di meja');
                npc.position.set(.7, 0, -1.4);
                npc.rotation.y = 0;
                g.add(npc);
                parent.add(g);

                [top, frame, screen].forEach(function (m) {
                    m.userData.interact = { type: 'member', id: p.id };
                    clickables.push(m);
                });
                npc.userData.body.forEach(function (m) {
                    m.userData.interact = { type: 'member', id: p.id };
                    clickables.push(m);
                });

                const wp = new THREE.Vector3();
                g.getWorldPosition(wp);
                world.desks[p.id] = { member: p, group: g, screen: screen, lamp: lamp, npc: npc, x: wp.x, z: wp.z };
                interactables.push({ type: 'member', id: p.id, x: wp.x, z: wp.z, r: 3.4 });
                animated.push(function (t) {
                    if (!npc.visible) return;
                    npc.userData.armL.rotation.x = -.9 + Math.sin(t * 14 + dx) * .12;
                    npc.userData.armR.rotation.x = -.9 + Math.sin(t * 14 + dx + 1.4) * .12;
                    npc.position.y = Math.abs(Math.sin(t * 1.4 + dx)) * .02;
                });
            }

            function refreshDeskScreens() {
                Object.keys(world.desks).forEach(function (id) {
                    const desk = world.desks[id];
                    const p = world.board.programmers.find(function (x) { return x.id === Number(id); });
                    if (!p) return;
                    desk.member = p;
                    const old = desk.screen.material.map;
                    desk.screen.material.map = deskScreenTexture(p);
                    desk.screen.material.emissiveMap = desk.screen.material.map;
                    desk.screen.material.needsUpdate = true;
                    old.dispose();
                });
            }

            // ---------- Papan kampus (ringkasan tim, tanpa perbandingan anggota) ----------
            function noticeTexture() {
                const b = world.board;
                const today = world.today;
                const active = b.items.filter(function (i) { return ['baru', 'dikerjakan'].includes(i.status); });
                const doing = active.filter(function (i) { return i.status === 'dikerjakan'; }).length;
                const dueWeek = active.filter(function (i) { return i.target_date && i.target_date >= today && i.target_date <= addDays(today, 7); }).length;
                const month = today.slice(0, 7);
                const doneMonth = b.items.filter(function (i) { return i.status === 'selesai' && String(i.finished_at || '').slice(0, 7) === month; }).length;
                const online = 1 + net.players.size;
                return canvasTexture(1024, 640, function (g) {
                    const grad = g.createLinearGradient(0, 0, 1024, 640);
                    grad.addColorStop(0, '#0b2a5c');
                    grad.addColorStop(1, '#1c5bb8');
                    g.fillStyle = grad;
                    roundRect(g, 0, 0, 1024, 640, 36);
                    g.fill();
                    g.fillStyle = '#fff';
                    g.font = '900 62px "Segoe UI", Arial';
                    g.fillText('Papan Kampus', 48, 92);
                    g.font = '600 30px "Segoe UI", Arial';
                    g.fillStyle = '#bcd0ee';
                    g.fillText(parseDate(today).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }), 48, 140);
                    const tiles = [['Kerjaan aktif', active.length, '#7fb2ff'], ['Sedang dikerjakan', doing, '#ffd36b'], ['Target 7 hari ke depan', dueWeek, '#ff9fb0'], ['Selesai bulan ini', doneMonth, '#7ee0a8']];
                    tiles.forEach(function (tile, k) {
                        const x = 48 + (k % 2) * 470, y = 180 + Math.floor(k / 2) * 170;
                        g.fillStyle = 'rgba(255,255,255,.1)';
                        roundRect(g, x, y, 450, 150, 24);
                        g.fill();
                        g.fillStyle = tile[2];
                        g.font = '900 76px "Segoe UI", Arial';
                        g.fillText(String(tile[1]), x + 28, y + 92);
                        g.fillStyle = '#e8eefc';
                        g.font = '700 28px "Segoe UI", Arial';
                        g.fillText(tile[0], x + 28, y + 132);
                    });
                    g.fillStyle = '#bcd0ee';
                    g.font = '600 28px "Segoe UI", Arial';
                    g.fillText('🟢 ' + online + ' orang di kampus sekarang  ·  Jalan Waktu ada di selatan ↓', 48, 600);
                });
            }
            function buildNotice() {
                const g = new THREE.Group();
                const l = box(.25, 5.6, .25, '#6b4a2f');
                l.position.set(-4.3, 2.8, 0);
                const r = l.clone();
                r.position.x = 4.3;
                const back = box(8.9, 5.7, .2, '#2d3442');
                back.position.y = 3.6;
                const face = new THREE.Mesh(new THREE.PlaneGeometry(8.6, 5.4), new THREE.MeshStandardMaterial({ map: noticeTexture(), emissive: '#ffffff', emissiveIntensity: .3 }));
                face.material.emissiveMap = face.material.map;
                face.position.set(0, 3.6, .12);
                const roof = box(9.6, .3, 1.1, '#9a6b43');
                roof.position.y = 6.6;
                g.add(l, r, back, face, roof);
                g.position.set(0, 0, -19);
                scene.add(g);
                world.notice = face;
                face.userData.interact = { type: 'board' };
                clickables.push(face);
                interactables.push({ type: 'board', x: 0, z: -17.5, r: 4.5 });
                obstacles.push({ x: 0, z: -19, r: 2 });
            }
            function refreshNotice() {
                if (!world.notice) return;
                const old = world.notice.material.map;
                world.notice.material.map = noticeTexture();
                world.notice.material.emissiveMap = world.notice.material.map;
                world.notice.material.needsUpdate = true;
                old.dispose();
            }

            // ---------- Jalan Waktu ----------
            function dayX(iso) {
                return days(world.today, iso) * DAY;
            }
            function buildRoad() {
                const total = PAST + FUTURE + 1;
                const pxDay = 48;
                const roadTex = canvasTexture(total * pxDay, 160, function (g, w, h) {
                    for (let d = 0; d < total; d++) {
                        const iso = addDays(world.today, d - PAST);
                        const date = parseDate(iso);
                        const weekend = date.getDay() === 0 || date.getDay() === 6;
                        const past = d < PAST;
                        g.fillStyle = d === PAST ? '#f0b429' : (past ? (weekend ? '#7b7f8a' : '#8a8e99') : (weekend ? '#4f5a70' : '#5d6880'));
                        g.fillRect(d * pxDay, 0, pxDay, h);
                        g.fillStyle = 'rgba(255,255,255,.18)';
                        g.fillRect(d * pxDay, 0, 2, h);
                        // Tulisan diputar supaya terbaca saat berjalan ke arah masa depan (sumbu +x)
                        g.save();
                        g.translate(d * pxDay + pxDay / 2, h / 2);
                        g.rotate(Math.PI / 2);
                        g.fillStyle = d === PAST ? '#1b2536' : 'rgba(255,255,255,.88)';
                        g.textAlign = 'center';
                        g.textBaseline = 'middle';
                        g.font = '800 21px "Segoe UI", Arial';
                        g.fillText(date.getDate() + ' ' + date.toLocaleDateString('id-ID', { month: 'short' }), -38, -7);
                        g.font = '700 14px "Segoe UI", Arial';
                        g.fillText(date.toLocaleDateString('id-ID', { weekday: 'short' }), -38, 13);
                        g.restore();
                    }
                    g.fillStyle = 'rgba(255,255,255,.75)';
                    for (let x = 0; x < w; x += 40) g.fillRect(x, h / 2 - 2, 22, 4);
                });
                const len = total * DAY;
                const road = new THREE.Mesh(new THREE.PlaneGeometry(len, ROAD_W), new THREE.MeshStandardMaterial({ map: roadTex, roughness: .9 }));
                road.rotation.x = -Math.PI / 2;
                road.position.set((FUTURE - PAST) * DAY / 2, .08, ROAD_Z);
                road.receiveShadow = true;
                scene.add(road);
                const x0 = -(PAST + .5) * DAY, x1 = (FUTURE + .5) * DAY;
                world.roadX0 = x0;
                world.roadX1 = x1;

                // Tepi jalan dan tiang dermaga saat jalan keluar dari pulau
                [-1, 1].forEach(function (side) {
                    const curb = box(len, .3, .3, '#e9edf3');
                    curb.position.set((x0 + x1) / 2, .15, ROAD_Z + side * (ROAD_W / 2 + .15));
                    scene.add(curb);
                });
                const islandEdge = Math.sqrt(ISLAND_R * ISLAND_R - (ROAD_Z + ROAD_W) * (ROAD_Z + ROAD_W));
                for (let x = Math.floor(islandEdge / 6) * 6; x <= x1; x += 6) {
                    [-1, 1].forEach(function (side) {
                        const pile = cyl(.35, .35, 3, '#7a5233', 8);
                        pile.position.set(x, -1.4, ROAD_Z + side * (ROAD_W / 2 - .3));
                        scene.add(pile);
                    });
                }
                for (let x = -Math.floor(islandEdge / 6) * 6; x >= x0; x -= 6) {
                    [-1, 1].forEach(function (side) {
                        const pile = cyl(.35, .35, 3, '#7a5233', 8);
                        pile.position.set(x, -1.4, ROAD_Z + side * (ROAD_W / 2 - .3));
                        scene.add(pile);
                    });
                }
                const deck = box(len, .5, ROAD_W + .8, '#8a6a4a');
                deck.position.set((x0 + x1) / 2, -.2, ROAD_Z);
                deck.castShadow = false;
                scene.add(deck);

                // Lampu setiap minggu dan papan tiap hari Senin
                for (let d = -PAST; d <= FUTURE; d++) {
                    const iso = addDays(world.today, d);
                    if (parseDate(iso).getDay() !== 1) continue;
                    addLamp(d * DAY, ROAD_Z - ROAD_W / 2 - .8);
                    const tag = labelSprite(['Senin, ' + fmt(iso)], { size: 30, height: .45, onTop: false, bg: d < 0 ? 'rgba(90,96,110,.9)' : 'rgba(28,91,184,.92)' });
                    tag.position.set(d * DAY, 5.3, ROAD_Z - ROAD_W / 2 - .8);
                    scene.add(tag);
                }

                // Ujung jalan: masa lalu dan masa depan
                const pastSign = labelSprite(['← ' + PAST + ' hari lalu'], { size: 34, height: .6, onTop: false, bg: 'rgba(90,96,110,.92)' });
                pastSign.position.set(x0 + 2, 3, ROAD_Z);
                const futureSign = labelSprite([FUTURE + ' hari ke depan →'], { size: 34, height: .6, onTop: false, bg: 'rgba(28,91,184,.92)' });
                futureSign.position.set(x1 - 2, 3, ROAD_Z);
                scene.add(pastSign, futureSign);

                // Gerbang HARI INI
                const gate = new THREE.Group();
                const pL = box(.8, 7, .8, '#f0b429', { emissive: '#7a5200', emissiveIntensity: .25 });
                pL.position.set(0, 3.5, -ROAD_W / 2 - .6);
                const pR = pL.clone();
                pR.position.z = ROAD_W / 2 + .6;
                const beam = box(1.1, 1, ROAD_W + 2.8, '#f0b429', { emissive: '#7a5200', emissiveIntensity: .25 });
                beam.position.y = 7.3;
                const gateTex = canvasTexture(1024, 192, function (g) {
                    g.fillStyle = '#082149';
                    roundRect(g, 0, 0, 1024, 192, 40);
                    g.fill();
                    g.fillStyle = '#ffd36b';
                    g.font = '900 84px "Segoe UI", Arial';
                    g.textAlign = 'center';
                    g.textBaseline = 'middle';
                    g.fillText('HARI INI · ' + fmt(world.today, true).toUpperCase(), 512, 100);
                });
                // Papan gerbang dua muka supaya terbaca dari masa lalu maupun masa depan
                const gateMat = new THREE.MeshStandardMaterial({ map: gateTex, emissive: '#ffffff', emissiveMap: gateTex, emissiveIntensity: .5 });
                const gateSign = new THREE.Mesh(new THREE.PlaneGeometry(ROAD_W + 2, (ROAD_W + 2) * 192 / 1024), gateMat);
                gateSign.rotation.y = -Math.PI / 2;
                gateSign.position.set(-.58, 8.6, 0);
                const gateSign2 = new THREE.Mesh(gateSign.geometry, gateMat);
                gateSign2.rotation.y = Math.PI / 2;
                gateSign2.position.set(.58, 8.6, 0);
                const signPlate = box(1, 1.95, ROAD_W + 2.2, '#082149');
                signPlate.position.y = 8.6;
                const halo = new THREE.Mesh(new THREE.TorusGeometry(1.1, .12, 8, 32), new THREE.MeshStandardMaterial({ color: 0xffe08a, emissive: 0xffc94d, emissiveIntensity: 1 }));
                halo.position.y = 10.6;
                gate.add(pL, pR, beam, signPlate, gateSign, gateSign2, halo);
                gate.position.set(0, 0, ROAD_Z);
                scene.add(gate);
                animated.push(function (t) {
                    halo.rotation.y = t * 1.5;
                    halo.position.y = 10.6 + Math.sin(t * 2) * .25;
                });
                obstacles.push({ x: 0, z: ROAD_Z - ROAD_W / 2 - .6, r: .7 }, { x: 0, z: ROAD_Z + ROAD_W / 2 + .6, r: .7 });
            }

            // Kerjaan berdiri di sepanjang Jalan Waktu: utara dan selatan jalan bergantian
            function laneZ(lane) {
                const n = Math.floor(lane / 2);
                return lane % 2 === 0 ? ROAD_Z - ROAD_W / 2 - 2 - n * LANE_GAP : ROAD_Z + ROAD_W / 2 + 2 + n * LANE_GAP;
            }
            function buildWork() {
                if (world.workGroup) {
                    scene.remove(world.workGroup);
                    disposeObject(world.workGroup);
                    for (let i = interactables.length - 1; i >= 0; i--) if (interactables[i].type === 'item') interactables.splice(i, 1);
                    for (let i = clickables.length - 1; i >= 0; i--) if (clickables[i].userData.interact && clickables[i].userData.interact.type === 'item') clickables.splice(i, 1);
                }
                const group = new THREE.Group();
                world.workGroup = group;
                scene.add(group);

                const from = addDays(world.today, -PAST), to = addDays(world.today, FUTURE);
                const bars = world.board.items.filter(function (i) { return i.status !== 'ditolak'; }).map(function (i) {
                    const done = i.status === 'selesai' && i.finished_at ? String(i.finished_at).slice(0, 10) : null;
                    const start = i.start_date || i.target_date || i.predicted_date || done;
                    if (!start) return null;
                    const planEnd = done || i.target_date || i.predicted_date || start;
                    const end = planEnd < start ? start : planEnd;
                    const overrun = !done && i.health === 'telat' && i.predicted_date && i.predicted_date > end ? i.predicted_date : null;
                    return { item: i, start: start, end: end, overrun: overrun, visualEnd: overrun || end };
                }).filter(function (b) { return b && b.start <= to && b.visualEnd >= from; })
                    .sort(function (a, b) { return a.start < b.start ? -1 : (a.start > b.start ? 1 : a.item.id - b.item.id); });

                const laneEnds = [];
                world.items = [];
                bars.forEach(function (b) {
                    let lane = laneEnds.findIndex(function (end) { return end < b.start; });
                    if (lane === -1) {
                        lane = laneEnds.length;
                        laneEnds.push(b.visualEnd);
                    } else {
                        laneEnds[lane] = b.visualEnd;
                    }
                    const s = b.start < from ? from : b.start;
                    const e = b.end > to ? to : b.end;
                    const x0 = dayX(s) - DAY / 2 + .15, x1 = dayX(e) + DAY / 2 - .15;
                    const z = laneZ(lane);
                    const i = b.item;
                    const color = HEALTH_COLOR[i.health] || '#8a97ad';
                    const done = i.status === 'selesai';
                    const owner = world.board.programmers.find(function (p) { return p.id === Number(i.programmer_id); });
                    const len = Math.max(.6, x1 - x0);

                    if (e >= s) {
                        const base = box(len, .5, 1.5, new THREE.Color(color).lerp(new THREE.Color('#ffffff'), done ? .25 : .55).getStyle());
                        base.position.set(x0 + len / 2, 1.1, z);
                        group.add(base);
                        const fillLen = Math.max(.2, len * (done ? 1 : Number(i.progress) / 100));
                        const fill = box(fillLen, .56, 1.56, color, { emissive: color, emissiveIntensity: .15 });
                        fill.position.set(x0 + fillLen / 2, 1.1, z);
                        group.add(fill);
                        [x0 + .3, x1 - .3].forEach(function (px) {
                            const post = box(.18, .85, .18, '#5c6577');
                            post.position.set(px, .43, z);
                            group.add(post);
                        });
                        base.userData.interact = fill.userData.interact = { type: 'item', id: i.id };
                        clickables.push(base, fill);

                        const label = labelSprite([trunc(i.title, 30), (owner ? owner.name : 'Belum ditugaskan') + ' · ' + (done ? 'Selesai' : i.progress + '%')], { size: 30, height: .34, onTop: false, bg: 'rgba(8,33,73,.85)', border: color });
                        label.position.set(x0 + Math.min(len / 2, 3.2), 2.35, z);
                        group.add(label);

                        if (owner) {
                            const flag = cyl(.32, .32, .12, owner.color, 16);
                            flag.rotation.x = Math.PI / 2;
                            flag.position.set(x0 + .2, 1.1, z + .8);
                            group.add(flag);
                        }
                        if (i.urgent) {
                            const warn = labelSprite(['⚠ Mendesak'], { size: 28, height: .3, onTop: false, bg: 'rgba(229,72,77,.95)' });
                            warn.position.set(x0 + Math.min(len / 2, 3.2), 3.05, z);
                            group.add(warn);
                        }
                    }
                    if (b.overrun) {
                        const os = addDays(b.end, 1) < from ? from : addDays(b.end, 1);
                        const oe = b.overrun > to ? to : b.overrun;
                        if (oe >= os) {
                            const ox0 = dayX(os) - DAY / 2, ox1 = dayX(oe) + DAY / 2;
                            const over = new THREE.Mesh(new THREE.BoxGeometry(ox1 - ox0, .35, 1.2), new THREE.MeshStandardMaterial({ color: '#ffb3bb', transparent: true, opacity: .55 }));
                            over.position.set((ox0 + ox1) / 2, 1.1, z);
                            group.add(over);
                        }
                    }
                    world.items.push({ item: i, x0: x0, x1: x1, z: z, owner: owner });
                    interactables.push({ type: 'item', id: i.id, x0: x0, x1: x1, z: z, r: 2.3 });
                });
            }

            // ---------- Event harian dan kata motivasi ----------
            // Hari libur yang tanggalnya bergeser tiap tahun (Hijriah, Imlek, Paskah, Waisak, Nyepi) berdasarkan perkiraan;
            // cocokkan dengan SKB 3 Menteri setiap tahun dan ubah di sini bila berbeda.
            const MOVING_HOLIDAYS = {
                '2026-01-16': ['isra', 'Isra Mikraj'], '2026-02-17': ['imlek', 'Tahun Baru Imlek'], '2026-03-19': ['nyepi', 'Hari Raya Nyepi'],
                '2026-03-20': ['lebaran', 'Idul Fitri 1447 H'], '2026-03-21': ['lebaran', 'Idul Fitri 1447 H (hari kedua)'], '2026-04-03': ['jumatagung', 'Wafat Isa Almasih'],
                '2026-04-05': ['paskah', 'Hari Paskah'], '2026-05-14': ['kenaikan', 'Kenaikan Isa Almasih'], '2026-05-27': ['iduladha', 'Idul Adha 1447 H'],
                '2026-05-31': ['waisak', 'Hari Raya Waisak'], '2026-06-16': ['hijriah', 'Tahun Baru Islam 1448 H'], '2026-08-25': ['maulid', 'Maulid Nabi Muhammad SAW'],
                '2027-01-05': ['isra', 'Isra Mikraj'], '2027-02-06': ['imlek', 'Tahun Baru Imlek'], '2027-03-08': ['nyepi', 'Hari Raya Nyepi'],
                '2027-03-10': ['lebaran', 'Idul Fitri 1448 H'], '2027-03-11': ['lebaran', 'Idul Fitri 1448 H (hari kedua)'], '2027-03-26': ['jumatagung', 'Wafat Isa Almasih'],
                '2027-03-28': ['paskah', 'Hari Paskah'], '2027-05-06': ['kenaikan', 'Kenaikan Isa Almasih'], '2027-05-17': ['iduladha', 'Idul Adha 1448 H'],
                '2027-05-20': ['waisak', 'Hari Raya Waisak'], '2027-06-06': ['hijriah', 'Tahun Baru Islam 1449 H'], '2027-08-15': ['maulid', 'Maulid Nabi Muhammad SAW'],
            };
            const RAMADAN = [['2026-02-18', '2026-03-19'], ['2027-02-08', '2027-03-09']];
            // Tanggal tetap tiap tahun: [jenis, nama, libur nasional?]
            const FIXED_DAYS = {
                '01-01': ['tahunbaru', 'Tahun Baru', true], '04-21': ['kartini', 'Hari Kartini', false], '05-01': ['buruh', 'Hari Buruh', true],
                '05-02': ['hardiknas', 'Hari Pendidikan Nasional', false], '05-20': ['harkitnas', 'Hari Kebangkitan Nasional', false], '06-01': ['pancasila', 'Hari Lahir Pancasila', true],
                '08-17': ['merdeka', 'HUT Kemerdekaan RI', true], '10-01': ['kesaktian', 'Hari Kesaktian Pancasila', false], '10-02': ['batik', 'Hari Batik Nasional', false],
                '10-28': ['sumpah', 'Hari Sumpah Pemuda', false], '11-10': ['pahlawan', 'Hari Pahlawan', false], '11-25': ['guru', 'Hari Guru Nasional', false],
                '12-22': ['ibu', 'Hari Ibu', false], '12-25': ['natal', 'Hari Raya Natal', true], '12-31': ['malamtahunbaru', 'Malam Tahun Baru', false],
            };
            const MERAH_PUTIH = ['#e5484d', '#ffffff'];
            // Tampilan tiap jenis hari besar: emoji, kalimat, warna, dekorasi
            const HOLIDAY_STYLE = {
                tahunbaru: ['🎆', 'Selamat Tahun Baru! Lembar baru, semangat baru.', ['#f0b429', '#ec4899', '#3b82f6'], ['fireworks', 'confetti', 'hats', 'plane', 'balloons']],
                malamtahunbaru: ['🎇', 'Hari terakhir tahun ini. Terima kasih untuk setahun kerja kerasnya!', ['#f0b429', '#8b5cf6', '#3b82f6'], ['fireworks', 'confetti', 'hats', 'plane']],
                isra: ['🌙', 'Selamat memperingati Isra Mikraj.', ['#10b981', '#f0b429'], ['crescent', 'lanterns']],
                imlek: ['🧧', 'Gong Xi Fa Cai! Semoga tahun ini penuh berkah.', ['#e5484d', '#f0b429'], ['lanterns', 'fireworks', 'bunting']],
                nyepi: ['🕯️', 'Selamat Hari Raya Nyepi. Hening sejenak, jernih kembali.', ['#f0b429', '#ffffff'], ['lanterns']],
                lebaran: ['🌙', 'Selamat Idul Fitri, mohon maaf lahir dan batin.', ['#10b981', '#f0b429', '#ffffff'], ['crescent', 'lanterns', 'bunting', 'fireworks', 'plane']],
                jumatagung: ['✝️', 'Selamat memperingati Jumat Agung.', ['#8b5cf6', '#ffffff'], ['lanterns']],
                paskah: ['🐣', 'Selamat Paskah!', ['#f0b429', '#ec4899', '#22c1c3'], ['balloons', 'bunting']],
                kenaikan: ['🕊️', 'Selamat memperingati Kenaikan Isa Almasih.', ['#3b82f6', '#ffffff'], ['balloons']],
                iduladha: ['🌙', 'Selamat Idul Adha. Semoga berkah untuk semua.', ['#10b981', '#f0b429'], ['crescent', 'lanterns', 'bunting']],
                waisak: ['🪷', 'Selamat Hari Raya Waisak.', ['#f0b429', '#f97316'], ['lanterns']],
                hijriah: ['🌙', 'Selamat Tahun Baru Islam.', ['#10b981', '#f0b429'], ['crescent', 'lanterns']],
                maulid: ['🌙', 'Selamat memperingati Maulid Nabi.', ['#10b981', '#f0b429'], ['crescent', 'lanterns', 'bunting']],
                kartini: ['🌸', 'Habis gelap terbitlah terang. Selamat Hari Kartini!', ['#ec4899', '#f0b429'], ['bunting', 'balloons']],
                buruh: ['🛠️', 'Selamat Hari Buruh untuk semua pekerja hebat!', ['#e5484d', '#f0b429'], ['bunting', 'confetti']],
                hardiknas: ['📚', 'Belajar tidak pernah selesai. Selamat Hari Pendidikan!', ['#3b82f6', '#f0b429'], ['bunting', 'kites']],
                harkitnas: ['🌅', 'Bangkit, bergerak, berkarya!', MERAH_PUTIH, ['merahputih', 'bunting']],
                pancasila: ['🦅', 'Selamat Hari Lahir Pancasila.', MERAH_PUTIH, ['merahputih', 'bunting']],
                merdeka: ['🎆', 'Dirgahayu Republik Indonesia! Merdeka!', MERAH_PUTIH, ['merahputih', 'bunting', 'fireworks', 'balloons', 'plane', 'confetti']],
                kesaktian: ['🦅', 'Mengenang Hari Kesaktian Pancasila.', MERAH_PUTIH, ['merahputih']],
                batik: ['🎨', 'Hari ini pakai batik? Selamat Hari Batik Nasional!', ['#8b5e3c', '#f0b429', '#1e3a8a'], ['bunting', 'kites']],
                sumpah: ['✊', 'Satu nusa, satu bangsa, satu bahasa. Selamat Hari Sumpah Pemuda!', MERAH_PUTIH, ['merahputih', 'bunting']],
                pahlawan: ['🎖️', 'Terima kasih para pahlawan. Selamat Hari Pahlawan!', MERAH_PUTIH, ['merahputih', 'bunting']],
                guru: ['🍎', 'Terima kasih untuk semua yang pernah mengajari kita.', ['#e5484d', '#10b981'], ['bunting', 'balloons']],
                ibu: ['💐', 'Selamat Hari Ibu!', ['#ec4899', '#f0b429'], ['balloons', 'bunting']],
                natal: ['🎄', 'Selamat Hari Natal! Damai untuk kita semua.', ['#e5484d', '#10b981', '#f0b429'], ['xmas', 'lanterns', 'bunting', 'plane']],
                programmer: ['💻', 'Hari ke-256 tahun ini: Selamat Hari Programmer!', ['#22c1c3', '#8b5cf6', '#10b981'], ['confetti', 'balloons', 'hats', 'plane']],
            };
            // Tema hari: index 0 = Minggu
            const WEEKDAY_THEME = [
                ['☕', 'Minggu Recharge', ['Isi ulang tenaga, Senin menunggu.', 'Kalau masuk hari ini, terima kasih ekstra!', 'Istirahat juga bagian dari produktif.'], ['#f97316', '#f0b429'], ['lanterns', 'kites']],
                ['🚀', 'Senin Semangat', ['Awal pekan, saatnya pasang target!', 'Pekan baru, lembar baru.', 'Mulai dari kerjaan yang paling penting dulu.', 'Cek Jalan Waktu, susun rencana sepekan.'], ['#3b82f6', '#22c1c3', '#f0b429'], ['bunting', 'balloons', 'plane']],
                ['🎯', 'Selasa Fokus', ['Satu fokus, satu selesai.', 'Kurangi distraksi, tambah progres.', 'Selasa adalah hari terbaik untuk menyelesaikan yang tertunda.'], ['#8b5cf6', '#3b82f6', '#22c1c3'], ['kites']],
                ['⛰️', 'Rabu Tengah Pekan', ['Setengah jalan sudah lewat, tinggal setengah lagi!', 'Cek lagi target pekan ini, masih di jalur?', 'Tengah pekan, tetap jaga ritme.'], ['#10b981', '#f0b429', '#3b82f6'], ['balloons', 'bunting']],
                ['🔧', 'Kamis Konsisten', ['Pelan tapi konsisten mengalahkan cepat tapi putus-putus.', 'Satu hari lagi menuju Jumat, rapikan yang tersisa.', 'Konsisten itu kunci.'], ['#f97316', '#f0b429', '#10b981'], ['kites', 'bunting']],
                ['🎉', 'Jumat Ceria', ['Besok weekend! Tutup pekan dengan manis.', 'Rapikan kerjaan, catat progres, lalu nikmati akhir pekan.', 'Jumat: selesaikan, rayakan, istirahat.'], ['#ec4899', '#f0b429', '#22c1c3', '#8b5cf6'], ['confetti', 'balloons', 'bunting', 'hats', 'plane']],
                ['🌴', 'Sabtu Santai', ['Kalau masuk hari ini, terima kasih ekstra! Pelan-pelan saja.', 'Weekend: waktunya isi ulang tenaga.', 'Santai dulu, Senin kita gas lagi.'], ['#22c1c3', '#f0b429'], ['kites', 'lanterns']],
            ];

            const QUOTES = [
                'Kerjaan besar selesai karena langkah kecil yang dilakukan setiap hari.', 'Progres sedikit tetap progres.', 'Mulai saja dulu, sempurnakan sambil jalan.',
                'Fokus pada satu hal sampai selesai, baru pindah ke hal berikutnya.', 'Bug hari ini adalah pelajaran untuk besok.', 'Target jelas membuat langkah lebih ringan.',
                'Tim yang saling bantu selalu lebih cepat sampai.', 'Jangan menunggu mood, bangun kebiasaan.', 'Selesai lebih baik daripada sempurna tapi tidak pernah rilis.',
                'Catat progres, karena yang tercatat bisa diperbaiki.', 'Hari ini cukup lebih baik sedikit dari kemarin.', 'Kesalahan kecil yang ketahuan cepat itu kabar baik.',
                'Kerjakan yang sulit di pagi hari, saat tenaga masih penuh.', 'Istirahat sejenak bukan berarti berhenti.', 'Satu kerjaan tuntas lebih bernilai daripada sepuluh yang setengah jalan.',
                'Bertanya itu tanda serius, bukan tanda lemah.', 'Rencana yang baik dimulai dari daftar yang jelas.', 'Kecepatan datang dari kebiasaan, bukan dari terburu-buru.',
                'Jaga ritme, bukan cuma kecepatan.', 'Setiap baris kode yang rapi adalah hadiah untuk diri sendiri di masa depan.', 'Kerja cerdas dimulai dari memahami masalahnya dulu.',
                'Hari yang produktif dimulai dari satu keputusan: mulai.', 'Tidak ada kerjaan yang terlalu kecil untuk dikerjakan dengan baik.', 'Komunikasi yang jelas menghemat banyak waktu.',
                'Kalau buntu, coba jelaskan masalahnya ke teman.', 'Pecah kerjaan besar jadi potongan kecil, lalu habiskan satu per satu.', 'Deadline bukan musuh, tapi kompas.',
                'Yang konsisten akan mengalahkan yang berbakat tapi malas.', 'Rayakan kemenangan kecil, itu bahan bakar untuk yang besar.', 'Belajar satu hal baru hari ini, sekecil apa pun.',
                'Kualitas adalah kebiasaan, bukan kebetulan.', 'Jangan biarkan kerjaan kecil menumpuk jadi gunung.', 'Dokumentasi hari ini menyelamatkan kebingungan besok.',
                'Pagi yang terencana membuat sore lebih tenang.', 'Tetap tenang, satu per satu pasti beres.', 'Kerjaan yang dibagikan terasa lebih ringan.',
                'Hargai proses, hasil akan mengikuti.', 'Kopi boleh dingin, semangat jangan.', 'Kerjakan hari ini apa yang bisa membuat besok lebih mudah.',
                'Tidak harus cepat, yang penting terus bergerak.', 'Setiap ahli dulunya pemula yang tidak menyerah.', 'Masalah rumit biasanya punya solusi sederhana yang belum ketemu.',
                'Bekerja bersama, menang bersama.', 'Ambil napas, rapikan prioritas, lanjut lagi.', 'Satu langkah maju tetap lebih baik dari diam di tempat.',
                'Kesabaran adalah bagian dari debugging.', 'Jangan takut mengulang, yang penting hasilnya benar.', 'Kerjaan selesai hari ini adalah ruang kosong untuk ide baru besok.',
                'Pastikan yang penting tidak kalah oleh yang mendesak.', 'Perbaiki sedikit demi sedikit, lama-lama jadi bukit.', 'Semangat itu menular, mulailah dari diri sendiri.',
                'Kerja keras hari ini adalah cerita bangga di kemudian hari.', 'Bila ragu, uji dulu.', 'Hasil terbaik lahir dari fokus, bukan dari multitasking.',
                'Saling sapa di pagi hari bikin kerja terasa lebih ringan.', 'Kegagalan adalah data, bukan vonis.', 'Rapikan meja, rapikan pikiran.',
                'Terima kasih untuk kerja kerasnya kemarin. Ayo lanjut hari ini!', 'Jangan menunda yang bisa selesai dalam lima menit.', 'Target yang ditulis lebih mungkin tercapai.',
                'Ide bagus tidak ada artinya tanpa eksekusi.', 'Hari ini adalah kesempatan untuk membuat sesuatu yang berguna.', 'Kalau lelah, istirahat, bukan menyerah.',
                'Kerja tuntas, pulang tenang.', 'Kesederhanaan adalah kecanggihan tertinggi.', 'Progres hari ini mungkin kecil, tapi tetap dihitung.',
                'Satu kebiasaan baik bisa mengubah satu tahun penuh.', 'Lebih baik bertanya sekarang daripada memperbaiki nanti.', 'Kerjakan dengan hati, hasilnya akan terasa.',
                'Mulai dari yang paling ditakuti, sisanya terasa mudah.', 'Tim hebat dibangun dari kepercayaan kecil setiap hari.', 'Fokus pada solusi, bukan pada siapa yang salah.',
                'Kode yang bekerja hari ini bisa lebih rapi besok.', 'Setiap tiket yang ditutup adalah satu beban yang hilang.', 'Waktu terbaik untuk mulai adalah sekarang.',
                'Teruslah belajar, dunia terus berubah.', 'Rencanakan pekerjaanmu, kerjakan rencanamu.', 'Senyum dulu, baru buka laptop.',
                'Yang penting bukan seberapa sibuk, tapi seberapa berarti.', 'Uji kecil-kecilan sebelum rilis besar.', 'Kepala dingin menghasilkan keputusan yang jernih.',
                'Jangan bandingkan bab satu kita dengan bab dua puluh orang lain.', 'Hari ini kita selangkah lebih dekat ke target.', 'Apa pun yang dikerjakan, kerjakan sepenuh hati.',
                'Ketelitian hari ini mencegah lembur esok hari.', 'Sedikit demi sedikit, lama-lama selesai juga.', 'Disiplin adalah jembatan antara target dan pencapaian.',
                'Bantu satu rekan hari ini, besok mungkin kita yang dibantu.', 'Momentum dibangun dari kerjaan kecil yang selesai.', 'Kerjakan, cek, perbaiki, ulangi.',
                'Hal hebat tidak terjadi di zona nyaman.', 'Jadikan setiap masalah sebagai teka-teki yang seru.', 'Kerja yang baik berbicara lebih keras dari kata-kata.',
                'Selalu ada cara, tinggal dicari.', 'Pikirkan pengguna, kerjaan jadi lebih bermakna.', 'Tenang, deadline bisa dikejar kalau dimulai dari sekarang.',
                'Satu jam fokus penuh lebih berharga dari tiga jam setengah hati.', 'Langkah kecil yang konsisten lebih kuat dari loncatan besar sesekali.', 'Jaga kesehatan, karena tenaga adalah modal utama.',
                'Minum air putih, regangkan badan, lalu lanjut lagi.', 'Kerjaan beres, hati senang.', 'Setiap hari adalah kesempatan untuk memperbaiki sesuatu.',
                'Hari ini milik mereka yang mau memulai.', 'Rapikan hari ini, ringankan besok.', 'Kreativitas muncul saat kita berani mencoba.',
                'Sukses adalah jumlah dari usaha kecil yang diulang setiap hari.', 'Jadikan kualitas sebagai kebiasaan tim.', 'Mendengar dengan baik adalah separuh dari solusi.',
                'Tidak apa-apa pelan, asal tidak berhenti.', 'Kerjaan yang jelas lebih cepat selesai.', 'Prioritas yang tepat menghemat tenaga.',
                'Mulailah dengan akhir di pikiran.', 'Kerja tim membuat mimpi jadi nyata.', 'Hari ini, buat satu hal jadi lebih baik.',
                'Jangan biarkan kemarin menghabiskan terlalu banyak hari ini.', 'Kesempatan sering datang menyamar sebagai kerja keras.', 'Bangun, bergerak, berkarya.',
                'Lebih baik lambat dan benar daripada cepat tapi diulang.', 'Pikiran positif, kerja produktif.', 'Kalau sudah dimulai, setengahnya sudah selesai.',
                'Hargai waktu, karena ia tidak bisa diulang.', 'Tetap rendah hati, tetap belajar.', 'Satu kerjaan, satu senyum.',
                'Kerjaan yang menantang membuat kita tumbuh.', 'Ubah keluhan jadi usulan.', 'Jangan berhenti saat lelah, berhentilah saat selesai, lalu istirahat.',
                'Detail kecil membuat perbedaan besar.', 'Bersyukur atas kerjaan hari ini, ada yang sedang mencarinya.', 'Fokus, selesaikan, rayakan.',
                'Mimpi besar, mulai kecil, bergerak sekarang.', 'Semua yang hebat butuh waktu.', 'Satu baris kode, satu langkah maju.',
                'Terus maju, meski sedikit.', 'Kerja hari ini adalah investasi untuk hari esok.', 'Jangan lupa menikmati prosesnya.',
                'Saling percaya membuat kerja lebih cepat.', 'Pikirkan sederhana, kerjakan dengan baik.', 'Keberhasilan tim adalah keberhasilan kita semua.',
                'Belajar dari kemarin, hidup untuk hari ini, berharap untuk besok.', 'Ketika ragu, pecah jadi langkah lebih kecil.', 'Yang dicatat hari ini, tidak terlupa besok.',
                'Energi positif adalah perlengkapan kerja terbaik.', 'Tidak ada lift menuju sukses, kita harus naik tangga.', 'Sudah sejauh ini, tinggal sedikit lagi.',
                'Satu jam pagi yang tenang bisa menyelamatkan seharian.', 'Kerja rapi, hasil rapi.', 'Kita tidak harus hebat untuk memulai, tapi harus memulai untuk jadi hebat.',
                'Setiap target besar dimulai dari satu tiket kecil.', 'Jangan lupa makan siang, otak juga butuh bensin.', 'Berani mencoba, siap memperbaiki.',
                'Kesalahan hari ini adalah guru terbaik besok.', 'Langkah pertama selalu yang paling berat, setelah itu lebih ringan.', 'Tuntaskan, jangan tinggalkan setengah jalan.',
                'Kerjaan selesai, beban pikiran berkurang.', 'Hari biasa bisa jadi luar biasa dengan sedikit usaha ekstra.', 'Sukses tim dibangun dari sapaan dan bantuan kecil.',
                'Fokus pada apa yang bisa dikendalikan.', 'Kecil-kecil, lama-lama jadi rilis.', 'Pelan-pelan asal sampai.',
                'Rapikan antrean, lapangkan pikiran.', 'Setiap hari adalah hari yang tepat untuk belajar.', 'Nikmati tantangannya, rayakan solusinya.',
                'Komit kecil yang sering lebih aman dari komit besar yang jarang.', 'Kepercayaan dibangun dari janji kecil yang ditepati.', 'Lakukan yang terbaik, sisanya serahkan pada proses.',
                'Semangat pagi ini menentukan warna hari ini.', 'Jika belum berhasil, berarti belum selesai mencoba.', 'Tumbuh itu kadang tidak nyaman, dan itu wajar.',
                'Kerjaan yang dibagi jelas, selesai lebih cepat.', 'Bangga pada hasil kerja sendiri itu sehat.', 'Semua dimulai dari niat dan langkah pertama.',
                'Mari buat hari ini berarti.', 'Tetap semangat, kampus ini butuh kontribusimu!', 'Hari ini, satu kerjaan selesai sudah cukup untuk dibanggakan.',
            ];

            function seededShuffle(list, seed) {
                const r = rng(seed);
                const out = list.slice();
                for (let i = out.length - 1; i > 0; i--) {
                    const j = Math.floor(r() * (i + 1));
                    const tmp = out[i];
                    out[i] = out[j];
                    out[j] = tmp;
                }
                return out;
            }
            // Satu kalimat per hari, sama untuk semua orang, tidak berulang sampai daftar habis
            function quoteOf(iso) {
                const d = parseDate(iso);
                const dayOfYear = days(d.getFullYear() + '-01-01', iso);
                const order = seededShuffle(QUOTES, d.getFullYear() * 7 + 3);
                return order[dayOfYear % order.length];
            }
            function programmerDay(year) {
                return addDays(year + '-01-01', 255);
            }
            function holidayOf(iso) {
                const fixed = FIXED_DAYS[iso.slice(5)];
                if (fixed) return { kind: fixed[0], name: fixed[1], off: fixed[2] };
                const moving = MOVING_HOLIDAYS[iso];
                if (moving) return { kind: moving[0], name: moving[1], off: true };
                if (iso === programmerDay(iso.slice(0, 4))) return { kind: 'programmer', name: 'Hari Programmer', off: false };
                return null;
            }
            function makeEvent(key, emoji, title, sub, colors, decor, priority) {
                return { key: key, emoji: emoji, title: title, sub: sub, colors: colors, decor: decor, priority: priority };
            }
            // Daftar event untuk satu tanggal, diurutkan dari yang paling penting
            function eventsFor(iso, withTeam) {
                const list = [];
                const date = parseDate(iso);
                const weekNo = Math.floor(days('2026-01-05', iso) / 7);

                const h = holidayOf(iso);
                if (h) {
                    const st = HOLIDAY_STYLE[h.kind] || ['🎈', 'Selamat ' + h.name + '!', ['#3b82f6', '#f0b429'], ['bunting']];
                    list.push(makeEvent('h-' + h.kind, st[0], h.name, st[1] + (h.off ? ' Hari ini libur nasional.' : ''), st[2], st[3], 100));
                }
                RAMADAN.forEach(function (r) {
                    if (iso >= r[0] && iso <= r[1]) {
                        list.push(makeEvent('ramadan', '🌙', 'Bulan Ramadan', 'Selamat menunaikan ibadah puasa. Jaga energi, atur ritme kerja.', ['#10b981', '#f0b429'], ['crescent', 'lanterns'], 70));
                    }
                });
                const tomorrow = holidayOf(addDays(iso, 1));
                if (tomorrow && tomorrow.off && !(h && h.off)) {
                    list.push(makeEvent('besoklibur', '🏖️', 'Besok libur: ' + tomorrow.name, 'Rapikan kerjaan hari ini supaya libur terasa tenang.', ['#22c1c3', '#f0b429'], ['balloons', 'confetti'], 80));
                }
                if (date.getMonth() === 7 && !(h && h.kind === 'merdeka')) {
                    list.push(makeEvent('agustusan', '🎊', 'Semarak Agustusan', 'Bulan kemerdekaan! Merdeka dari bug dan kerjaan telat.', MERAH_PUTIH, ['merahputih', 'bunting'], 40));
                }

                const day = date.getDate();
                const lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();
                if (day === 1) {
                    list.push(makeEvent('gajian', '💰', 'Awal Bulan & Hari Gajian', 'Bulan baru, semangat baru. Selamat gajian!', ['#f0b429', '#10b981'], ['coins', 'confetti', 'plane'], 90));
                } else if (day === 15) {
                    list.push(makeEvent('tengahbulan', '🌓', 'Pertengahan Bulan', 'Setengah bulan sudah lewat. Cek lagi target bulan ini.', ['#3b82f6', '#f0b429'], ['kites'], 30));
                } else if (day >= lastDay - 1) {
                    list.push(makeEvent('akhirbulan', '📦', 'Akhir Bulan', 'Waktunya tutup buku. Kejar yang tersisa bulan ini!', ['#f97316', '#3b82f6'], ['plane', 'bunting'], 60));
                }

                if (withTeam && world.board) {
                    const items = world.board.items;
                    const monday = addDays(iso, -((date.getDay() + 6) % 7));
                    const doneOn = function (i) { return i.status === 'selesai' ? String(i.finished_at || '').slice(0, 10) : ''; };
                    const doneToday = items.filter(function (i) { return doneOn(i) === iso; }).length;
                    const doneWeek = items.filter(function (i) { const d = doneOn(i); return d && d >= monday && d <= iso; }).length;
                    const active = items.filter(function (i) { return ['baru', 'dikerjakan'].includes(i.status); });
                    const late = active.filter(function (i) { return i.health === 'telat'; }).length;
                    if (doneWeek >= 3) list.push(makeEvent('timpekan', '🏆', 'Pekan Produktif', doneWeek + ' kerjaan tim selesai pekan ini. Mantap!', ['#f0b429', '#10b981', '#3b82f6'], ['confetti', 'balloons'], 55));
                    if (doneToday) list.push(makeEvent('timhariini', '✅', doneToday + ' kerjaan selesai hari ini', 'Satu per satu, tim terus maju.', ['#10b981', '#22c1c3'], ['confetti'], 50));
                    if (active.length && !late) list.push(makeEvent('timtanpatelat', '🎯', 'Tanpa kerjaan telat', 'Semua kerjaan aktif masih di jalur. Pertahankan!', ['#10b981', '#3b82f6'], ['balloons'], 45));
                    if (world.board.summary && world.board.summary.unassigned === 0 && active.length) list.push(makeEvent('timantrean', '📭', 'Antrean kosong', 'Semua kerjaan sudah punya pemilik.', ['#22c1c3', '#3b82f6'], [], 20));
                }

                const w = WEEKDAY_THEME[date.getDay()];
                list.push(makeEvent('hari-' + date.getDay(), w[0], w[1], w[2][weekNo % w[2].length], w[3], w[4], 10));
                return list.sort(function (a, b) { return b.priority - a.priority; });
            }

            function decorOf(events) {
                const set = [];
                events.slice(0, 3).forEach(function (e) {
                    e.decor.forEach(function (d) { if (set.indexOf(d) === -1) set.push(d); });
                });
                return set;
            }

            // Dekorasi 3D per event; dibangun ulang saat data diperbarui
            const eventAnims = [];
            function buildEvents() {
                if (world.eventGroup) {
                    scene.remove(world.eventGroup);
                    disposeObject(world.eventGroup);
                }
                eventAnims.length = 0;
                for (let i = obstacles.length - 1; i >= 0; i--) if (obstacles[i].event) obstacles.splice(i, 1);
                const group = new THREE.Group();
                world.eventGroup = group;
                scene.add(group);

                const events = world.events;
                const main = events[0];
                const colors = main.colors;
                const decor = decorOf(events);
                const pick = function (n) { return colors[n % colors.length]; };

                // Spanduk di pintu masuk plaza, menghadap plaza dan Jalan Waktu
                const bannerTex = canvasTexture(1024, 200, function (g) {
                    const grad = g.createLinearGradient(0, 0, 1024, 0);
                    colors.forEach(function (c, n) { grad.addColorStop(n / Math.max(1, colors.length - 1), c); });
                    g.fillStyle = grad;
                    roundRect(g, 0, 0, 1024, 200, 36);
                    g.fill();
                    g.fillStyle = 'rgba(8,33,73,.55)';
                    roundRect(g, 14, 14, 996, 172, 28);
                    g.fill();
                    g.fillStyle = '#fff';
                    g.textAlign = 'center';
                    g.textBaseline = 'middle';
                    g.font = '900 70px "Segoe UI", Arial';
                    g.fillText(main.emoji + ' ' + trunc(main.title, 26), 512, 82);
                    g.font = '600 32px "Segoe UI", Arial';
                    g.fillText(trunc(main.sub, 56), 512, 150);
                });
                const bannerMat = new THREE.MeshStandardMaterial({ map: bannerTex, emissive: '#ffffff', emissiveMap: bannerTex, emissiveIntensity: .35 });
                [[0, .06], [Math.PI, -.06]].forEach(function (f) {
                    const face = new THREE.Mesh(new THREE.PlaneGeometry(7.4, 1.45), bannerMat);
                    face.rotation.y = f[0];
                    face.position.set(0, 4.6, 16 + f[1]);
                    group.add(face);
                });
                [-3.9, 3.9].forEach(function (x, n) {
                    const pole = cyl(.14, .14, 5.6, pick(n), 8);
                    pole.position.set(x, 2.8, 16);
                    group.add(pole);
                });

                const plazaRing = [];
                for (let i = 0; i < 8; i++) {
                    const a = i / 8 * Math.PI * 2 + Math.PI / 8;
                    plazaRing.push(new THREE.Vector3(Math.cos(a) * 13.2, 4.1, Math.sin(a) * 13.2));
                }

                if (decor.includes('bunting') || decor.includes('merahputih')) {
                    const cols = decor.includes('merahputih') ? MERAH_PUTIH : colors;
                    const lines = plazaRing.map(function (p, i) { return [p, plazaRing[(i + 1) % plazaRing.length]]; });
                    lines.forEach(function (seg) { addBunting(group, seg[0], seg[1], cols); });
                    // Untaian di sepanjang Jalan Waktu dekat gerbang
                    for (let x = -24; x < 24; x += 8) {
                        addBunting(group, new THREE.Vector3(x, 4.6, ROAD_Z - ROAD_W / 2 - .8), new THREE.Vector3(x + 8, 4.6, ROAD_Z - ROAD_W / 2 - .8), cols);
                        addBunting(group, new THREE.Vector3(x, 4.6, ROAD_Z + ROAD_W / 2 + .8), new THREE.Vector3(x + 8, 4.6, ROAD_Z + ROAD_W / 2 + .8), cols);
                    }
                    [-24, -16, -8, 0, 8, 16, 24].forEach(function (x) {
                        [-1, 1].forEach(function (s) {
                            if (x === 0) return;
                            const pole = cyl(.08, .08, 4.6, '#cfd6e0', 6);
                            pole.position.set(x, 2.3, ROAD_Z + s * (ROAD_W / 2 + .8));
                            group.add(pole);
                        });
                    });
                }
                if (decor.includes('merahputih')) {
                    const pole = cyl(.12, .14, 12, '#e9edf3', 10);
                    pole.position.set(-11, 6, -9);
                    group.add(pole);
                    const flagGeo = new THREE.PlaneGeometry(4, 2.6, 16, 2);
                    flagGeo.translate(2, 0, 0);
                    const flagTex = canvasTexture(64, 64, function (g) {
                        g.fillStyle = '#e5484d';
                        g.fillRect(0, 0, 64, 32);
                        g.fillStyle = '#ffffff';
                        g.fillRect(0, 32, 64, 32);
                    });
                    const flag = new THREE.Mesh(flagGeo, new THREE.MeshStandardMaterial({ map: flagTex, side: THREE.DoubleSide }));
                    flag.position.set(-11, 10.6, -9);
                    group.add(flag);
                    const fp = flagGeo.attributes.position;
                    const baseX = [];
                    for (let k = 0; k < fp.count; k++) baseX.push(fp.getX(k));
                    eventAnims.push(function (t) {
                        for (let k = 0; k < fp.count; k++) fp.setZ(k, Math.sin(baseX[k] * 1.8 - t * 5) * .25 * baseX[k] / 4);
                        fp.needsUpdate = true;
                    });
                    obstacles.push({ x: -11, z: -9, r: .5, event: true });
                }
                if (decor.includes('balloons')) {
                    const spots = [[-14, 2], [14, -2], [-6, 15.5], [6, 15.5], [-4.6, ROAD_Z - 5.2], [4.6, ROAD_Z - 5.2], [-12, -12], [12, -12]];
                    spots.forEach(function (s, n) { addBalloonBunch(group, s[0], s[1], colors, n); });
                }
                if (decor.includes('confetti')) addConfetti(group, colors, 0, 0, 18, 700);
                if (decor.includes('confetti')) addConfetti(group, colors, 0, ROAD_Z, 12, 300);
                if (decor.includes('kites')) {
                    [[-16, 10], [18, 6], [0, -26]].forEach(function (k, n) { addKite(group, k[0], k[1], pick(n), pick(n + 1), n); });
                }
                if (decor.includes('lanterns')) {
                    plazaRing.forEach(function (p, n) { addLantern(group, p.x * .82, p.z * .82, pick(n), n); });
                    for (let x = -20; x <= 20; x += 5) addLantern(group, x, ROAD_Z - ROAD_W / 2 - .9, pick(x / 5 + 4), x);
                }
                if (decor.includes('fireworks')) addFireworks(group, colors);
                if (decor.includes('coins')) addCoins(group);
                if (decor.includes('xmas')) addXmasTree(group);
                if (decor.includes('crescent')) addCrescent(group);
                if (decor.includes('plane')) addBannerPlane(group, main, colors);
                world.partyHats = decor.includes('hats');
            }

            function addBunting(group, a, b, colors) {
                if (a.distanceTo(b) < .5) return;
                const n = Math.max(4, Math.round(a.distanceTo(b) / .9));
                const triGeo = new THREE.BufferGeometry();
                triGeo.setAttribute('position', new THREE.Float32BufferAttribute([-.28, 0, 0, .28, 0, 0, 0, -.55, 0], 3));
                triGeo.computeVertexNormals();
                const pts = [];
                for (let i = 0; i <= n; i++) {
                    const t = i / n;
                    const p = a.clone().lerp(b, t);
                    p.y -= Math.sin(t * Math.PI) * .7;
                    pts.push(p);
                }
                group.add(new THREE.Line(new THREE.BufferGeometry().setFromPoints(pts), new THREE.LineBasicMaterial({ color: 0x41506a })));
                const flags = [];
                for (let i = 1; i < n; i++) {
                    const flag = new THREE.Mesh(triGeo, new THREE.MeshStandardMaterial({ color: colors[i % colors.length], side: THREE.DoubleSide }));
                    flag.position.copy(pts[i]);
                    flag.lookAt(flag.position.x + (b.z - a.z), flag.position.y, flag.position.z - (b.x - a.x));
                    group.add(flag);
                    flags.push(flag);
                }
                const seed = a.x + a.z;
                eventAnims.push(function (t) {
                    flags.forEach(function (f, i) { f.rotation.x = Math.sin(t * 3 + i + seed) * .25; });
                });
            }
            function addBalloonBunch(group, x, z, colors, seed) {
                const bunch = new THREE.Group();
                bunch.position.set(x, 0, z);
                const weight = box(.3, .2, .3, '#41506a');
                weight.position.y = .1;
                bunch.add(weight);
                const balloons = [];
                for (let i = 0; i < 3; i++) {
                    const ball = new THREE.Mesh(new THREE.SphereGeometry(.55, 16, 12), new THREE.MeshStandardMaterial({ color: colors[(i + seed) % colors.length], roughness: .35, metalness: .05 }));
                    ball.scale.y = 1.18;
                    ball.castShadow = true;
                    const home = new THREE.Vector3((i - 1) * .7, 4.2 + (i % 2) * .7, (i % 2 ? .3 : -.3));
                    ball.position.copy(home);
                    const string = new THREE.Line(new THREE.BufferGeometry().setFromPoints([new THREE.Vector3(0, .2, 0), home.clone().add(new THREE.Vector3(0, -.62, 0))]), new THREE.LineBasicMaterial({ color: 0xffffff }));
                    bunch.add(ball, string);
                    balloons.push({ ball: ball, home: home, string: string });
                }
                group.add(bunch);
                eventAnims.push(function (t) {
                    balloons.forEach(function (b, i) {
                        b.ball.position.set(b.home.x + Math.sin(t * .9 + i + seed) * .25, b.home.y + Math.sin(t * 1.3 + i * 2 + seed) * .18, b.home.z + Math.cos(t * .8 + i) * .2);
                        const p = b.string.geometry.attributes.position;
                        p.setXYZ(1, b.ball.position.x, b.ball.position.y - .62, b.ball.position.z);
                        p.needsUpdate = true;
                    });
                });
            }
            function addConfetti(group, colors, cx, cz, radius, count) {
                const geo = new THREE.BufferGeometry();
                const pos = new Float32Array(count * 3);
                const col = new Float32Array(count * 3);
                const seeds = [];
                const c = new THREE.Color();
                for (let i = 0; i < count; i++) {
                    seeds.push({ a: Math.random() * Math.PI * 2, d: Math.sqrt(Math.random()) * radius, s: .8 + Math.random() * 1.2, o: Math.random() * 14, w: Math.random() * 6 });
                    c.set(colors[i % colors.length]);
                    col.set([c.r, c.g, c.b], i * 3);
                }
                geo.setAttribute('position', new THREE.BufferAttribute(pos, 3));
                geo.setAttribute('color', new THREE.BufferAttribute(col, 3));
                const pts = new THREE.Points(geo, new THREE.PointsMaterial({ size: .22, vertexColors: true }));
                group.add(pts);
                eventAnims.push(function (t) {
                    for (let i = 0; i < count; i++) {
                        const s = seeds[i];
                        const y = 14 - ((t * s.s + s.o) % 14);
                        pos[i * 3] = cx + Math.cos(s.a) * s.d + Math.sin(t * 2 + s.w) * .4;
                        pos[i * 3 + 1] = y;
                        pos[i * 3 + 2] = cz + Math.sin(s.a) * s.d + Math.cos(t * 1.7 + s.w) * .4;
                    }
                    geo.attributes.position.needsUpdate = true;
                });
            }
            function addKite(group, ax, az, colorA, colorB, seed) {
                const shape = new THREE.Shape();
                shape.moveTo(0, 1.4);
                shape.lineTo(.9, 0);
                shape.lineTo(0, -1.8);
                shape.lineTo(-.9, 0);
                shape.closePath();
                const kiteTex = canvasTexture(64, 64, function (g) {
                    g.fillStyle = colorA;
                    g.fillRect(0, 0, 64, 64);
                    g.fillStyle = colorB;
                    g.fillRect(0, 0, 32, 32);
                    g.fillRect(32, 32, 32, 32);
                });
                const geo = new THREE.ShapeGeometry(shape);
                geo.computeBoundingBox();
                const uv = geo.attributes.uv;
                for (let i = 0; i < uv.count; i++) uv.setXY(i, (uv.getX(i) + .9) / 1.8, (uv.getY(i) + 1.8) / 3.2);
                const kite = new THREE.Mesh(geo, new THREE.MeshStandardMaterial({ map: kiteTex, side: THREE.DoubleSide }));
                const tail = new THREE.Line(new THREE.BufferGeometry().setFromPoints([new THREE.Vector3(0, -1.8, 0), new THREE.Vector3(.3, -3, 0), new THREE.Vector3(-.2, -4.2, 0)]), new THREE.LineBasicMaterial({ color: new THREE.Color(colorB) }));
                kite.add(tail);
                group.add(kite);
                const anchor = new THREE.Vector3(ax, .5, az);
                const lineGeo = new THREE.BufferGeometry().setFromPoints([anchor, anchor]);
                group.add(new THREE.Line(lineGeo, new THREE.LineBasicMaterial({ color: 0xffffff, transparent: true, opacity: .7 })));
                eventAnims.push(function (t) {
                    kite.position.set(ax + Math.sin(t * .4 + seed) * 4, 20 + Math.sin(t * .7 + seed) * 2, az - 8 + Math.cos(t * .3 + seed) * 2);
                    kite.rotation.set(-.3, Math.sin(t * .5 + seed) * .4, Math.sin(t * 1.1 + seed) * .25);
                    const p = lineGeo.attributes.position;
                    p.setXYZ(1, kite.position.x, kite.position.y - 1.2, kite.position.z);
                    p.needsUpdate = true;
                });
            }
            function addLantern(group, x, z, color, seed) {
                const lantern = new THREE.Group();
                const body = new THREE.Mesh(new THREE.SphereGeometry(.42, 14, 10), new THREE.MeshStandardMaterial({ color: color, emissive: color, emissiveIntensity: .6, roughness: .5 }));
                body.scale.y = 1.25;
                const capT = cyl(.22, .22, .12, '#f0b429', 10);
                capT.position.y = .55;
                const capB = capT.clone();
                capB.position.y = -.55;
                const tassel = cyl(.03, .08, .5, '#f0b429', 6);
                tassel.position.y = -.85;
                lantern.add(body, capT, capB, tassel);
                lantern.position.set(x, 3.5, z);
                group.add(lantern);
                const pole = cyl(.06, .06, 3, '#6b4a2f', 6);
                pole.position.set(x, 1.5 + .45, z);
                group.add(pole);
                eventAnims.push(function (t) {
                    lantern.rotation.z = Math.sin(t * 1.4 + seed) * .12;
                    body.material.emissiveIntensity = .5 + Math.sin(t * 2 + seed) * .15;
                });
            }
            function addFireworks(group, colors) {
                const bursts = [];
                for (let b = 0; b < 4; b++) {
                    const count = 140;
                    const geo = new THREE.BufferGeometry();
                    const pos = new Float32Array(count * 3);
                    geo.setAttribute('position', new THREE.BufferAttribute(pos, 3));
                    const matF = new THREE.PointsMaterial({ color: new THREE.Color(colors[b % colors.length]), size: .7, transparent: true, blending: THREE.AdditiveBlending, depthWrite: false, fog: false });
                    const pts = new THREE.Points(geo, matF);
                    group.add(pts);
                    const dirs = [];
                    for (let i = 0; i < count; i++) dirs.push(new THREE.Vector3(Math.random() - .5, Math.random() - .5, Math.random() - .5).normalize().multiplyScalar(.6 + Math.random() * .4));
                    bursts.push({ geo: geo, pos: pos, mat: matF, dirs: dirs, center: new THREE.Vector3(), start: -10, next: b * 1.1, period: 2.6 + b * .7 });
                }
                eventAnims.push(function (t) {
                    bursts.forEach(function (b, n) {
                        if (t >= b.next) {
                            b.next = t + b.period;
                            b.start = t;
                            b.center.set((Math.random() - .5) * 80, 32 + Math.random() * 16, -20 + (Math.random() - .5) * 60);
                            b.mat.color.set(colors[(n + Math.floor(t)) % colors.length]);
                        }
                        const age = t - b.start;
                        const r = Math.min(1, age / 1.2) * 9;
                        for (let i = 0; i < b.dirs.length; i++) {
                            b.pos[i * 3] = b.center.x + b.dirs[i].x * r;
                            b.pos[i * 3 + 1] = b.center.y + b.dirs[i].y * r - age * age * .8;
                            b.pos[i * 3 + 2] = b.center.z + b.dirs[i].z * r;
                        }
                        b.geo.attributes.position.needsUpdate = true;
                        b.mat.opacity = Math.max(0, 1 - age / 1.8);
                    });
                });
            }
            function addCoins(group) {
                const coins = [];
                for (let i = 0; i < 14; i++) {
                    const coin = new THREE.Mesh(new THREE.CylinderGeometry(.45, .45, .1, 20), new THREE.MeshStandardMaterial({ color: 0xf0b429, metalness: .7, roughness: .25, emissive: 0x7a5200, emissiveIntensity: .3 }));
                    coin.rotation.x = Math.PI / 2;
                    group.add(coin);
                    coins.push(coin);
                }
                eventAnims.push(function (t) {
                    coins.forEach(function (c, i) {
                        const a = t * .6 + i / coins.length * Math.PI * 2;
                        c.position.set(Math.cos(a) * 7, 6 + Math.sin(t * 2 + i) * .6, Math.sin(a) * 7);
                        c.rotation.z = t * 3 + i;
                    });
                });
            }
            function addXmasTree(group) {
                const tree = new THREE.Group();
                const trunk = cyl(.35, .45, 1.2, '#7a5233', 8);
                trunk.position.y = .6;
                tree.add(trunk);
                [[2.6, 2.6, 1.9], [2.1, 2.2, 3.4], [1.5, 1.9, 4.7]].forEach(function (l) {
                    const cone = new THREE.Mesh(new THREE.ConeGeometry(l[0], l[1], 10), mat('#1f7a45', { flatShading: true }));
                    cone.position.y = l[2];
                    cone.castShadow = true;
                    tree.add(cone);
                });
                const bulbs = [];
                for (let i = 0; i < 26; i++) {
                    const h = 1.4 + Math.random() * 4.2;
                    const r = (5.9 - h) * .5 + .2;
                    const a = Math.random() * Math.PI * 2;
                    const bulb = new THREE.Mesh(new THREE.SphereGeometry(.14, 8, 6), new THREE.MeshStandardMaterial({ color: ['#e5484d', '#f0b429', '#3b82f6', '#ffffff'][i % 4], emissive: ['#e5484d', '#f0b429', '#3b82f6', '#ffffff'][i % 4], emissiveIntensity: .6 }));
                    bulb.position.set(Math.cos(a) * r, h, Math.sin(a) * r);
                    tree.add(bulb);
                    bulbs.push(bulb);
                }
                const star = new THREE.Mesh(new THREE.OctahedronGeometry(.45, 0), new THREE.MeshStandardMaterial({ color: 0xffd36b, emissive: 0xffc94d, emissiveIntensity: 1 }));
                star.position.y = 6.4;
                tree.add(star);
                tree.position.set(10, 0, -7);
                group.add(tree);
                obstacles.push({ x: 10, z: -7, r: 2.4, event: true });
                eventAnims.push(function (t) {
                    star.rotation.y = t;
                    bulbs.forEach(function (b, i) { b.material.emissiveIntensity = (Math.sin(t * 3 + i) + 1) * .5; });
                });
            }
            function addCrescent(group) {
                const tex = canvasTexture(256, 256, function (g) {
                    g.fillStyle = '#ffe9a8';
                    g.beginPath();
                    g.arc(128, 128, 100, 0, Math.PI * 2);
                    g.fill();
                    g.globalCompositeOperation = 'destination-out';
                    g.beginPath();
                    g.arc(170, 104, 88, 0, Math.PI * 2);
                    g.fill();
                });
                const moon = new THREE.Sprite(new THREE.SpriteMaterial({ map: tex, transparent: true, fog: false, depthWrite: false }));
                moon.scale.set(40, 40, 1);
                moon.position.set(140, 170, -380);
                group.add(moon);
                const starGeo = new THREE.BufferGeometry();
                const sp = [];
                for (let i = 0; i < 60; i++) sp.push(80 + Math.random() * 160, 130 + Math.random() * 90, -400 + Math.random() * 40);
                starGeo.setAttribute('position', new THREE.Float32BufferAttribute(sp, 3));
                const stars = new THREE.Points(starGeo, new THREE.PointsMaterial({ color: 0xfff6d6, size: 2.2, fog: false, transparent: true }));
                group.add(stars);
                eventAnims.push(function (t) { stars.material.opacity = .6 + Math.sin(t * 2) * .3; });
            }
            function addBannerPlane(group, ev, colors) {
                const plane = new THREE.Group();
                const body = box(3.2, .7, .8, '#ffffff');
                const wing = box(.9, .12, 4.4, colors[0]);
                wing.position.x = .3;
                const tailFin = box(.6, .8, .12, colors[0]);
                tailFin.position.set(-1.4, .5, 0);
                const prop = box(.08, 1.1, .16, '#2d3442');
                prop.position.x = 1.65;
                plane.add(body, wing, tailFin, prop);
                const tex = canvasTexture(1024, 160, function (g) {
                    g.fillStyle = '#ffffff';
                    g.fillRect(0, 0, 1024, 160);
                    g.fillStyle = colors[0];
                    g.fillRect(0, 0, 1024, 14);
                    g.fillRect(0, 146, 1024, 14);
                    g.fillStyle = '#082149';
                    g.font = '900 78px "Segoe UI", Arial';
                    g.textAlign = 'center';
                    g.textBaseline = 'middle';
                    g.fillText(ev.emoji + ' ' + trunc(ev.title, 28), 512, 84);
                });
                const banner = new THREE.Mesh(new THREE.PlaneGeometry(14, 2.2, 20, 1), new THREE.MeshStandardMaterial({ map: tex, side: THREE.DoubleSide }));
                banner.position.x = -10;
                plane.add(banner);
                const bp = banner.geometry.attributes.position;
                const bx = [];
                for (let k = 0; k < bp.count; k++) bx.push(bp.getX(k));
                group.add(plane);
                eventAnims.push(function (t) {
                    const a = t * .12;
                    plane.position.set(Math.cos(a) * 70, 34 + Math.sin(t * .5) * 2, Math.sin(a) * 70 - 10);
                    // Hidung pesawat (sumbu +x lokal) searah lintasan melingkar
                    plane.rotation.set(0, -a - Math.PI / 2, Math.sin(t) * .05);
                    prop.rotation.x = t * 40;
                    for (let k = 0; k < bp.count; k++) bp.setZ(k, Math.sin(bx[k] * .6 + t * 4) * .35);
                    bp.needsUpdate = true;
                });
            }

            // Papan agenda: event hari ini dan 7 hari ke depan, serta kata hari ini
            function agendaTexture() {
                return canvasTexture(1024, 768, function (g) {
                    const grad = g.createLinearGradient(0, 0, 0, 768);
                    grad.addColorStop(0, '#fff8e6');
                    grad.addColorStop(1, '#ffeec2');
                    g.fillStyle = grad;
                    roundRect(g, 0, 0, 1024, 768, 36);
                    g.fill();
                    g.fillStyle = '#082149';
                    g.font = '900 56px "Segoe UI", Arial';
                    g.fillText('📅 Agenda Kampus', 44, 82);
                    g.fillStyle = '#7a5300';
                    g.font = 'italic 600 30px "Segoe UI", Arial';
                    wrapText(g, '“' + quoteOf(world.today) + '”', 44, 136, 940, 38, 2);
                    for (let d = 0; d < 8; d++) {
                        const iso = addDays(world.today, d);
                        const ev = d === 0 ? world.events[0] : eventsFor(iso, false)[0];
                        const y = 240 + d * 64;
                        g.fillStyle = d === 0 ? 'rgba(240,180,41,.35)' : (d % 2 ? 'rgba(255,255,255,.55)' : 'rgba(255,255,255,.25)');
                        roundRect(g, 32, y - 40, 960, 58, 14);
                        g.fill();
                        g.fillStyle = '#41506a';
                        g.font = '700 26px "Segoe UI", Arial';
                        g.fillText(d === 0 ? 'Hari ini' : (d === 1 ? 'Besok' : parseDate(iso).toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short' })), 52, y);
                        g.fillStyle = '#082149';
                        g.font = '800 30px "Segoe UI", Arial';
                        g.fillText(ev.emoji + ' ' + trunc(ev.title, 38), 290, y);
                    }
                });
            }
            function wrapText(g, text, x, y, maxW, lineH, maxLines) {
                const words = text.split(' ');
                let line = '', lines = 0;
                for (let i = 0; i < words.length; i++) {
                    const test = line ? line + ' ' + words[i] : words[i];
                    if (g.measureText(test).width > maxW && line) {
                        g.fillText(lines === maxLines - 1 ? line + '…' : line, x, y + lines * lineH);
                        lines++;
                        if (lines >= maxLines) return;
                        line = words[i];
                    } else {
                        line = test;
                    }
                }
                g.fillText(line, x, y + lines * lineH);
            }
            function buildAgenda() {
                const g = new THREE.Group();
                const l = box(.25, 5.6, .25, '#6b4a2f');
                l.position.set(-3.6, 2.8, 0);
                const r = l.clone();
                r.position.x = 3.6;
                const back = box(7.5, 5.8, .2, '#9a6b43');
                back.position.y = 3.6;
                const face = new THREE.Mesh(new THREE.PlaneGeometry(7.2, 5.4), new THREE.MeshStandardMaterial({ map: agendaTexture(), emissive: '#ffffff', emissiveIntensity: .2 }));
                face.material.emissiveMap = face.material.map;
                face.position.set(0, 3.6, .12);
                const roof = box(8.2, .3, 1.1, '#c0392b');
                roof.position.y = 6.6;
                g.add(l, r, back, face, roof);
                g.position.set(-19, 0, 2);
                g.rotation.y = Math.PI / 2;
                scene.add(g);
                world.agenda = face;
                face.userData.interact = { type: 'agenda' };
                clickables.push(face);
                interactables.push({ type: 'agenda', x: -17.5, z: 2, r: 4.5 });
                obstacles.push({ x: -19, z: 2, r: 2 });
            }
            function refreshAgenda() {
                if (!world.agenda) return;
                const old = world.agenda.material.map;
                world.agenda.material.map = agendaTexture();
                world.agenda.material.emissiveMap = world.agenda.material.map;
                world.agenda.material.needsUpdate = true;
                old.dispose();
            }
            function renderEventHud() {
                const main = world.events[0];
                const others = world.events.slice(1, 3).map(function (e) { return e.emoji + ' ' + e.title; });
                $('#ww-event').html(
                    '<div class="ww-event-title" style="background:linear-gradient(rgba(8,33,73,.4),rgba(8,33,73,.4)),linear-gradient(90deg,' + main.colors.map(esc).join(',') + (main.colors.length < 2 ? ',' + esc(main.colors[0]) : '') + ')">' + esc(main.emoji + ' ' + main.title) + '</div>' +
                    '<div class="ww-event-sub">' + esc(main.sub) + '</div>' +
                    (others.length ? '<div class="ww-event-also">Juga hari ini: ' + esc(others.join(' · ')) + '</div>' : '') +
                    '<div class="ww-quote">“' + esc(quoteOf(world.today)) + '”</div>'
                );
            }

            // ---------- Pendamping (misalnya ADO yang menempel pada Dede) ----------
            const COMPANION_LINES = {
                online: ['Semangat, {o}! ✨', 'Aku ikut ke mana pun {o} pergi~', 'Jangan lupa minum air putih ya!', 'Kerjaan hari ini pasti beres!', 'Ehehe, {o} keren hari ini', 'Istirahat sebentar juga boleh kok', 'Ayo cek Jalan Waktu!', 'Fokus, fokus~ aku jagain', 'Satu tiket lagi, {o}!', 'Hari ini: {e}!', '“{q}”'],
                desk: ['Menunggu {o} datang…', 'Aku jagain meja {o} dulu ya', 'Psst, {o} belum masuk kampus', 'Meja {o} aman terkendali!', 'Hari ini: {e}~'],
            };
            const companions = [];
            function buildCompanions() {
                Object.keys(COMPANIONS || {}).forEach(function (uid) {
                    const conf = COMPANIONS[uid];
                    const userId = Number(uid);
                    const group = new THREE.Group();
                    group.visible = false;

                    // Kartu bergambar: bingkai putih, pinggiran gradasi, sudut membulat
                    const cv = document.createElement('canvas');
                    cv.width = 320;
                    cv.height = 400;
                    const tex = new THREE.CanvasTexture(cv);
                    tex.encoding = THREE.sRGBEncoding;
                    const drawCard = function (img) {
                        const g = cv.getContext('2d');
                        g.clearRect(0, 0, 320, 400);
                        const rim = g.createLinearGradient(0, 0, 320, 400);
                        rim.addColorStop(0, '#22c1c3');
                        rim.addColorStop(.5, '#3b82f6');
                        rim.addColorStop(1, '#8b5cf6');
                        g.fillStyle = rim;
                        roundRect(g, 4, 4, 312, 392, 42);
                        g.fill();
                        g.fillStyle = '#ffffff';
                        roundRect(g, 14, 14, 292, 372, 34);
                        g.fill();
                        g.save();
                        roundRect(g, 22, 22, 276, 356, 28);
                        g.clip();
                        if (img) {
                            const s = Math.max(276 / img.width, 356 / img.height);
                            g.drawImage(img, 22 + (276 - img.width * s) / 2, 22 + (356 - img.height * s) / 2, img.width * s, img.height * s);
                        } else {
                            g.fillStyle = '#dbe7fb';
                            g.fillRect(22, 22, 276, 356);
                        }
                        g.restore();
                        tex.needsUpdate = true;
                    };
                    drawCard(null);
                    const img = new Image();
                    img.onload = function () { drawCard(img); };
                    img.src = conf.image;

                    const card = new THREE.Sprite(new THREE.SpriteMaterial({ map: tex, transparent: true, depthWrite: false }));
                    card.scale.set(1.2, 1.5, 1);
                    card.renderOrder = 15;
                    const label = labelSprite([conf.name + ' ✨'], { size: 30, height: .26, bg: 'rgba(59,130,246,.95)' });
                    label.position.y = -.95;
                    group.add(card, label);

                    // Kilau kecil yang mengitari kartu
                    const sparkCount = 18;
                    const sparkGeo = new THREE.BufferGeometry();
                    const sparkPos = new Float32Array(sparkCount * 3);
                    sparkGeo.setAttribute('position', new THREE.BufferAttribute(sparkPos, 3));
                    const sparks = new THREE.Points(sparkGeo, new THREE.PointsMaterial({ color: 0xbfe3ff, size: .09, transparent: true, opacity: .9, blending: THREE.AdditiveBlending, depthWrite: false }));
                    group.add(sparks);
                    scene.add(group);

                    card.userData.interact = { type: 'companion', id: userId };
                    clickables.push(card);
                    const c = { userId: userId, conf: conf, group: group, card: card, sparkGeo: sparkGeo, sparkPos: sparkPos, bubble: null, bubbleUntil: 0, nextLine: performance.now() + 3500, mode: null, placed: false };
                    companions.push(c);
                    // Pendamping milik sendiri selalu dekat, jadi tidak memunculkan kartu otomatis (tetap bisa diklik)
                    interactables.push({ type: 'companion', id: userId, get x() { return c.group.visible && !(c.mode === 'online' && userId === Number(ME.id)) ? c.group.position.x : 9999; }, get z() { return c.group.position.z; }, r: 1.8 });
                });
            }
            // Siapa yang diikuti: pemiliknya kalau online, kalau tidak menunggu di meja pemiliknya
            function companionAnchor(c) {
                if (c.userId === Number(ME.id) && me.mesh) return { mesh: me.mesh, mode: 'online' };
                const pl = net.players.get(c.userId);
                if (pl) return { mesh: pl.mesh, mode: 'online' };
                const desk = Object.keys(world.desks).map(function (k) { return world.desks[k]; }).find(function (d) { return Number(d.member.user_id) === c.userId; });
                if (desk) return { mesh: desk.npc, mode: 'desk' };
                return null;
            }
            function companionLine(c, mode) {
                const pool = COMPANION_LINES[mode] || COMPANION_LINES.online;
                const ev = world.events && world.events[0];
                return pool[Math.floor(Math.random() * pool.length)]
                    .replace(/\{o\}/g, c.conf.owner || 'kamu')
                    .replace(/\{e\}/g, ev ? ev.emoji + ' ' + ev.title : 'hari yang seru')
                    .replace(/\{q\}/g, trunc(quoteOf(world.today), 40));
            }
            function companionSay(c, text) {
                if (c.bubble) {
                    c.group.remove(c.bubble);
                    disposeObject(c.bubble);
                }
                c.bubble = labelSprite([trunc(text, 44)], { size: 30, height: .3, bg: 'rgba(255,255,255,.97)', color: '#1b2536', border: '#8b5cf6' });
                c.bubble.position.y = 1.15;
                c.group.add(c.bubble);
                c.bubbleUntil = performance.now() + 5500;
            }
            const _anchor = new THREE.Vector3();
            function updateCompanions(dt, t) {
                const now = performance.now();
                companions.forEach(function (c, n) {
                    const a = companionAnchor(c);
                    if (!a) {
                        c.group.visible = false;
                        return;
                    }
                    // Melayang di belakang bahu kanan pemiliknya, ikut arah hadapnya
                    a.mesh.updateMatrixWorld();
                    // Di meja: di sebelah monitor supaya tidak tertutup layar
                    if (a.mode === 'desk') _anchor.set(1, 2.55 + Math.sin(t * 2 + n) * .12, .4);
                    else _anchor.set(-.85, 2.2 + Math.sin(t * 2 + n) * .12, -.55);
                    a.mesh.localToWorld(_anchor);
                    if (!c.placed || c.mode !== a.mode || c.group.position.distanceTo(_anchor) > 15) {
                        c.group.position.copy(_anchor);
                        c.placed = true;
                    } else {
                        c.group.position.lerp(_anchor, Math.min(1, dt * 4.5));
                    }
                    if (c.mode !== a.mode) {
                        c.mode = a.mode;
                        c.nextLine = now + 1200;
                    }
                    c.group.visible = true;
                    c.card.material.rotation = Math.sin(t * 1.6 + n) * .07;
                    for (let i = 0; i < c.sparkPos.length / 3; i++) {
                        const ang = t * 1.4 + i / (c.sparkPos.length / 3) * Math.PI * 2;
                        c.sparkPos[i * 3] = Math.cos(ang) * .85;
                        c.sparkPos[i * 3 + 1] = Math.sin(ang * 2 + t) * .7;
                        c.sparkPos[i * 3 + 2] = Math.sin(ang) * .25;
                    }
                    c.sparkGeo.attributes.position.needsUpdate = true;
                    if (now > c.nextLine) {
                        companionSay(c, companionLine(c, c.mode));
                        c.nextLine = now + 14000 + Math.random() * 12000;
                    }
                    if (c.bubble && now > c.bubbleUntil) {
                        c.group.remove(c.bubble);
                        disposeObject(c.bubble);
                        c.bubble = null;
                    }
                });
            }

            // ---------- Pemain ----------
            const me = {
                pos: new THREE.Vector3(0, 0, 8), ry: Math.PI, vy: 0, moving: false, speed: 0,
                yaw: 0, pitch: .42, dist: 11, keys: {}, mesh: null,
            };
            const myColor = ME.color || hashColor(ME.id);
            function buildMe() {
                me.mesh = makeCharacter(myColor, ME.name, 'kamu');
                me.mesh.position.copy(me.pos);
                scene.add(me.mesh);
                if (ME.member_id) $('#ww-go-desk').removeClass('d-none');
            }

            function walkable(x, z) {
                const onIsland = Math.hypot(x, z) < ISLAND_R - 1.5;
                const onRoad = Math.abs(z - ROAD_Z) < ROAD_W / 2 + .2 && x > world.roadX0 + .5 && x < world.roadX1 - .5;
                return onIsland || onRoad;
            }
            function resolveObstacles(p) {
                obstacles.forEach(function (o) {
                    const dx = p.x - o.x, dz = p.z - o.z;
                    const d = Math.hypot(dx, dz);
                    const min = o.r + .45;
                    if (d < min && d > 0.0001) {
                        p.x = o.x + dx / d * min;
                        p.z = o.z + dz / d * min;
                    }
                });
            }
            function updateMe(dt, t) {
                const k = me.keys;
                let fx = 0, fz = 0;
                if (k.KeyW || k.ArrowUp) fz += 1;
                if (k.KeyS || k.ArrowDown) fz -= 1;
                if (k.KeyA || k.ArrowLeft) fx -= 1;
                if (k.KeyD || k.ArrowRight) fx += 1;
                const run = k.ShiftLeft || k.ShiftRight;
                const fwd = new THREE.Vector3(-Math.sin(me.yaw), 0, -Math.cos(me.yaw));
                const right = new THREE.Vector3(-fwd.z, 0, fwd.x);
                const dir = fwd.multiplyScalar(fz).add(right.multiplyScalar(fx));
                me.moving = dir.lengthSq() > 0;
                me.speed = me.moving ? (run ? 9 : 5) : 0;
                if (me.moving) {
                    dir.normalize();
                    const next = me.pos.clone().addScaledVector(dir, me.speed * dt);
                    resolveObstacles(next);
                    if (walkable(next.x, next.z)) {
                        me.pos.x = next.x;
                        me.pos.z = next.z;
                    } else if (walkable(next.x, me.pos.z)) {
                        me.pos.x = next.x;
                    } else if (walkable(me.pos.x, next.z)) {
                        me.pos.z = next.z;
                    }
                    me.ry = angleLerp(me.ry, Math.atan2(dir.x, dir.z), Math.min(1, dt * 12));
                }
                if (k.Space && me.pos.y <= 0.001) me.vy = 6.5;
                me.vy -= 18 * dt;
                me.pos.y = Math.max(0, me.pos.y + me.vy * dt);
                if (me.pos.y === 0) me.vy = Math.max(0, me.vy);

                me.mesh.position.copy(me.pos);
                me.mesh.rotation.y = me.ry;
                animateCharacter(me.mesh, t, me.moving && me.pos.y < .05, me.speed);

                // Kamera orang ketiga
                const target = new THREE.Vector3(me.pos.x, me.pos.y + 1.6, me.pos.z);
                const cp = new THREE.Vector3(
                    target.x + Math.sin(me.yaw) * Math.cos(me.pitch) * me.dist,
                    target.y + Math.sin(me.pitch) * me.dist,
                    target.z + Math.cos(me.yaw) * Math.cos(me.pitch) * me.dist
                );
                cp.y = Math.max(cp.y, .8);
                camera.position.lerp(cp, Math.min(1, dt * 10));
                camera.lookAt(target);

                // Bayangan mengikuti pemain supaya tetap tajam
                sun.position.set(me.pos.x - 40, 70, me.pos.z - 30);
                sun.target.position.set(me.pos.x, 0, me.pos.z);
            }
            function teleport(x, z, yaw) {
                me.pos.set(x, 0, z);
                if (yaw !== undefined) me.yaw = yaw;
                camera.position.set(x + Math.sin(me.yaw) * me.dist, 6, z + Math.cos(me.yaw) * me.dist);
            }

            // ---------- Jaringan: Echo presence + cadangan polling ----------
            const net = { players: new Map(), echoLive: false, channel: null, lastChatN: 0, seenChat: new Set(), lastWhisper: 0, firstPoll: true, pollOk: false };
            function playerFor(p) {
                let pl = net.players.get(p.id);
                if (!pl) {
                    const color = p.color || hashColor(p.id);
                    const mesh = makeCharacter(color, p.name, p.member_id ? (p.division || '') : 'tamu');
                    mesh.position.set(p.x || 0, 0, p.z || 0);
                    mesh.rotation.y = p.ry || 0;
                    scene.add(mesh);
                    pl = { id: p.id, name: p.name, color: color, member_id: p.member_id || null, division: p.division || null, mesh: mesh, target: new THREE.Vector3(p.x || 0, 0, p.z || 0), ry: p.ry || 0, moving: false, lastWhisper: 0, lastSeen: performance.now() };
                    net.players.set(p.id, pl);
                    mesh.userData.body.forEach(function (m) {
                        m.userData.interact = { type: 'player', id: p.id };
                        clickables.push(m);
                    });
                    if (!net.firstPoll) addSystemChat(p.name + ' masuk ke kampus 👋');
                    refreshOnline();
                }
                return pl;
            }
            function applyState(p, source) {
                if (!p || Number(p.id) === Number(ME.id)) return;
                const pl = playerFor(p);
                const now = performance.now();
                pl.lastSeen = now;
                if (source === 'whisper') pl.lastWhisper = now;
                if (source === 'poll' && now - pl.lastWhisper < 1500) return;
                pl.target.set(Number(p.x), 0, Number(p.z));
                pl.ry = Number(p.ry);
                pl.moving = !!p.moving;
            }
            function removePlayer(id) {
                const pl = net.players.get(id);
                if (!pl) return;
                scene.remove(pl.mesh);
                for (let i = clickables.length - 1; i >= 0; i--) {
                    const it = clickables[i].userData.interact;
                    if (it && it.type === 'player' && it.id === id) clickables.splice(i, 1);
                }
                disposeObject(pl.mesh);
                net.players.delete(id);
                addSystemChat(pl.name + ' keluar dari kampus');
                refreshOnline();
            }
            function updatePlayers(dt, t) {
                net.players.forEach(function (pl) {
                    const m = pl.mesh;
                    const far = m.position.distanceTo(pl.target);
                    if (far > 25) m.position.copy(pl.target);
                    else m.position.lerp(pl.target, Math.min(1, dt * 8));
                    m.rotation.y = angleLerp(m.rotation.y, pl.ry, Math.min(1, dt * 10));
                    animateCharacter(m, t, pl.moving || far > .15, far > 1.5 ? 9 : 5);
                    if (m.userData.bubble && performance.now() > m.userData.bubbleUntil) {
                        m.remove(m.userData.bubble);
                        disposeObject(m.userData.bubble);
                        m.userData.bubble = null;
                    }
                });
                if (me.mesh.userData.bubble && performance.now() > me.mesh.userData.bubbleUntil) {
                    me.mesh.remove(me.mesh.userData.bubble);
                    disposeObject(me.mesh.userData.bubble);
                    me.mesh.userData.bubble = null;
                }
            }
            function myState() {
                return { id: ME.id, name: ME.name, color: myColor, member_id: ME.member_id, division: ME.division, x: +me.pos.x.toFixed(2), z: +me.pos.z.toFixed(2), ry: +me.ry.toFixed(3), moving: me.moving };
            }

            function heartbeat() {
                const s = myState();
                $.ajax({ url: ROUTES.presence, method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF }, data: { x: s.x, z: s.z, ry: s.ry, moving: s.moving ? 1 : 0, after: net.lastChatN } })
                    .done(function (res) {
                        if (!res || !res.players) {
                            net.pollOk = false;
                            setConn();
                            return;
                        }
                        net.pollOk = true;
                        const seen = {};
                        res.players.forEach(function (p) {
                            seen[p.id] = true;
                            applyState(p, 'poll');
                        });
                        net.players.forEach(function (pl, id) {
                            if (!seen[id] && performance.now() - pl.lastWhisper > 6000) removePlayer(id);
                        });
                        (res.chat || []).forEach(function (m) { receiveChat(m, net.firstPoll); });
                        net.firstPoll = false;
                        setConn();
                    })
                    .fail(function () {
                        net.pollOk = false;
                        setConn();
                    })
                    .always(function () {
                        setTimeout(heartbeat, net.echoLive ? 3000 : (me.moving ? 700 : 1500));
                    });
            }

            function loadScript(src) {
                return new Promise(function (resolve, reject) {
                    const s = document.createElement('script');
                    s.src = src;
                    s.onload = resolve;
                    s.onerror = reject;
                    document.head.appendChild(s);
                });
            }
            function connectEcho() {
                const start = window.Echo ? Promise.resolve() : loadScript(ROUTES.socket).then(function () { return loadScript(ROUTES.echo); });
                start.then(function () {
                    if (!window.Echo || !window.Echo.join) throw new Error('Echo tidak tersedia');
                    const channel = window.Echo.join('work-tracker-world');
                    net.channel = channel;
                    channel.here(function () {
                        net.echoLive = true;
                        setConn();
                    }).joining(function () {
                        whisperMove(true);
                    }).leaving(function (u) {
                        if (u && net.players.has(u.id)) removePlayer(u.id);
                    }).listenForWhisper('move', function (p) {
                        applyState(p, 'whisper');
                    }).listenForWhisper('chat', function (m) {
                        receiveChat(m, false);
                    });
                    const socket = window.Echo.connector && window.Echo.connector.socket;
                    if (socket && socket.on) {
                        socket.on('disconnect', function () { net.echoLive = false; setConn(); });
                    }
                }).catch(function () {
                    net.echoLive = false;
                    setConn();
                });
            }
            function whisperMove(force) {
                if (!net.echoLive || !net.channel) return;
                const now = performance.now();
                const gap = now - net.lastWhisper;
                if (force || (me.moving && gap > 90) || gap > 2000) {
                    net.lastWhisper = now;
                    try { net.channel.whisper('move', myState()); } catch (e) { net.echoLive = false; setConn(); }
                }
            }
            function setConn() {
                const $c = $('#ww-conn').removeClass('live off');
                if (net.echoLive) {
                    $c.addClass('live');
                    $('#ww-conn-text').text('Realtime aktif');
                } else if (net.pollOk) {
                    $('#ww-conn-text').text('Mode cadangan (sinkron tiap ±1 detik)');
                } else {
                    $c.addClass('off');
                    $('#ww-conn-text').text('Terputus, mencoba lagi...');
                }
            }

            // ---------- Chat ----------
            function addChatLine(html) {
                const $log = $('#ww-chat-log');
                $log.append('<div>' + html + '</div>');
                while ($log.children().length > 60) $log.children().first().remove();
                $log.scrollTop($log[0].scrollHeight);
            }
            function addSystemChat(text) {
                addChatLine('<span class="sys">' + esc(text) + '</span>');
            }
            function receiveChat(m, history) {
                if (!m || !m.cid || net.seenChat.has(m.cid)) {
                    if (m && m.n) net.lastChatN = Math.max(net.lastChatN, m.n);
                    return;
                }
                net.seenChat.add(m.cid);
                if (m.n) net.lastChatN = Math.max(net.lastChatN, m.n);
                const color = Number(m.user_id) === Number(ME.id) ? myColor : ((net.players.get(m.user_id) || {}).color || hashColor(m.user_id));
                addChatLine('<time>' + esc(m.at || '') + '</time><b style="color:' + esc(color) + '">' + esc(m.name) + ':</b> ' + esc(m.text));
                if (history) return;
                if (Number(m.user_id) === Number(ME.id)) setBubble(me.mesh, m.text);
                else if (net.players.has(m.user_id)) setBubble(net.players.get(m.user_id).mesh, m.text);
            }
            $('#ww-chat-form').on('submit', function (e) {
                e.preventDefault();
                const text = $('#ww-chat-input').val().trim();
                if (!text) {
                    $('#ww-chat-input').blur();
                    canvas.focus();
                    return;
                }
                $('#ww-chat-input').val('');
                const cid = ME.id + '-' + Date.now() + '-' + Math.random().toString(36).slice(2, 7);
                const local = { cid: cid, user_id: ME.id, name: ME.name, text: text, at: new Date().toTimeString().slice(0, 5) };
                receiveChat(local, false);
                $.ajax({ url: ROUTES.chat, method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF }, data: { text: text, cid: cid } })
                    .fail(function () { addSystemChat('Pesan gagal terkirim'); });
                if (net.echoLive && net.channel) {
                    try { net.channel.whisper('chat', local); } catch (err) { /* polling tetap mengantar pesan */ }
                }
            });
            $('#ww-chat-input').on('keydown', function (e) {
                e.stopPropagation();
                if (e.key === 'Escape') {
                    this.blur();
                    canvas.focus();
                }
            });

            // ---------- Online list ----------
            function refreshOnline() {
                const rows = [{ id: ME.id, name: ME.name, color: myColor, you: true, member: !!ME.member_id }];
                net.players.forEach(function (pl) { rows.push({ id: pl.id, name: pl.name, color: pl.color, member: !!pl.member_id }); });
                $('#ww-online-count').text(rows.length);
                $('#ww-online-list').html(rows.map(function (r) {
                    const buddy = COMPANIONS && COMPANIONS[r.id] ? ' <span title="Pendamping" style="color:#c4b5fd">+ ' + esc(COMPANIONS[r.id].name) + ' ✨</span>' : '';
                    return '<li><span class="av" style="background:' + esc(r.color) + '">' + esc(initials(r.name)) + '</span>' + esc(r.name) + buddy + '<small>' + (r.you ? 'kamu' : (r.member ? '' : 'tamu')) + '</small></li>';
                }).join(''));
                // Anggota yang online tidak lagi "berjaga" di meja; lampu meja menyala hijau
                const onlineUsers = {};
                rows.forEach(function (r) { onlineUsers[r.id] = true; });
                Object.keys(world.desks).forEach(function (id) {
                    const desk = world.desks[id];
                    const on = !!onlineUsers[desk.member.user_id];
                    desk.npc.visible = !on;
                    desk.lamp.material.color.set(on ? 0x2fd27a : 0x8a97ad);
                    desk.lamp.material.emissive.set(on ? 0x2fd27a : 0x000000);
                });
                clearTimeout(refreshOnline.t);
                refreshOnline.t = setTimeout(refreshNotice, 800);
            }

            // ---------- Kartu info saat mendekat / klik ----------
            let cardKey = null;
            let cardPinned = false;
            function itemLink(id) {
                return ROUTES.tracker + '?item=' + id;
            }
            function itemCard(id) {
                const i = world.board.items.find(function (x) { return x.id === id; });
                if (!i) return '';
                const owner = world.board.programmers.find(function (p) { return p.id === Number(i.programmer_id); });
                const color = HEALTH_COLOR[i.health] || '#8a97ad';
                const done = i.status === 'selesai';
                let when = '';
                if (done) when = 'Selesai ' + fmt(String(i.finished_at || '').slice(0, 10), true);
                else if (i.target_date) {
                    const left = days(world.today, i.target_date);
                    when = left > 0 ? left + ' hari lagi menuju target' : (left === 0 ? 'Target hari ini' : 'Lewat target ' + Math.abs(left) + ' hari');
                }
                return '<button class="close" data-close>&times;</button>' +
                    '<div class="meta">' + esc(i.code || '') + '</div><h5>' + esc(i.title) + '</h5>' +
                    '<div class="mb-1"><span class="chip" style="background:' + color + '22;color:' + color + '">' + esc(HEALTH_LABEL[i.health] || '-') + '</span>' +
                    '<span class="chip" style="background:#eef1f6;color:#41506a">' + esc(STATUS_LABEL[i.status] || i.status) + '</span>' +
                    (i.urgent ? '<span class="chip" style="background:#fde8ea;color:#c21f30">⚠ Mendesak</span>' : '') + '</div>' +
                    '<div class="meta"><i class="fa-solid fa-user"></i> ' + esc(owner ? owner.name : 'Belum ditugaskan') + ' &nbsp; <i class="fa-regular fa-calendar"></i> ' + fmt(i.start_date) + ' – ' + fmt(i.target_date, true) + '</div>' +
                    '<div class="bar"><span style="width:' + (done ? 100 : Number(i.progress)) + '%;background:' + color + '"></span></div>' +
                    '<div class="meta">' + (done ? 100 : Number(i.progress)) + '% · ' + esc(when) + (i.message && !done ? ' · ' + esc(i.message) : '') + '</div>' +
                    '<a class="go" href="' + itemLink(i.id) + '" target="_blank" rel="noopener"><i class="fa-solid fa-up-right-from-square"></i> Buka di Work Tracker</a>';
            }
            function memberCard(memberId, userName) {
                const p = world.board.programmers.find(function (x) { return x.id === Number(memberId); });
                if (!p) return '';
                const items = world.board.items.filter(function (i) { return Number(i.programmer_id) === p.id && ['baru', 'dikerjakan'].includes(i.status); })
                    .sort(function (a, b) { return String(a.target_date || '9999').localeCompare(String(b.target_date || '9999')); });
                const desk = world.desks[p.id];
                const online = desk && !desk.npc.visible;
                return '<button class="close" data-close>&times;</button>' +
                    '<div class="meta">' + esc(p.division) + (online ? ' · <span style="color:#0c7f3e;font-weight:700">● online</span>' : ' · sedang tidak online') + '</div>' +
                    '<h5><span class="chip" style="background:' + esc(p.color) + ';color:#fff">' + esc(initials(p.name)) + '</span>' + esc(userName || p.name) + '</h5>' +
                    '<div class="meta">' + items.length + ' kerjaan aktif</div>' +
                    '<div class="list">' + (items.length ? items.map(function (i) {
                        const color = HEALTH_COLOR[i.health] || '#8a97ad';
                        return '<a href="' + itemLink(i.id) + '" target="_blank" rel="noopener"><b>' + esc(trunc(i.title, 44)) + '</b>' +
                            '<div class="bar" style="margin:4px 0 2px"><span style="width:' + Number(i.progress) + '%;background:' + color + '"></span></div>' +
                            '<span class="meta">' + i.progress + '% · target ' + fmt(i.target_date, true) + ' · ' + esc(HEALTH_LABEL[i.health] || '') + '</span></a>';
                    }).join('') : '<div class="meta py-2">Tidak ada kerjaan aktif. ☕</div>') + '</div>';
            }
            function cardHtml(target) {
                if (target.type === 'item') return itemCard(target.id);
                if (target.type === 'member') return memberCard(target.id);
                if (target.type === 'player') {
                    const pl = net.players.get(target.id);
                    if (!pl) return '';
                    if (pl.member_id) return memberCard(pl.member_id, pl.name);
                    return '<button class="close" data-close>&times;</button><div class="meta">Tamu kampus</div><h5>' + esc(pl.name) + '</h5><div class="meta">Sapa lewat chat: tekan Enter lalu ketik pesan 👋</div>';
                }
                if (target.type === 'cat') return '<button class="close" data-close>&times;</button><div class="meta">Kucing kantor</div><h5>Si Oyen 🐾</h5><div class="meta">Meong! Si Oyen suka jalan-jalan keliling plaza. Ia tidak mengerjakan apa pun, tapi semangatnya menular.</div>';
                if (target.type === 'companion') {
                    const c = companions.find(function (x) { return x.userId === target.id; });
                    if (!c) return '';
                    const owner = world.board.programmers.find(function (p) { return Number(p.user_id) === c.userId; });
                    const ownerName = owner ? owner.name : (c.conf.owner || '');
                    return '<button class="close" data-close>&times;</button>' +
                        '<div class="d-flex gap-3 align-items-center"><img src="' + esc(c.conf.image) + '" alt="' + esc(c.conf.name) + '" style="width:74px;height:94px;object-fit:cover;border-radius:14px;border:3px solid #8b5cf6;flex:none">' +
                        '<div><div class="meta">Pendamping setia ' + esc(ownerName) + '</div><h5>' + esc(c.conf.name) + ' ✨</h5>' +
                        '<div class="meta">' + (c.mode === 'desk' ? esc(c.conf.name) + ' sedang menunggu ' + esc(c.conf.owner) + ' di mejanya.' : esc(c.conf.name) + ' selalu menempel ke mana pun ' + esc(c.conf.owner) + ' pergi.') + '</div>' +
                        '<div class="meta mt-1" style="font-style:italic">' + esc(companionLine(c, c.mode || 'online')) + '</div></div></div>';
                }
                if (target.type === 'agenda') {
                    const ev = world.events[0];
                    return '<button class="close" data-close>&times;</button><div class="meta">Agenda Kampus</div><h5>' + esc(ev.emoji + ' ' + ev.title) + '</h5>' +
                        '<div class="meta">' + esc(ev.sub) + '</div><div class="meta mt-2" style="font-style:italic">“' + esc(quoteOf(world.today)) + '”</div>' +
                        '<div class="list">' + [1, 2, 3, 4, 5, 6, 7].map(function (d) {
                            const iso = addDays(world.today, d);
                            const e = eventsFor(iso, false)[0];
                            return '<a href="javascript:void(0)"><b>' + esc(parseDate(iso).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'short' })) + '</b><br><span class="meta">' + esc(e.emoji + ' ' + e.title + ' · ' + e.sub) + '</span></a>';
                        }).join('') + '</div>';
                }
                if (target.type === 'board') return '<button class="close" data-close>&times;</button><div class="meta">Papan Kampus</div><h5>Ringkasan tim hari ini</h5><div class="meta">Angka di papan dihitung dari seluruh kerjaan tim. Jalan ke selatan untuk menyusuri Jalan Waktu: kerjaan yang sudah lewat di sebelah kiri gerbang HARI INI, rencana ke depan di sebelah kanan.</div>';
                return '';
            }
            function showCard(target, pinned) {
                const key = target ? target.type + ':' + target.id : null;
                if (!target) {
                    if (cardPinned) return;
                    cardKey = null;
                    $('#ww-card').addClass('d-none');
                    return;
                }
                if (key === cardKey && !pinned) return;
                const html = cardHtml(target);
                if (!html) return;
                cardKey = key;
                cardPinned = !!pinned;
                $('#ww-card').html(html).removeClass('d-none');
            }
            $('#ww-card').on('click', '[data-close]', function () {
                cardPinned = false;
                cardKey = null;
                $('#ww-card').addClass('d-none');
                canvas.focus();
            });
            function nearest() {
                let best = null, bestD = Infinity;
                interactables.forEach(function (o) {
                    let d;
                    if (o.type === 'item') {
                        const cx = Math.max(o.x0, Math.min(o.x1, me.pos.x));
                        d = Math.hypot(me.pos.x - cx, me.pos.z - o.z);
                    } else {
                        d = Math.hypot(me.pos.x - o.x, me.pos.z - o.z);
                    }
                    if (d < o.r && d < bestD) {
                        best = o;
                        bestD = d;
                    }
                });
                net.players.forEach(function (pl) {
                    const d = Math.hypot(me.pos.x - pl.mesh.position.x, me.pos.z - pl.mesh.position.z);
                    if (d < 2.6 && d < bestD) {
                        best = { type: 'player', id: pl.id };
                        bestD = d;
                    }
                });
                return best;
            }

            // ---------- Minimap ----------
            const mini = document.getElementById('ww-minimap');
            const mg = mini.getContext('2d');
            function drawMinimap() {
                const S = mini.width, scale = S / 2 / 125, cx = S / 2, cy = S / 2 - 20;
                const px = function (x) { return cx + (x - me.pos.x * .35) * scale; };
                const pz = function (z) { return cy + (z - me.pos.z * .35) * scale; };
                mg.clearRect(0, 0, S, S);
                mg.fillStyle = '#2f9fd8';
                mg.fillRect(0, 0, S, S);
                mg.fillStyle = '#7cc35f';
                mg.beginPath();
                mg.arc(px(0), pz(0), ISLAND_R * scale, 0, Math.PI * 2);
                mg.fill();
                mg.fillStyle = '#5d6880';
                mg.fillRect(px(world.roadX0), pz(ROAD_Z - ROAD_W / 2), (world.roadX1 - world.roadX0) * scale, ROAD_W * scale);
                mg.fillStyle = '#f0b429';
                mg.fillRect(px(-DAY / 2), pz(ROAD_Z - ROAD_W / 2 - 1), DAY * scale, (ROAD_W + 2) * scale);
                mg.fillStyle = '#d9d4c7';
                mg.beginPath();
                mg.arc(px(0), pz(0), 15 * scale, 0, Math.PI * 2);
                mg.fill();
                world.buildings.forEach(function (b) {
                    mg.fillStyle = b.color;
                    mg.beginPath();
                    mg.arc(px(b.x), pz(b.z), 9 * scale, 0, Math.PI * 2);
                    mg.fill();
                });
                net.players.forEach(function (pl) {
                    mg.fillStyle = pl.color;
                    mg.strokeStyle = '#fff';
                    mg.lineWidth = 3;
                    mg.beginPath();
                    mg.arc(px(pl.mesh.position.x), pz(pl.mesh.position.z), 7, 0, Math.PI * 2);
                    mg.fill();
                    mg.stroke();
                });
                companions.forEach(function (c) {
                    if (!c.group.visible) return;
                    mg.fillStyle = '#c4b5fd';
                    mg.beginPath();
                    mg.arc(px(c.group.position.x), pz(c.group.position.z), 4, 0, Math.PI * 2);
                    mg.fill();
                });
                mg.save();
                mg.translate(px(me.pos.x), pz(me.pos.z));
                mg.rotate(-me.ry + Math.PI);
                mg.fillStyle = '#fff';
                mg.beginPath();
                mg.moveTo(0, -13);
                mg.lineTo(9, 10);
                mg.lineTo(0, 5);
                mg.lineTo(-9, 10);
                mg.closePath();
                mg.fill();
                mg.restore();
            }

            // ---------- Input ----------
            const drag = { on: false, x: 0, y: 0, moved: 0 };
            canvas.addEventListener('pointerdown', function (e) {
                canvas.focus();
                drag.on = true;
                drag.x = e.clientX;
                drag.y = e.clientY;
                drag.moved = 0;
                canvas.setPointerCapture(e.pointerId);
            });
            canvas.addEventListener('pointermove', function (e) {
                if (!drag.on) return;
                const dx = e.clientX - drag.x, dy = e.clientY - drag.y;
                drag.moved += Math.abs(dx) + Math.abs(dy);
                drag.x = e.clientX;
                drag.y = e.clientY;
                if (drag.moved > 4) canvas.classList.add('dragging');
                me.yaw -= dx * .006;
                me.pitch = Math.max(.08, Math.min(1.25, me.pitch + dy * .004));
            });
            canvas.addEventListener('pointerup', function (e) {
                drag.on = false;
                canvas.classList.remove('dragging');
                if (drag.moved < 5) pick(e);
            });
            canvas.addEventListener('wheel', function (e) {
                e.preventDefault();
                me.dist = Math.max(4, Math.min(34, me.dist + e.deltaY * .012));
            }, { passive: false });
            const raycaster = new THREE.Raycaster();
            function pick(e) {
                const rect = canvas.getBoundingClientRect();
                const v = new THREE.Vector2((e.clientX - rect.left) / rect.width * 2 - 1, -(e.clientY - rect.top) / rect.height * 2 + 1);
                raycaster.setFromCamera(v, camera);
                const hit = raycaster.intersectObjects(clickables, false).find(function (h) { return h.object.visible !== false && h.object.parent && h.object.parent.visible !== false; });
                if (hit) showCard(hit.object.userData.interact, true);
            }
            const MOVE_KEYS = ['KeyW', 'KeyA', 'KeyS', 'KeyD', 'ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'Space', 'ShiftLeft', 'ShiftRight'];
            window.addEventListener('keydown', function (e) {
                if ($(e.target).is('input, textarea, select')) return;
                if (e.key === 'Enter') {
                    e.preventDefault();
                    $('#ww-chat-input').focus();
                    return;
                }
                if (e.key === 'Escape') {
                    cardPinned = false;
                    showCard(null);
                }
                if (MOVE_KEYS.includes(e.code)) {
                    me.keys[e.code] = true;
                    if (cardPinned && /Key[WASD]|Arrow/.test(e.code)) cardPinned = false;
                    e.preventDefault();
                }
            });
            window.addEventListener('keyup', function (e) { me.keys[e.code] = false; });
            window.addEventListener('blur', function () { me.keys = {}; });

            $('[data-go]').on('click', function () {
                const go = this.dataset.go;
                if (go === 'plaza') teleport(0, 20, 0);
                // Berdiri sedikit sebelum gerbang, menghadap ke masa depan
                if (go === 'today') teleport(-8, ROAD_Z, -Math.PI / 2);
                if (go === 'desk' && ME.member_id && world.desks[ME.member_id]) {
                    const d = world.desks[ME.member_id];
                    const b = world.buildings.find(function (x) { return x.name === d.member.division; });
                    const out = b ? new THREE.Vector3(-b.x, 0, -b.z).normalize() : new THREE.Vector3(0, 0, 1);
                    teleport(d.x + out.x * 3, d.z + out.z * 3, Math.atan2(out.x, out.z));
                }
                canvas.focus();
            });
            $('#ww-fullscreen').on('click', function () {
                if (document.fullscreenElement) document.exitFullscreen();
                else $ww[0].requestFullscreen && $ww[0].requestFullscreen();
            });

            function resize() {
                const w = $ww.width(), h = $ww.height();
                renderer.setSize(w, h, false);
                camera.aspect = w / h;
                camera.updateProjectionMatrix();
            }
            window.addEventListener('resize', resize);
            document.addEventListener('fullscreenchange', function () { setTimeout(resize, 50); });

            // ---------- Loop ----------
            const clock = new THREE.Clock();
            let tick = 0;
            function loop() {
                requestAnimationFrame(loop);
                const dt = Math.min(.05, clock.getDelta());
                const t = clock.elapsedTime;
                animated.forEach(function (fn) { fn(t, dt); });
                eventAnims.forEach(function (fn) { fn(t, dt); });
                updateMe(dt, t);
                updatePlayers(dt, t);
                updateCompanions(dt, t);
                whisperMove(false);
                tick += dt;
                if (tick > .15) {
                    tick = 0;
                    if (!cardPinned) showCard(nearest(), false);
                    drawMinimap();
                }
                renderer.render(scene, camera);
            }

            // ---------- Mulai ----------
            function loadBoard() {
                return $.get(ROUTES.data).then(function (board) {
                    if (!board || !board.items) throw new Error('Data kosong');
                    return board;
                });
            }
            loadBoard().then(function (board) {
                world.board = board;
                world.today = board.today;
                $('#ww-date').text(parseDate(board.today).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }));
                // Event dihitung lebih dulu karena topi pesta ikut dipasang saat karakter dibuat
                world.events = eventsFor(board.today, true);
                world.partyHats = decorOf(world.events).includes('hats');
                resize();
                buildSky();
                buildIsland();
                buildPlaza();
                buildBuildings(board);
                buildNotice();
                buildAgenda();
                buildRoad();
                buildWork();
                buildNature();
                buildCritters();
                buildEvents();
                buildMe();
                buildCompanions();
                renderEventHud();
                teleport(0, 20, 0);
                refreshOnline();
                drawMinimap();
                addSystemChat('Selamat datang di Kampus Work Tracker, ' + ME.name + '! Tekan Enter untuk menyapa.');
                loop();
                setTimeout(function () { $('#ww-loading').addClass('hide'); }, 300);
                setTimeout(function () { toast(world.events[0].emoji + ' ' + world.events[0].title); }, 900);
                setTimeout(function () { $('#ww-help').fadeOut(600); }, 15000);
                heartbeat();
                connectEcho();

                // Data kerjaan diperbarui tiap 2 menit
                setInterval(function () {
                    loadBoard().then(function (fresh) {
                        world.board = fresh;
                        buildWork();
                        refreshDeskScreens();
                        refreshNotice();
                        // Momen tim bisa berubah (misal ada kerjaan baru selesai); dekorasi dibangun ulang hanya kalau event berubah
                        const fresh_events = eventsFor(world.today, true);
                        const sig = function (list) { return list.map(function (e) { return e.key + e.title; }).join('|'); };
                        if (sig(fresh_events) !== sig(world.events)) {
                            world.events = fresh_events;
                            buildEvents();
                            renderEventHud();
                            refreshAgenda();
                        }
                    });
                }, 120000);
            }).catch(function () {
                $('#ww-loading-text').text('Data Work Tracker gagal dimuat');
                $('#ww-loading small').text('Coba muat ulang halaman.');
            });
        })();
    </script>
@endsection
