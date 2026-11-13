@extends('layouts.index')

@section('custom-link')
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <style>
        :root {
            --wd-navy: #082149; --wd-ink: #1b2536; --wd-muted: #5f6b80; --wd-line: #e4e9f2; --wd-soft: #f4f7fc;
            --wd-series-1: #2f6fd6; --wd-series-2: #0f9d8a;
            --wd-good: #0c7f3e; --wd-warn: #a86700; --wd-bad: #c21f30;
        }
        .wd { color: var(--wd-ink); padding-bottom: 32px; }
        .wd-card { background: #fff; border: 1px solid var(--wd-line); border-radius: 14px; box-shadow: 0 4px 16px rgba(8, 33, 73, .05); padding: 16px 18px; }
        .wd-title { margin: 0; font-size: 15px; font-weight: 700; }
        .wd-sub { font-size: 12px; color: var(--wd-muted); }
        .wd-hero { border-radius: 16px; padding: 18px 24px; margin-bottom: 14px; color: #fff; background: linear-gradient(120deg, var(--wd-navy), #123a78 70%, #1c5bb8); }
        .wd-hero h1 { margin: 0; font-size: 22px; font-weight: 700; }
        .wd-hero .wd-date { color: #bcd0ee; font-size: 13px; }
        .wd-hero label { display: block; margin-bottom: 3px; color: #bcd0ee; font-size: 12px; font-weight: 600; }
        .wd-hero-filter { width: 180px; color: var(--wd-ink); }
        .wd-btn-ghost { align-self: flex-end; border: 1px solid rgba(255, 255, 255, .4); color: #fff; font-weight: 600; }
        .wd-btn-ghost:hover { background: rgba(255, 255, 255, .12); color: #fff; }
        .wd-btn-3d { position: relative; align-self: flex-end; overflow: hidden; border: 0; background: linear-gradient(120deg, #22c1c3, #3b82f6 55%, #8b5cf6); color: #fff; font-weight: 700; box-shadow: 0 0 0 0 rgba(59, 130, 246, .6); animation: wd-3d-pulse 2.4s infinite; }
        .wd-btn-3d:hover { color: #fff; filter: brightness(1.08); }
        .wd-btn-3d::after { content: ""; position: absolute; top: 0; left: -60%; width: 40%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .45), transparent); transform: skewX(-20deg); animation: wd-3d-shine 3.2s infinite; }
        @keyframes wd-3d-pulse { 0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, .55); } 70% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); } 100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); } }
        @keyframes wd-3d-shine { 0%, 60% { left: -60%; } 100% { left: 130%; } }
        .wd-demo { padding: 8px 14px; margin-bottom: 14px; border: 1px solid #f3d995; border-radius: 10px; background: #fff5dc; color: #755300; font-size: 13px; }

        .wd-kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 12px; margin-bottom: 14px; }
        .wd-kpi-label { font-size: 12px; color: var(--wd-muted); }
        .wd-kpi-value { font-size: 26px; font-weight: 700; line-height: 1.2; }
        .wd-kpi-note { font-size: 11.5px; color: var(--wd-muted); }

        .wd-verdict { display: flex; flex-wrap: wrap; align-items: center; gap: 10px 14px; padding: 12px 14px; margin: 10px 0; border-radius: 10px; background: var(--wd-soft); }
        .wd-verdict-main { font-size: 17px; font-weight: 700; }
        .wd-verdict i { font-size: 20px; }
        .wd-verdict.good i { color: var(--wd-good); } .wd-verdict.warn i { color: var(--wd-warn); } .wd-verdict.bad i { color: var(--wd-bad); }
        .wd-badge { display: inline-block; padding: 2px 9px; border-radius: 12px; background: #fff2d6; color: var(--wd-warn); font-size: 11.5px; font-weight: 700; }
        .wd-line-legend { display: flex; flex-wrap: wrap; gap: 16px; font-size: 12px; color: var(--wd-muted); }
        .wd-line-legend span::before { content: ""; display: inline-block; width: 26px; margin-right: 6px; vertical-align: middle; border-top: 2px solid var(--wd-series-1); }
        .wd-line-legend .dashed::before { border-top-style: dashed; }
        .wd-sim { display: grid; grid-template-columns: auto minmax(160px, 1fr) auto; gap: 12px; align-items: center; margin-top: 6px; padding-top: 10px; border-top: 1px solid var(--wd-line); font-size: 13px; }
        .wd-sim input { width: 100%; accent-color: var(--wd-series-1); }
        .wd-sim-result { min-width: 210px; text-align: right; font-weight: 600; }

        .wd-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; margin-top: 14px; }
        .wd-division { margin: 10px 0 4px; font-size: 11.5px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: var(--wd-navy); }
        .wd-member { display: grid; grid-template-columns: 130px minmax(100px, 1fr) 130px; gap: 10px; align-items: center; padding: 6px 0; font-size: 13px; }
        .wd-member-name { display: flex; align-items: center; gap: 7px; min-width: 0; font-weight: 600; }
        .wd-member-name b { overflow: hidden; font-weight: 600; white-space: nowrap; text-overflow: ellipsis; }
        .wd-dot { flex: none; width: 10px; height: 10px; border-radius: 50%; }
        .wd-bullet { position: relative; height: 14px; border-radius: 4px; background: #e9eef6; }
        .wd-bullet-fill { position: absolute; left: 0; top: 2px; bottom: 2px; border-radius: 0 4px 4px 0; background: var(--wd-series-1); transition: width .7s ease; }
        .wd-bullet-mark { position: absolute; top: -3px; bottom: -3px; width: 3px; border-radius: 2px; background: var(--wd-navy); box-shadow: 0 0 0 2px #fff; }
        .wd-chip { display: inline-flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 20px; font-size: 11px; font-weight: 700; white-space: nowrap; }
        .wd-chip.good { background: #e0f7ea; color: var(--wd-good); } .wd-chip.warn { background: #fff2d6; color: var(--wd-warn); } .wd-chip.bad { background: #ffe3e6; color: var(--wd-bad); } .wd-chip.none { background: #eceff4; color: #5b6577; }
        .wd-member-side { text-align: right; }
        .wd-table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
        .wd-table th { padding: 6px 8px; border-bottom: 2px solid var(--wd-line); color: var(--wd-muted); font-size: 11px; text-transform: uppercase; white-space: nowrap; }
        .wd-table td { padding: 7px 8px; border-bottom: 1px solid var(--wd-line); vertical-align: middle; }
        .wd-risk-link { display: block; color: var(--wd-ink); font-weight: 600; }
        .wd-risk-link:hover { color: var(--wd-series-1); text-decoration: underline; }
        .wd-comp-group + .wd-comp-group { margin-top: 12px; }
        .wd-comp-row { display: grid; grid-template-columns: 130px 1fr 28px; gap: 8px; align-items: center; padding: 3px 0; font-size: 12.5px; }
        .wd-comp-bar { height: 10px; border-radius: 0 4px 4px 0; background: var(--wd-series-1); transition: width .7s ease; }
        .wd-empty { padding: 34px 16px; text-align: center; color: var(--wd-muted); }
        .wd-empty i { display: block; margin-bottom: 10px; font-size: 34px; color: #b9c6da; }
        .wd details { margin-top: 8px; font-size: 12.5px; }
        .wd summary { color: var(--wd-muted); cursor: pointer; }
        .wd-table-scroll { max-height: 240px; margin-top: 6px; overflow: auto; }
        @media (max-width: 991.98px) {
            .wd-grid { grid-template-columns: 1fr; }
            .wd-sim { grid-template-columns: 1fr; }
            .wd-sim-result { text-align: left; }
        }
        @media (prefers-reduced-motion: reduce) {
            .wd * { transition: none !important; }
        }
    </style>
@endsection

@section('content')
    <div class="wd">
        <div class="wd-hero d-flex flex-wrap justify-content-between gap-3">
            <div>
                <h1><i class="fa-solid fa-gauge-high"></i> Dashboard Work Tracker</h1>
                <div class="wd-date" id="wd-date"></div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <div class="wd-hero-filter"><label for="wd-division">Divisi</label><select id="wd-division"><option value="">Semua divisi</option></select></div>
                <div class="wd-hero-filter"><label for="wd-period">Periode</label>
                    <select id="wd-period"><option value="month">Bulan ini</option><option value="30">30 hari terakhir</option><option value="90">90 hari terakhir</option></select>
                </div>
                <a class="btn btn-sm wd-btn-ghost" href="{{ route('work-tracker') }}"><i class="fa-solid fa-person-running"></i> Work Tracker</a>
                <a class="btn btn-sm wd-btn-3d" href="{{ route('work-tracker-world') }}"><i class="fa-solid fa-cube"></i> Dunia 3D</a>
            </div>
        </div>

        <div class="wd-demo d-none" id="wd-demo-banner">
            <i class="fa-solid fa-eye"></i> <b>Mode contoh:</b> data ini hanya untuk melihat tampilan dan tidak tersimpan.
            <a href="#" class="fw-bold ms-1" id="wd-demo-exit">Kembali ke data asli</a>
        </div>

        <div class="wd-card wd-empty d-none" id="wd-empty">
            <i class="fa-solid fa-chart-line"></i>Belum ada kerjaan untuk ditampilkan.
            <div class="mt-3"><button class="btn btn-outline-secondary btn-sm" id="wd-demo-start"><i class="fa-solid fa-eye"></i> Lihat Contoh Tampilan</button></div>
        </div>

        <div id="wd-content" class="d-none">
            <div class="wd-kpis" id="wd-kpis"></div>

            <section class="wd-card">
                <div class="d-flex flex-wrap justify-content-between gap-2">
                    <h2 class="wd-title">Proyeksi: dengan pace sekarang, keburu atau tidak?</h2>
                    <div class="wd-line-legend"><span>Sisa beban aktual</span><span class="dashed">Proyeksi</span></div>
                </div>
                <div class="wd-verdict" id="wd-verdict"></div>
                <div id="wd-burn-chart" style="min-height: 320px;"></div>
                <div class="wd-sim">
                    <label for="wd-sim-range" class="mb-0 fw-bold">Simulasi pace <span id="wd-sim-label">sekarang</span></label>
                    <input type="range" id="wd-sim-range" min="-50" max="100" step="10" value="0">
                    <div class="wd-sim-result" id="wd-sim-result"></div>
                </div>
                <div class="wd-sub mt-1" id="wd-basis"></div>
                <details><summary>Lihat data sebagai tabel</summary><div class="wd-table-scroll" id="wd-burn-table"></div></details>
            </section>

            <div class="wd-grid">
                <section class="wd-card">
                    <h2 class="wd-title">Beban per anggota</h2>
                    <div class="wd-sub">Batang: sisa beban (hari). Garis tegak: hari yang tersedia sampai target terakhir.</div>
                    <div id="wd-members"></div>
                </section>
                <section class="wd-card">
                    <h2 class="wd-title">Kerjaan paling berisiko</h2>
                    <div class="wd-sub">Klik kerjaan untuk membukanya di Work Tracker.</div>
                    <div class="table-responsive" id="wd-risks"></div>
                </section>
                <section class="wd-card">
                    <h2 class="wd-title">Kerjaan masuk vs selesai per minggu</h2>
                    <div class="wd-sub">Kalau yang masuk terus lebih banyak dari yang selesai, antrean makin menumpuk.</div>
                    <div id="wd-weekly-chart" style="min-height: 280px;"></div>
                    <details><summary>Lihat data sebagai tabel</summary><div class="wd-table-scroll" id="wd-weekly-table"></div></details>
                </section>
                <section class="wd-card">
                    <h2 class="wd-title">Komposisi kerjaan aktif</h2>
                    <div id="wd-composition" class="mt-2"></div>
                </section>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('plugins/apexcharts/apexcharts.min.js') }}"></script>
    <script>
        (function () {
            const DATA_URL = @json(route('data-work-tracker-dashboard'));
            const TRACKER_URL = @json(route('work-tracker'));
            const REFRESH_MS = 300000;
            const SERIES = { first: '#2f6fd6', second: '#0f9d8a', ink: '#1b2536', muted: '#5f6b80', grid: '#e9eef6' };
            const HEALTH = {
                telat: ['bad', 'fa-fire', 'Telat'],
                mepet: ['warn', 'fa-hourglass-half', 'Mepet'],
                aman: ['good', 'fa-circle-check', 'Aman'],
                belum_dinilai: ['none', 'fa-circle-minus', 'Belum dinilai'],
            };
            const TIER = {
                sprint: ['good', 'fa-circle-check', 'Aman'],
                jog: ['warn', 'fa-hourglass-half', 'Mepet'],
                walk: ['bad', 'fa-fire', 'Kurang waktu'],
                rest: ['none', 'fa-mug-hot', 'Kosong'],
                finish: ['good', 'fa-trophy', 'Beres'],
            };

            const state = { data: null, demo: false, burn: null, weekly: null };

            function esc(value) {
                return $('<div>').text(value === null || value === undefined ? '' : value).html().replace(/"/g, '&quot;').replace(/'/g, '&#39;');
            }
            function toDate(iso) {
                const p = iso.split('-').map(Number);
                return new Date(p[0], p[1] - 1, p[2]);
            }
            function toIso(date) {
                return date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0');
            }
            function addDays(iso, days) {
                const date = toDate(iso);
                date.setDate(date.getDate() + days);
                return toIso(date);
            }
            function fmt(iso, withYear) {
                if (!iso) return '-';
                const options = { day: 'numeric', month: 'short' };
                if (withYear) options.year = 'numeric';
                return toDate(iso).toLocaleDateString('id-ID', options);
            }
            function num(value, digits) {
                return Number(value || 0).toLocaleString('id-ID', { maximumFractionDigits: digits === undefined ? 1 : digits });
            }
            function dayDiff(a, b) {
                return Math.round((toDate(b) - toDate(a)) / 86400000);
            }
            function chip(def) {
                return '<span class="wd-chip ' + def[0] + '"><i class="fa-solid ' + def[1] + '"></i> ' + esc(def[2]) + '</span>';
            }

            function load() {
                const params = { division: $('#wd-division').val(), period: $('#wd-period').val() };
                if (state.demo) params.demo = 1;
                return $.get(DATA_URL, params).done(function (data) {
                    state.data = data;
                    render();
                }).fail(function (xhr) {
                    const json = xhr.responseJSON || {};
                    iziToast.error({ message: json.message || 'Gagal memuat dashboard', position: 'topCenter' });
                });
            }

            function render() {
                const d = state.data;
                $('#wd-date').text(toDate(d.today).toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }));
                $('#wd-demo-banner').toggleClass('d-none', !state.demo);

                const current = $('#wd-division').val();
                $('#wd-division').html('<option value="">Semua divisi</option>' + d.divisions.map(function (name) {
                    return '<option value="' + esc(name) + '">' + esc(name) + '</option>';
                }).join('')).val(current || '').trigger('change.select2');

                const filtered = !!current;
                $('#wd-empty').toggleClass('d-none', d.has_items || filtered);
                $('#wd-content').toggleClass('d-none', !d.has_items && !filtered);
                if (!d.has_items && !filtered) return;

                renderKpis(d);
                renderMembers(d.members);
                renderRisks(d.risks);
                renderComposition(d.composition);
                renderWeekly(d.weekly);
                renderBurn(d);
                simulate();
            }

            function renderKpis(d) {
                const p = d.projection;
                const k = d.kpi;
                const tiles = [
                    ['Kerjaan aktif', k.active, k.in_progress + ' sedang dikerjakan'],
                    ['Selesai pada periode', k.done, 'Sejak ' + fmt(d.from)],
                    ['Tepat waktu', k.on_time_pct === null ? '-' : k.on_time_pct + '%', k.on_time_base ? 'Dari ' + k.on_time_base + ' kerjaan bertarget' : 'Belum ada data'],
                    ['Prediksi semua selesai', p.remaining <= 0 ? 'Beres' : (p.predicted_date ? fmt(p.predicted_date) : '-'), p.remaining > 0 ? 'Sisa ' + num(p.remaining) + ' hari beban' : 'Tidak ada sisa beban'],
                    ['Diprediksi telat', k.late, k.urgent ? k.urgent + ' masalah mendesak' : 'Tidak ada masalah mendesak'],
                ];
                $('#wd-kpis').html(tiles.map(function (t) {
                    return '<div class="wd-card"><div class="wd-kpi-label">' + esc(t[0]) + '</div><div class="wd-kpi-value">' + esc(t[1]) + '</div><div class="wd-kpi-note">' + esc(t[2]) + '</div></div>';
                }).join(''));
            }

            function renderMembers(members) {
                if (!members.length) {
                    $('#wd-members').html('<div class="wd-empty"><i class="fa-solid fa-users"></i>Belum ada anggota.</div>');
                    return;
                }
                const scale = Math.max.apply(null, members.map(function (m) { return Math.max(m.effort_left || 0, m.available_days || 0); }).concat([1]));
                let division = null;
                $('#wd-members').html(members.map(function (m) {
                    const head = m.division !== division ? '<div class="wd-division">' + esc(m.division) + '</div>' : '';
                    division = m.division;
                    const fill = (m.effort_left || 0) / scale * 100;
                    const mark = m.available_days !== null ? '<div class="wd-bullet-mark" style="left:calc(' + (m.available_days / scale * 100) + '% - 2px)"></div>' : '';
                    const note = m.available_days === null ? 'Sisa ' + num(m.effort_left) + ' hari, tanpa target' : 'Sisa ' + num(m.effort_left) + ' hari, tersedia ' + m.available_days + ' hari';
                    const sideNote = !m.active ? 'Tidak ada kerjaan aktif' : (m.available_days === null ? 'Sisa ' + num(m.effort_left) + ' hari' : num(m.effort_left) + ' dari ' + m.available_days + ' hari');
                    return head + '<div class="wd-member"><div class="wd-member-name"><span class="wd-dot" style="background:' + esc(m.color) + '"></span><b title="' + esc(m.name) + '">' + esc(m.name) + '</b></div>' +
                        '<div class="wd-bullet" title="' + esc(note) + '"><div class="wd-bullet-fill" style="width:' + fill + '%"></div>' + mark + '</div>' +
                        '<div class="wd-member-side">' + chip(TIER[m.tier]) + '<div class="wd-sub">' + esc(sideNote) + '</div></div></div>';
                }).join(''));
            }

            function renderRisks(risks) {
                if (!risks.length) {
                    $('#wd-risks').html('<div class="wd-empty"><i class="fa-solid fa-shield-halved"></i>Tidak ada kerjaan yang berisiko.</div>');
                    return;
                }
                const rows = risks.map(function (r) {
                    const needed = r.needed_pace === null ? 'Target lewat' : num(r.needed_pace) + '%/hari';
                    return '<tr><td><a class="wd-risk-link" href="' + esc(TRACKER_URL + '?item=' + r.id) + '">' + esc(r.title) + '</a><div class="wd-sub">' + esc(r.code + (r.programmer ? ' · ' + r.programmer : ' · Belum ditugaskan')) + '</div></td>' +
                        '<td class="text-nowrap">' + (r.pace ? num(r.pace) + '%/hari' : '-') + '</td><td class="text-nowrap">' + esc(needed) + '</td>' +
                        '<td class="text-nowrap">' + fmt(r.target_date) + '</td><td>' + (r.urgent ? chip(['bad', 'fa-road-barrier', 'Mendesak']) : chip(HEALTH[r.health])) + '</td></tr>';
                }).join('');
                $('#wd-risks').html('<table class="wd-table mt-2"><thead><tr><th>Kerjaan</th><th>Pace</th><th>Dibutuhkan</th><th>Target</th><th>Kondisi</th></tr></thead><tbody>' + rows + '</tbody></table>');
            }

            function renderComposition(composition) {
                const groups = [['Jenis', composition.type], ['Level', composition.level], ['Divisi', composition.division]];
                const total = Math.max(1, composition.type.reduce(function (sum, row) { return sum + row.count; }, 0));
                if (!composition.type.length) {
                    $('#wd-composition').html('<div class="wd-empty"><i class="fa-solid fa-layer-group"></i>Tidak ada kerjaan aktif.</div>');
                    return;
                }
                $('#wd-composition').html(groups.map(function (g) {
                    return '<div class="wd-comp-group"><div class="wd-sub fw-bold mb-1">' + g[0] + '</div>' + g[1].map(function (row) {
                        return '<div class="wd-comp-row"><span>' + esc(row.label) + '</span><div><div class="wd-comp-bar" style="width:' + (row.count / total * 100) + '%"></div></div><b class="text-end">' + row.count + '</b></div>';
                    }).join('') + '</div>';
                }).join(''));
            }

            function axisStyle() {
                return { colors: SERIES.muted, fontSize: '11px' };
            }

            function renderWeekly(weeks) {
                const categories = weeks.map(function (w) { return fmt(w.start); });
                const series = [
                    { name: 'Masuk', data: weeks.map(function (w) { return w.in; }) },
                    { name: 'Selesai', data: weeks.map(function (w) { return w.done; }) },
                ];
                if (state.weekly) {
                    state.weekly.updateOptions({ series: series, xaxis: { categories: categories } });
                } else {
                    state.weekly = new ApexCharts(document.getElementById('wd-weekly-chart'), {
                        chart: { type: 'bar', height: 280, toolbar: { show: false }, fontFamily: 'inherit', animations: { enabled: false } },
                        series: series,
                        colors: [SERIES.first, SERIES.second],
                        plotOptions: { bar: { columnWidth: '55%', borderRadius: 4, borderRadiusApplication: 'end' } },
                        stroke: { show: true, width: 2, colors: ['#ffffff'] },
                        dataLabels: { enabled: false },
                        grid: { borderColor: SERIES.grid, strokeDashArray: 3 },
                        legend: { position: 'top', horizontalAlign: 'left', labels: { colors: SERIES.ink } },
                        xaxis: { categories: categories, title: { text: 'Minggu mulai', style: { color: SERIES.muted, fontWeight: 400 } }, labels: { style: axisStyle() }, axisBorder: { color: SERIES.grid }, axisTicks: { show: false } },
                        yaxis: { min: 0, forceNiceScale: true, decimalsInFloat: 0, labels: { style: axisStyle() } },
                        tooltip: { shared: true, intersect: false },
                    });
                    state.weekly.render();
                }
                $('#wd-weekly-table').html('<table class="wd-table"><thead><tr><th>Minggu mulai</th><th>Masuk</th><th>Selesai</th></tr></thead><tbody>' + weeks.map(function (w) {
                    return '<tr><td>' + fmt(w.start, true) + '</td><td>' + w.in + '</td><td>' + w.done + '</td></tr>';
                }).join('') + '</tbody></table>');
            }

            function ts(iso) {
                return toDate(iso).getTime();
            }

            // Tanggal selesai untuk pace tertentu, null kalau melewati batas proyeksi
            function finishFor(pace) {
                const p = state.data.projection;
                if (p.remaining <= 0) return { days: 0, date: state.data.today };
                const days = Math.ceil(p.remaining / pace);
                return days > p.horizon_days ? { days: days, date: null } : { days: days, date: addDays(state.data.today, days) };
            }

            function burnSeries(finish) {
                const d = state.data;
                const actual = d.history.map(function (h) { return [ts(h.date), h.remaining]; });
                const projected = d.projection.remaining > 0 && finish.date ? [[ts(d.today), d.projection.remaining], [ts(finish.date), 0]] : [];
                return [{ name: 'Sisa beban aktual', data: actual }, { name: 'Proyeksi', data: projected }];
            }

            function marker(iso, text, color, offsetY) {
                return {
                    x: ts(iso), borderColor: color, strokeDashArray: 4,
                    label: { text: text, orientation: 'horizontal', offsetY: offsetY, borderColor: color, style: { background: '#ffffff', color: SERIES.ink, fontSize: '11px' } },
                };
            }

            function burnMarkers(finish) {
                const d = state.data;
                const lines = [marker(d.today, 'Hari ini', SERIES.muted, 0)];
                if (d.projection.latest_target) lines.push(marker(d.projection.latest_target, 'Target ' + fmt(d.projection.latest_target), SERIES.ink, 20));
                if (finish.date && d.projection.remaining > 0) lines.push(marker(finish.date, 'Prediksi ' + fmt(finish.date), SERIES.first, 40));
                return { xaxis: lines };
            }

            // Sumbu waktu selalu memuat hari ini, target, dan prediksi, plus ruang untuk labelnya
            function burnMaxX(finish) {
                const d = state.data;
                const last = [d.today, d.projection.latest_target, finish.date].filter(Boolean).reduce(function (a, b) { return a > b ? a : b; });
                return ts(addDays(last, Math.max(3, Math.ceil(dayDiff(d.from, last) * 0.12))));
            }

            function renderBurn(d) {
                const finish = finishFor(d.projection.pace);
                if (state.burn) {
                    state.burn.updateOptions({ series: burnSeries(finish), annotations: burnMarkers(finish), xaxis: { max: burnMaxX(finish) } });
                } else {
                    state.burn = new ApexCharts(document.getElementById('wd-burn-chart'), {
                        chart: { type: 'line', height: 320, toolbar: { show: false }, zoom: { enabled: false }, fontFamily: 'inherit' },
                        series: burnSeries(finish),
                        annotations: burnMarkers(finish),
                        colors: [SERIES.first, SERIES.first],
                        stroke: { width: [2, 2], dashArray: [0, 6], curve: 'straight' },
                        markers: { size: 0, hover: { size: 5 } },
                        legend: { show: false },
                        grid: { borderColor: SERIES.grid, strokeDashArray: 3 },
                        xaxis: { type: 'datetime', max: burnMaxX(finish), labels: { datetimeUTC: false, format: 'd MMM', style: axisStyle() }, axisBorder: { color: SERIES.grid }, axisTicks: { show: false }, tooltip: { enabled: false } },
                        yaxis: { min: 0, forceNiceScale: true, title: { text: 'Sisa beban (hari)', style: { color: SERIES.muted, fontWeight: 400 } }, labels: { style: axisStyle(), formatter: function (v) { return num(v); } } },
                        tooltip: { shared: true, intersect: false, x: { format: 'd MMM yyyy' }, y: { formatter: function (v) { return v === null || v === undefined ? '-' : num(v) + ' hari'; } } },
                    });
                    state.burn.render();
                }
                $('#wd-burn-table').html('<table class="wd-table"><thead><tr><th>Tanggal</th><th>Sisa beban (hari)</th></tr></thead><tbody>' + d.history.slice().reverse().map(function (h) {
                    return '<tr><td>' + fmt(h.date, true) + '</td><td>' + num(h.remaining) + '</td></tr>';
                }).join('') + '</tbody></table>');
            }

            function verdictFor(finish) {
                const p = state.data.projection;
                if (p.remaining <= 0) return ['good', 'fa-trophy', 'Tidak ada sisa beban', 'Semua kerjaan aktif sudah beres.'];
                if (!finish.date) return ['bad', 'fa-triangle-exclamation', 'Dengan pace ini, belum selesai dalam ' + p.horizon_days + ' hari', 'Pace terlalu kecil dibanding sisa beban.'];
                if (!p.latest_target) return ['warn', 'fa-circle-info', 'Diprediksi selesai ' + fmt(finish.date, true), 'Belum ada target tanggal untuk dibandingkan.'];
                const slack = dayDiff(finish.date, p.latest_target);
                const needed = p.needed_pace ? 'Agar keburu, butuh ' + num(p.needed_pace, 2) + ' hari beban per hari.' : 'Target terakhir sudah lewat.';
                if (slack < 0) return ['bad', 'fa-triangle-exclamation', 'Telat ' + Math.abs(slack) + ' hari: diprediksi selesai ' + fmt(finish.date, true), 'Target terakhir ' + fmt(p.latest_target, true) + '. ' + needed];
                if (slack <= p.tight_slack_days) return ['warn', 'fa-hourglass-half', 'Mepet: diprediksi selesai ' + fmt(finish.date, true), 'Target terakhir ' + fmt(p.latest_target, true) + ', sisa waktu ' + slack + ' hari.'];
                return ['good', 'fa-circle-check', 'Keburu: diprediksi selesai ' + fmt(finish.date, true), 'Target terakhir ' + fmt(p.latest_target, true) + ', sisa waktu ' + slack + ' hari.'];
            }

            function simulate() {
                const p = state.data.projection;
                const percent = Number($('#wd-sim-range').val());
                const pace = p.pace * (1 + percent / 100);
                const finish = finishFor(pace);
                const verdict = verdictFor(finish);
                const basisBadge = p.basis === 'estimasi' ? '<span class="wd-badge">Data belum cukup</span>' : '';

                $('#wd-sim-label').text(percent === 0 ? 'sekarang' : (percent > 0 ? '+' : '') + percent + '%');
                $('#wd-sim-range').prop('disabled', p.remaining <= 0);
                $('#wd-sim-result').text(p.remaining <= 0 ? '-' : num(pace, 2) + ' hari beban per hari');
                $('#wd-verdict').attr('class', 'wd-verdict ' + verdict[0]).html('<i class="fa-solid ' + verdict[1] + '"></i><div><div class="wd-verdict-main">' + esc(verdict[2]) + '</div><div class="wd-sub">' + esc(verdict[3]) + '</div></div>' + basisBadge);

                const unestimated = p.unestimated ? ' ' + p.unestimated + ' kerjaan tanpa estimasi dihitung 1 hari.' : '';
                $('#wd-basis').text((p.basis === 'riwayat'
                    ? 'Pace dihitung dari riwayat progress ' + p.window_days + ' hari terakhir (' + p.active_days + ' hari ada progress). Semua hari ikut dihitung.'
                    : 'Riwayat progress belum cukup (' + p.active_days + ' hari), jadi tiap orang dianggap menyelesaikan 1 hari beban per hari.') + unestimated);

                if (state.burn) state.burn.updateOptions({ series: burnSeries(finish), annotations: burnMarkers(finish), xaxis: { max: burnMaxX(finish) } }, false, false);
            }

            $('#wd-division, #wd-period').select2({ theme: 'bootstrap4', width: '100%', minimumResultsForSearch: 8 }).on('change', load);
            $('#wd-sim-range').on('input', simulate);
            $('#wd-demo-start').on('click', function () {
                state.demo = true;
                load();
            });
            $('#wd-demo-exit').on('click', function (e) {
                e.preventDefault();
                state.demo = false;
                $('#wd-division').val('').trigger('change.select2');
                load();
            });

            load();
            setInterval(load, REFRESH_MS);
        })();
    </script>
@endsection
