@extends('layouts.index')

@section('custom-link')
<style>
    .dash-wrap {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    /* ===== Page Header — navy card dengan eyebrow + title ===== */
    .page-header {
        background: #0f172a;
        border-radius: 12px;
        padding: 1.4rem 1.75rem;
        margin-bottom: 1.5rem;
        position: relative;
        overflow: hidden;
    }
    .page-header::before {
        content: "";
        position: absolute; left: 0; top: 0; bottom: 0; width: 5px;
        background: repeating-linear-gradient(180deg, #38bdf8 0px, #38bdf8 10px, transparent 10px, transparent 20px);
    }
    .page-header .eyebrow {
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 1.4px;
        text-transform: uppercase;
        color: #5aa9f0;
        margin: 0 0 4px 2px;
        display: block;
    }
    .page-header .dash-title {
        color: #f8fafc;
        margin: 0 0 0 2px;
    }
    .page-header .dash-subtitle {
        color: #94a3b8;
    }

    .dash-title {
        font-size: 1.2rem;
        font-weight: 700;
        color: #343a40;
        margin: 0;
    }

    .dash-subtitle {
        font-size: 0.78rem;
        color: #6c757d;
    }

    #periodBadge {
        background: #1e3a8a;
        border: 1px solid #3085d6;
        color: #dbeafe;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.72rem;
    }

    .section-label {
        font-size: 0.72rem;
        font-weight: 600;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: .06em;
        margin: 4px 2px 8px;
    }

    /* ===== Chart / detail cards ===== */
    .chart-grid {
        display: grid;
        grid-template-columns: 3fr 2fr;
        gap: 10px;
    }
    @media (max-width: 992px) { .chart-grid { grid-template-columns: 1fr; } }

    .chart-grid.cols-2 { grid-template-columns: repeat(2, 1fr); }
    @media (max-width: 768px) { .chart-grid.cols-2 { grid-template-columns: 1fr; } }

    .chart-card {
        background: #fff;
        border: 1px solid #dee2e6;
        border-left: 3px solid #007bff;
        border-radius: 8px;
        padding: 16px;
    }
    .card-heading {
        font-size: 0.8rem;
        font-weight: 700;
        color: #1e293b;
        text-transform: uppercase;
        letter-spacing: .04em;
        padding-bottom: 10px;
        border-bottom: 1px solid #eef1f6;
        margin-bottom: 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .card-heading .heading-main {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .card-icon-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px; height: 20px;
        border-radius: 50%;
        background: #007bff;
        color: #fff;
        font-size: 0.6rem;
        flex-shrink: 0;
    }
    .card-heading .sub {
        font-weight: 400;
        text-transform: none;
        letter-spacing: 0;
        color: #adb5bd;
        font-size: 0.7rem;
    }

    /* ===== Radial KPI (non-mainstream, pengganti bar/pie) ===== */
    .radial-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
        gap: 1rem;
    }
    .radial {
        position: relative;
        width: 100px; height: 100px;
        margin: 0 auto 0.5rem;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
    }
    .radial::before {
        content: "";
        position: absolute; inset: 9px;
        background: #fff;
        border-radius: 50%;
    }
    .radial-value {
        position: relative; z-index: 1;
        font-weight: 700; font-size: 0.74rem;
        color: #343a40; text-align: center; line-height: 1.1;
    }
    .radial-label {
        text-align: center;
        font-size: 0.68rem; font-weight: 600;
        color: #6c757d; margin-top: 2px;
    }

    .slope-legend {
        display: flex; gap: 1.2rem; margin-bottom: 0.5rem; font-size: 0.72rem; color: #6c757d;
    }
    .slope-legend span { display: inline-flex; align-items: center; gap: 5px; }
    .slope-dot { width: 9px; height: 9px; border-radius: 50%; display: inline-block; }

    /* ===== Treemap ===== */
    #dbc-bubbles text.bubble-label { font-size: 11px; font-weight: 700; fill: #fff; pointer-events: none; text-anchor: middle; }
    #dbc-bubbles text.bubble-value { font-size: 9.5px; font-weight: 500; fill: rgba(255,255,255,0.9); pointer-events: none; text-anchor: middle; }
    #dbc-bubbles polygon { stroke: rgba(255,255,255,.55); stroke-width: 1.5; stroke-linejoin: round; transition: transform .15s; transform-origin: center; }
    #dbc-bubbles polygon:hover { transform: scale(1.03); }

    .chart-tooltip {
        position: fixed;
        display: none;
        background: #343a40;
        color: #fff;
        padding: 6px 10px;
        border-radius: 6px;
        font-size: 0.72rem;
        line-height: 1.4;
        pointer-events: none;
        white-space: nowrap;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .2);
        z-index: 9999;
    }

    /* ===== Jumlah Dokumen (mini stat items) ===== */
    .doc-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(85px, 1fr));
        gap: 0.6rem;
    }
    .doc-item {
        border: 1px solid #f0f0f0;
        background: #f8f9fa;
        border-radius: 8px;
        padding: 0.6rem 0.4rem;
        text-align: center;
    }
    .doc-item .n { font-size: 1.2rem; font-weight: 700; color: #343a40; }
    .doc-item .l { font-size: 0.64rem; font-weight: 600; color: #6c757d; text-transform: uppercase; }

    /* ===== Penangguhan BC 23 mini KPI ===== */
    .tax-item { border-left: 3px solid #dee2e6; }
    .tax-item.t-beamasuk { border-left-color: #007bff; }
    .tax-item.t-bmt      { border-left-color: #fd7e14; }
    .tax-item.t-ppn      { border-left-color: #6f42c1; }
    .tax-item.t-pph      { border-left-color: #dc3545; }

    /* ===== Loading & Error state ===== */
    .btn-load-report {
        background: #0f172a;
        color: #f8fafc;
        border: none;
        padding: .6rem 1.6rem;
        border-radius: 8px;
        font-size: .82rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: background .15s;
        cursor: pointer;
    }
    .btn-load-report:hover { background: #1e293b; color: #fff; }
    .btn-load-report:disabled { opacity: .6; cursor: not-allowed; }

    .report-loading {
        display: none;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 4rem 1.5rem;
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 12px;
    }
    .spinner-ring {
        width: 46px; height: 46px;
        border: 4px solid #e2e8f0;
        border-top-color: #1e3a8a;
        border-radius: 50%;
        animation: spin .8s linear infinite;
        margin-bottom: 1rem;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    .report-loading p { font-size: .8rem; color: #6c757d; margin: 0; font-weight: 600; }
    .report-loading .loading-sub { font-size: .7rem; color: #adb5bd; margin-top: 4px; }

    .report-body { display: none; }
    .fade-in { animation: fadeIn .35s ease; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

    .report-error {
        display: none;
        text-align: center;
        padding: 3rem 1.5rem;
        background: #fff;
        border: 1px solid #f5c2c7;
        border-radius: 12px;
        color: #842029;
    }
    .report-error i { font-size: 1.6rem; margin-bottom: .6rem; display: block; }
</style>
@endsection

@section('content')
<div class="dash-wrap" id="dashWrap">

    <div class="page-header d-flex align-items-center justify-content-between flex-wrap">
        <div>
            <h5 class="dash-title">Dashboard Report BC</h5>
        </div>
        <span id="periodBadge">Memuat...</span>
    </div>
    <div class="report-loading" id="reportLoading">
        <div class="spinner-ring"></div>
        <p>Mengambil data report...</p>
        <div class="loading-sub">Mohon tunggu sebentar</div>
    </div>
    <div class="report-error" id="reportError">
        <i class="fas fa-triangle-exclamation"></i>
        <div>Gagal memuat report. Silakan coba lagi.</div>
        <button type="button" class="btn-load-report mt-3" id="btnRetryReport">
            <i class="fas fa-rotate-right"></i> Coba Lagi
        </button>
    </div>

    <div class="report-body fade-in" id="reportBody">
        <div class="section-label">Nilai Ekspor, Impor &amp; Penjualan Lokal</div>
        <div class="chart-grid mb-3">
            <div class="chart-card">
                <div class="card-heading">
                    <span class="heading-main"><span class="card-icon-badge"><i class="fas fa-coins"></i></span> Nilai per Jenis BC (IDR)</span>
                    <span class="sub" id="periodeLabelSub"></span>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="text-center mb-2" style="font-size:.72rem; font-weight:600; color:#6c757d;" id="ytdLabelTop"></div>
                        <div class="radial-grid" id="dbc-radial-ytd"></div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-center mb-2" style="font-size:.72rem; font-weight:600; color:#6c757d;" id="bulanLabelTop"></div>
                        <div class="radial-grid" id="dbc-radial-bulan"></div>
                    </div>
                </div>
            </div>

            <div class="chart-card" style="position:relative;">
                <div class="card-heading">
                    <span class="heading-main"><span class="card-icon-badge"><i class="fas fa-th-large"></i></span> Proporsi Nilai</span>
                    <span class="sub" id="ytdLabelSub2"></span>
                </div>
                <div id="dbc-bubbles"></div>
            </div>
        </div>

        <div class="section-label">Perbandingan Periode</div>
        <div class="chart-card mb-3">
            <div class="card-heading">
                <span class="heading-main"><span class="card-icon-badge"><i class="fas fa-random"></i></span> <span id="slopeTitleLabel"></span></span>
            </div>
            <div class="slope-legend">
                <span><i class="slope-dot" style="background:#007bff"></i> <span id="legendYtd"></span></span>
                <span><i class="slope-dot" style="background:#f59e0b"></i> <span id="legendBulan"></span></span>
            </div>
            <div id="dbc-slope"></div>
        </div>

        <div class="section-label">Jumlah Dokumen</div>
        <div class="chart-grid cols-2 mb-3">
            <div class="chart-card">
                <div class="card-heading"><span class="heading-main"><span class="card-icon-badge"><i class="fas fa-file-alt"></i></span> <span id="docYtdLabel"></span></span></div>
                <div class="doc-grid" id="docGridYtd"></div>
            </div>

            <div class="chart-card">
                <div class="card-heading"><span class="heading-main"><span class="card-icon-badge"><i class="fas fa-file-alt"></i></span> <span id="docBulanLabel"></span></span></div>
                <div class="doc-grid" id="docGridBulan"></div>
            </div>
        </div>

        <div class="section-label" id="penangguhanLabel"></div>
        <div class="chart-card mb-3">
            <div class="card-heading">
                <span class="heading-main"><span class="card-icon-badge"><i class="fas fa-file-invoice-dollar"></i></span> Rincian Penangguhan</span>
            </div>
            <div class="row text-center">
                <div class="col-6 col-md-3">
                    <div class="doc-item tax-item t-beamasuk"><div class="n" id="taxBeaMasuk"></div><div class="l">Bea Masuk</div></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="doc-item tax-item t-bmt"><div class="n" id="taxBmt"></div><div class="l">BMT</div></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="doc-item tax-item t-ppn"><div class="n" id="taxPpn"></div><div class="l">PPN</div></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="doc-item tax-item t-pph"><div class="n" id="taxPph"></div><div class="l">PPh</div></div>
                </div>
            </div>
        </div>

    </div>

</div>

<div id="chartTooltip" class="chart-tooltip"></div>
@endsection

@section('custom-script')
<script>
    let NILAI_YTD = {};
    let NILAI_BULAN = {};

    const PALETTE = {
        'BC 23': '#007bff',
        'BC 30': '#16a34a',
        'BC 27 Out': '#fd7e14',
        'BC 41': '#6f42c1',
        'BC 25 (Finish Goods)': '#dc3545'
    };

    function formatIdr(v) {
        if (v >= 1e9) return 'Rp ' + (v / 1e9).toFixed(1) + ' M';
        if (v >= 1e6) return 'Rp ' + (v / 1e6).toFixed(1) + ' Jt';
        if (v >= 1e3) return 'Rp ' + (v / 1e3).toFixed(0) + ' Rb';
        return 'Rp ' + v;
    }

    function renderRadial(containerId, dataObj) {
        const el = document.getElementById(containerId);
        const max = Math.max(...Object.values(dataObj), 1);
        el.innerHTML = '';

        Object.entries(dataObj).forEach(([label, value]) => {
            const pct = Math.round((value / max) * 100);
            const color = PALETTE[label] || '#007bff';

            const wrap = document.createElement('div');
            wrap.innerHTML = `
                <div class="radial" style="background: conic-gradient(${color} ${pct * 3.6}deg, #eef1f6 0deg);">
                    <div class="radial-value">${formatIdr(value)}</div>
                </div>
                <div class="radial-label">${label}</div>
            `;
            el.appendChild(wrap);
        });
    }

    function svgEl(tag, attrs = {}, text = null) {
        const el = document.createElementNS('http://www.w3.org/2000/svg', tag);
        Object.entries(attrs).forEach(([k, v]) => el.setAttribute(k, v));
        if (text !== null) el.textContent = text;
        return el;
    }

    const $tooltip = document.getElementById('chartTooltip');
    function showTooltip(html, evt) {
        $tooltip.innerHTML = html;
        $tooltip.style.display = 'block';
        $tooltip.style.left = (evt.clientX + 12) + 'px';
        $tooltip.style.top = (evt.clientY + 12) + 'px';
    }
    function hideTooltip() { $tooltip.style.display = 'none'; }

    function packCircles(items, width, height) {
        const total = items.reduce((s, d) => s + d.value, 0) || 1;
        const areaBudget = width * height * 0.5;

        const circles = items.map(d => {
            const share = d.value / total;
            const area = Math.max(areaBudget * share, 1);
            return { ...d, r: Math.sqrt(area / Math.PI) };
        });

        const minAllowedR = Math.min(width, height) * 0.06;
        circles.forEach(c => { c.r = Math.max(c.r, minAllowedR); });

        const cx0 = width / 2, cy0 = height / 2;
        const placed = [];

        circles.forEach((c, i) => {
            if (i === 0) {
                c.x = cx0; c.y = cy0;
                placed.push(c);
                return;
            }

            let angle = 0;
            let radius = 2;
            const angleStep = 0.28;
            const radiusStep = 1.6;
            let found = false;
            let tx = cx0, ty = cy0;

            for (let step = 0; step < 4000 && !found; step++) {
                tx = cx0 + radius * Math.cos(angle);
                ty = cy0 + radius * Math.sin(angle);

                const overlaps = placed.some(p => {
                    const dx = p.x - tx, dy = p.y - ty;
                    return Math.sqrt(dx * dx + dy * dy) < (p.r + c.r + 3);
                });

                if (!overlaps) { found = true; break; }

                angle += angleStep;
                radius += radiusStep;
            }

            c.x = tx; c.y = ty;
            placed.push(c);
        });

        let maxReach = 0;
        placed.forEach(c => {
            maxReach = Math.max(
                maxReach,
                Math.abs(c.x - cx0) + c.r,
                Math.abs(c.y - cy0) + c.r
            );
        });
        const limit = Math.min(width, height) / 2 - 4;
        const scale = maxReach > limit ? limit / maxReach : 1;
        if (scale < 1) {
            placed.forEach(c => {
                c.x = cx0 + (c.x - cx0) * scale;
                c.y = cy0 + (c.y - cy0) * scale;
                c.r = c.r * scale;
            });
        }

        return placed;
    }

    function hexPoints(cx, cy, r) {
        const pts = [];
        for (let i = 0; i < 6; i++) {
            const angle = (Math.PI / 180) * (60 * i - 90);
            pts.push(`${cx + r * Math.cos(angle)},${cy + r * Math.sin(angle)}`);
        }
        return pts.join(' ');
    }

    function renderBubbles() {
        const container = document.getElementById('dbc-bubbles');
        const width = container.clientWidth || 400;
        const height = 280;

        try {
            const entries = Object.entries(NILAI_YTD)
                .filter(([, v]) => v > 0)
                .map(([label, value]) => ({ label, value }))
                .sort((a, b) => b.value - a.value);

            if (entries.length === 0) {
                container.innerHTML = '<div class="text-center text-muted py-5" style="font-size:0.8rem;">Tidak ada data</div>';
                return;
            }

            const hexes = packCircles(entries, width, height);

            const svg = svgEl('svg', { width, height, viewBox: `0 0 ${width} ${height}` });
            container.innerHTML = '';
            container.appendChild(svg);

            hexes.forEach(c => {
                if (!isFinite(c.x) || !isFinite(c.y) || !isFinite(c.r)) return;

                const g = svgEl('g');

                const hex = svgEl('polygon', {
                    points: hexPoints(c.x, c.y, c.r),
                    fill: PALETTE[c.label] || '#007bff'
                });
                hex.addEventListener('mousemove', (evt) => showTooltip(`<strong>${c.label}</strong><br>${formatIdr(c.value)}`, evt));
                hex.addEventListener('mouseleave', hideTooltip);
                g.appendChild(hex);

                if (c.r >= 24) {
                    g.appendChild(svgEl('text', { x: c.x, y: c.y - 3, class: 'bubble-label' }, c.label));
                    g.appendChild(svgEl('text', { x: c.x, y: c.y + 12, class: 'bubble-value' }, formatIdr(c.value)));
                }

                svg.appendChild(g);
            });
        } catch (err) {
            console.error('renderBubbles error:', err);
            container.innerHTML = '<div class="text-center text-muted py-5" style="font-size:0.8rem;">Gagal menampilkan grafik.</div>';
        }
    }

    function renderSlope() {
        const container = document.getElementById('dbc-slope');
        const labels = Object.keys(NILAI_YTD)
            .sort((a, b) => Math.max(NILAI_BULAN[b], NILAI_YTD[b]) - Math.max(NILAI_BULAN[a], NILAI_YTD[a]));

        const width = container.clientWidth || 500;
        const height = 60 + labels.length * 46;
        const marginLeft = 130, marginRight = 130;
        const trackW = width - marginLeft - marginRight;

        const toLog = v => v <= 0 ? 0 : Math.log10(1 + v);
        const allVals = labels.flatMap(l => [NILAI_YTD[l], NILAI_BULAN[l]]);
        const maxLog = Math.max(...allVals.map(toLog), 0.0001);
        const scaleX = v => marginLeft + (toLog(v) / maxLog) * trackW;

        const svg = svgEl('svg', { width, height, viewBox: `0 0 ${width} ${height}` });
        container.innerHTML = '';
        container.appendChild(svg);

        labels.forEach((label, i) => {
            const y = 30 + i * 46;
            const v1 = NILAI_YTD[label];
            const v2 = NILAI_BULAN[label];

            const x1 = scaleX(v1);
            const x2 = scaleX(v2);
            const closeTogether = Math.abs(x1 - x2) < 55;

            svg.appendChild(svgEl('line', { x1, y1: y, x2, y2: y, stroke: '#cbd5e1', 'stroke-width': 2 }));
            svg.appendChild(svgEl('circle', { cx: x1, cy: y, r: 6, fill: '#007bff' }));
            svg.appendChild(svgEl('circle', { cx: x2, cy: y, r: 6, fill: '#f59e0b' }));

            svg.appendChild(svgEl('text', {
                x: 8, y: y + 4, 'font-size': 12, 'font-weight': 700, fill: '#343a40'
            }, label));

            if (closeTogether) {
                const midX = (x1 + x2) / 2;
                svg.appendChild(svgEl('text', { x: midX, y: y - 12, 'text-anchor': 'middle', 'font-size': 10, fill: '#007bff' }, formatIdr(v1)));
                svg.appendChild(svgEl('text', { x: midX, y: y + 22, 'text-anchor': 'middle', 'font-size': 10, fill: '#f59e0b' }, formatIdr(v2)));
            } else {
                svg.appendChild(svgEl('text', { x: x1, y: y - 12, 'text-anchor': 'middle', 'font-size': 10, fill: '#007bff' }, formatIdr(v1)));
                svg.appendChild(svgEl('text', { x: x2, y: y - 12, 'text-anchor': 'middle', 'font-size': 10, fill: '#f59e0b' }, formatIdr(v2)));
            }
        });
    }

    function renderDocGrid(containerId, dataObj) {
        const el = document.getElementById(containerId);
        el.innerHTML = '';
        Object.entries(dataObj).forEach(([label, count]) => {
            el.insertAdjacentHTML('beforeend', `
                <div class="doc-item">
                    <div class="n">${count}</div>
                    <div class="l">${label}</div>
                </div>
            `);
        });
    }

    function renderSummary(summary) {
        NILAI_YTD = summary.nilai.ytd;
        NILAI_BULAN = summary.nilai.bulan;

        document.getElementById('periodeLabelSub').textContent =
            summary.periode.ytd.label + ' vs ' + summary.periode.bulan.label;
        document.getElementById('ytdLabelTop').textContent = summary.periode.ytd.label;
        document.getElementById('bulanLabelTop').textContent = summary.periode.bulan.label;
        document.getElementById('ytdLabelSub2').textContent = summary.periode.ytd.label;
        document.getElementById('slopeTitleLabel').textContent =
            'Nilai: ' + summary.periode.ytd.label + ' vs ' + summary.periode.bulan.label;
        document.getElementById('legendYtd').textContent = summary.periode.ytd.label;
        document.getElementById('legendBulan').textContent = summary.periode.bulan.label;
        document.getElementById('docYtdLabel').textContent = summary.periode.ytd.label;
        document.getElementById('docBulanLabel').textContent = summary.periode.bulan.label;
        document.getElementById('penangguhanLabel').textContent =
            'Penangguhan BC 23 — ' + summary.periode.ytd.label;

        renderRadial('dbc-radial-ytd', NILAI_YTD);
        renderRadial('dbc-radial-bulan', NILAI_BULAN);
        renderBubbles();
        renderSlope();
        renderDocGrid('docGridYtd', summary.dokumen.ytd);
        renderDocGrid('docGridBulan', summary.dokumen.bulan);

        const p = summary.penangguhan_bc23.ytd;
        document.getElementById('taxBeaMasuk').textContent = 'Rp ' + Number(p.bea_masuk).toLocaleString('id-ID');
        document.getElementById('taxBmt').textContent = 'Rp ' + Number(p.bmt).toLocaleString('id-ID');
        document.getElementById('taxPpn').textContent = 'Rp ' + Number(p.ppn).toLocaleString('id-ID');
        document.getElementById('taxPph').textContent = 'Rp ' + Number(p.pph).toLocaleString('id-ID');
    }

    function setState(state) {
        document.getElementById('reportLoading').style.display = state === 'loading' ? 'flex' : 'none';
        document.getElementById('reportError').style.display = state === 'error' ? 'block' : 'none';
        document.getElementById('reportBody').style.display = state === 'body' ? 'block' : 'none';
    }

    function loadReport() {
        setState('loading');

        fetch("{{ route('dashboard-report-bc.summary') }}", {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(res => {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(json => {
                if (!json.success) throw new Error('Gagal memuat data');
                setState('body');
                renderSummary(json.summary);

                document.getElementById('periodBadge').textContent =
                    'Update: ' + new Date().toLocaleString('id-ID');
            })
            .catch(err => {
                console.error(err);
                setState('error');
            });
    }

    document.addEventListener('DOMContentLoaded', loadReport);
    document.getElementById('btnRetryReport').addEventListener('click', loadReport);
</script>
@endsection
