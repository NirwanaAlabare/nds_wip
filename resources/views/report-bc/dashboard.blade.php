@extends('layouts.index')

@section('custom-link')
<style>
    .dash2 {
        --ink: #0f172a;
        --ink-soft: #475569;
        --muted: #94a3b8;
        --line: #e6eaf0;
        --surface: #ffffff;
        --accent: #2563eb;
        --accent-soft: #eff5ff;
        --green: #0f766e;
        --shadow: 0 1px 2px rgba(15, 23, 42, .04), 0 8px 24px -18px rgba(15, 23, 42, .35);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        color: var(--ink);
    }
    .d2-header {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: 1.1rem 1.25rem;
        margin-bottom: 1.25rem;
        background: var(--surface);
        border: 1px solid var(--line);
        border-left: 4px solid var(--accent);
        border-radius: 12px;
        box-shadow: var(--shadow);
    }
    .d2-eyebrow {
        font-size: .66rem;
        font-weight: 700;
        letter-spacing: .14em;
        text-transform: uppercase;
        color: var(--accent);
    }
    .d2-title {
        font-size: 1.3rem;
        font-weight: 700;
        margin: .15rem 0 .1rem;
        letter-spacing: -.01em;
    }
    .d2-sub {
        font-size: .78rem;
        color: var(--ink-soft);
    }
    .d2-stamp {
        font-size: .7rem;
        font-weight: 600;
        color: var(--ink-soft);
        background: #f6f8fb;
        border: 1px solid var(--line);
        padding: .4rem .7rem;
        border-radius: 999px;
        white-space: nowrap;
    }
    .d2-section {
        display: flex;
        align-items: baseline;
        gap: .6rem;
        margin: 1.6rem 0 .7rem;
    }
    .d2-section h2 {
        font-size: .92rem;
        font-weight: 700;
        margin: 0;
    }
    .d2-section span {
        font-size: .72rem;
        color: var(--muted);
    }
    .d2-card {
        background: var(--surface);
        border: 1px solid var(--line);
        border-radius: 12px;
        padding: 1.1rem 1.2rem;
        box-shadow: var(--shadow);
    }
    .d2-card + .d2-card {
        margin-top: 1rem;
    }
    .d2-card-title {
        font-size: .8rem;
        font-weight: 700;
        margin-bottom: .9rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .5rem;
    }
    .d2-card-title small {
        font-size: .68rem;
        font-weight: 600;
        color: var(--muted);
    }
    .d2-grid {
        display: grid;
        gap: 1rem;
    }
    .d2-grid.kpi {
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
    }
    .d2-grid.two {
        grid-template-columns: 1.35fr 1fr;
    }
    @media (max-width: 900px) {
        .d2-grid.two {
            grid-template-columns: 1fr;
        }
    }
    .kpi-card {
        background: var(--surface);
        border: 1px solid var(--line);
        border-radius: 12px;
        padding: .95rem 1.05rem;
        box-shadow: var(--shadow);
    }
    .kpi-label {
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--muted);
    }
    .kpi-value {
        font-size: 1.45rem;
        font-weight: 700;
        margin-top: .3rem;
        letter-spacing: -.02em;
    }
    .kpi-foot {
        font-size: .7rem;
        color: var(--ink-soft);
        margin-top: .2rem;
    }
    .kpi-card.is-accent {
        border-color: #cfe0ff;
        background: var(--accent-soft);
    }
    .kpi-card.is-accent .kpi-value {
        color: var(--accent);
    }
    .bar-row {
        padding: .6rem 0;
        border-bottom: 1px dashed var(--line);
    }
    .bar-row:last-child {
        border-bottom: 0;
    }
    .bar-head {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: .5rem;
        margin-bottom: .35rem;
    }
    .bar-name {
        font-size: .78rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: .45rem;
    }
    .bar-key {
        width: 8px;
        height: 8px;
        border-radius: 2px;
        flex: none;
    }
    .bar-nums {
        font-size: .72rem;
        color: var(--ink-soft);
        font-variant-numeric: tabular-nums;
    }
    .bar-nums b {
        color: var(--ink);
    }
    .bar-track {
        height: 8px;
        border-radius: 6px;
        background: #f1f4f9;
        overflow: hidden;
    }
    .bar-track + .bar-track {
        margin-top: 5px;
    }
    .bar-fill {
        height: 100%;
        border-radius: 6px;
        width: 0;
        transition: width .7s cubic-bezier(.22,.7,.3,1);
    }
    .bar-legend {
        display: flex;
        gap: 1rem;
        font-size: .7rem;
        color: var(--ink-soft);
        margin-bottom: .8rem;
    }
    .bar-legend i {
        display: inline-block;
        width: 18px;
        height: 6px;
        border-radius: 3px;
        margin-right: .35rem;
        vertical-align: middle;
    }
    .donut-wrap {
        display: flex;
        align-items: center;
        gap: 1.2rem;
        flex-wrap: wrap;
        justify-content: center;
    }
    .donut-svg {
        position: relative;
        flex: none;
    }
    .donut-center {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        pointer-events: none;
    }
    .donut-center .l {
        font-size: .62rem;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: var(--muted);
        font-weight: 700;
    }
    .donut-center .v {
        font-size: .95rem;
        font-weight: 700;
    }
    .donut-seg {
        transition: opacity .18s ease;
        cursor: pointer;
    }
    .donut-svg.dim .donut-seg {
        opacity: .28;
    }
    .donut-svg.dim .donut-seg.on {
        opacity: 1;
    }
    .dl {
        flex: 1 1 190px;
        min-width: 180px;
    }
    .dl-item {
        display: flex;
        align-items: center;
        gap: .55rem;
        padding: .35rem .4rem;
        border-radius: 7px;
        font-size: .74rem;
    }
    .dl-item.on {
        background: #f6f8fb;
    }
    .dl-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        flex: none;
    }
    .dl-name {
        flex: 1;
        font-weight: 600;
    }
    .dl-val {
        color: var(--ink-soft);
        font-variant-numeric: tabular-nums;
    }
    .dl-pct {
        width: 46px;
        text-align: right;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
    }
    .d2-table {
        width: 100%;
        border-collapse: collapse;
        font-size: .78rem;
    }
    .d2-table th {
        text-align: left;
        font-size: .66rem;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--muted);
        font-weight: 700;
        padding: .5rem .6rem;
        border-bottom: 1px solid var(--line);
    }
    .d2-table th.num, .d2-table td.num {
        text-align: right;
        font-variant-numeric: tabular-nums;
    }
    .d2-table td {
        padding: .55rem .6rem;
        border-bottom: 1px solid #f2f5f9;
    }
    .d2-table tr:last-child td {
        border-bottom: 0;
    }
    .d2-table tbody tr:hover {
        background: #fafbfd;
    }
    .d2-table td.name {
        font-weight: 600;
    }
    .d2-table td .bar-key {
        display: inline-block;
        margin-right: .45rem;
    }
    .d2-table tfoot td {
        padding: .55rem .6rem;
        border-top: 1px solid var(--line);
        font-weight: 700;
    }
    .tax-grid {
        display: grid;
        gap: .8rem;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    }
    .tax-item {
        border: 1px solid var(--line);
        border-radius: 10px;
        padding: .75rem .85rem;
        background: #fbfcfe;
    }
    .tax-item .l {
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: var(--muted);
    }
    .tax-item .v {
        font-size: .95rem;
        font-weight: 700;
        margin-top: .25rem;
        font-variant-numeric: tabular-nums;
    }
    .tax-item.total {
        background: var(--accent-soft);
        border-color: #cfe0ff;
    }
    .tax-item.total .v {
        color: var(--accent);
    }
    .d2-tooltip {
        position: fixed;
        z-index: 1080;
        display: none;
        pointer-events: none;
        background: #0f172a;
        color: #fff;
        font-size: .72rem;
        line-height: 1.35;
        padding: .45rem .6rem;
        border-radius: 7px;
        box-shadow: 0 8px 24px rgba(15,23,42,.25);
    }
    .d2-loading {
        display: none;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 4rem 1rem;
        background: var(--surface);
        border: 1px solid var(--line);
        border-radius: 12px;
    }
    .d2-spinner {
        width: 38px;
        height: 38px;
        border: 3px solid #e6eaf0;
        border-top-color: var(--accent);
        border-radius: 50%;
        animation: d2spin .8s linear infinite;
        margin-bottom: .9rem;
    }
    @keyframes d2spin {
        to {
            transform: rotate(360deg);
        }
    }
    .d2-loading p {
        font-size: .78rem;
        font-weight: 600;
        color: var(--ink-soft);
        margin: 0;
    }
    .d2-error {
        display: none;
        text-align: center;
        padding: 3rem 1.5rem;
        background: var(--surface);
        border: 1px solid #f3c9cd;
        border-radius: 12px;
        color: #9f1239;
    }
    .d2-error i {
        font-size: 1.5rem;
        display: block;
        margin-bottom: .5rem;
    }
    .btn-load-report {
        border: 0;
        background: var(--accent);
        color: #fff;
        font-size: .76rem;
        font-weight: 600;
        padding: .5rem .95rem;
        border-radius: 8px;
        margin-top: .8rem;
        cursor: pointer;
    }
    .d2-body {
        display: none;
    }
    .fade-in {
        animation: d2fade .35s ease;
    }
    @keyframes d2fade {
        from {
            opacity: 0;
            transform: translateY(6px);
        }
        to {
            opacity: 1;
            transform: none;
        }
    }
</style>
@endsection

@section('content')
<div class="dash2">

    <div class="d2-header">
        <div>
            <div class="d2-eyebrow">Bea Cukai</div>
            <h1 class="d2-title">Dashboard Report BC</h1>
            <div class="d2-sub" id="periodeLabelSub">Memuat...</div>
        </div>
        <span class="d2-stamp" id="periodBadge">Memuat...</span>
    </div>

    <div class="d2-loading" id="reportLoading">
        <div class="d2-spinner"></div>
        <p>Mengambil data report...</p>
    </div>

    <div class="d2-error" id="reportError">
        <i class="fas fa-triangle-exclamation"></i>
        <p>Gagal memuat report. Silakan coba lagi.</p>
        <button class="btn-load-report" id="btnRetryReport"><i class="fas fa-rotate-right"></i> Coba Lagi</button>
    </div>

    <div class="d2-body" id="reportBody">

        <div class="d2-grid kpi">
            <div class="kpi-card is-accent">
                <div class="kpi-label">Total Nilai</div>
                <div class="kpi-value" id="kpiYtdValue">-</div>
                <div class="kpi-foot" id="kpiYtdLabel">-</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Total Nilai</div>
                <div class="kpi-value" id="kpiBulanValue">-</div>
                <div class="kpi-foot" id="kpiBulanLabel">-</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Total Dokumen</div>
                <div class="kpi-value" id="kpiDocValue">-</div>
                <div class="kpi-foot" id="kpiDocLabel">-</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Penangguhan BC 23</div>
                <div class="kpi-value" id="kpiTaxValue">-</div>
                <div class="kpi-foot">Bea Masuk + BMT + PPN + PPh</div>
            </div>
        </div>

        <div class="d2-section">
            <h2>Nilai Ekspor, Impor &amp; Penjualan Lokal</h2>
            <span id="periodeCompareLabel"></span>
        </div>

        <div class="d2-grid two">
            <div class="d2-card">
                <div class="d2-card-title">Nilai per Jenis BC <small>skala terhadap nilai tertinggi</small></div>
                <div class="bar-legend">
                    <span><i style="background:#2563eb"></i><span id="legendYtd"></span></span>
                    <span><i style="background:#94a3b8"></i><span id="legendBulan"></span></span>
                </div>
                <div id="barList"></div>
            </div>

            <div class="d2-card">
                <div class="d2-card-title">Proporsi Nilai <small id="donutPeriodLabel"></small></div>
                <div class="donut-wrap" id="dbc-donut"></div>
            </div>
        </div>

        <div class="d2-section"><h2>Jumlah Dokumen</h2></div>

        <div class="d2-card">
            <table class="d2-table">
                <thead>
                    <tr>
                        <th>Jenis BC</th>
                        <th class="num" id="thDocYtd">YTD</th>
                        <th class="num" id="thDocBulan">Bulan</th>
                    </tr>
                </thead>
                <tbody id="docTableBody"></tbody>
                <tfoot>
                    <tr>
                        <td>Total</td>
                        <td class="num" id="docTotalYtd">-</td>
                        <td class="num" id="docTotalBulan">-</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="d2-section"><h2 id="penangguhanLabel">Penangguhan BC 23</h2></div>

        <div class="d2-card">
            <div class="tax-grid">
                <div class="tax-item"><div class="l">Bea Masuk</div><div class="v" id="taxBeaMasuk">-</div></div>
                <div class="tax-item"><div class="l">BMT</div><div class="v" id="taxBmt">-</div></div>
                <div class="tax-item"><div class="l">PPN</div><div class="v" id="taxPpn">-</div></div>
                <div class="tax-item"><div class="l">PPh</div><div class="v" id="taxPph">-</div></div>
                <div class="tax-item total"><div class="l">Total</div><div class="v" id="taxTotal">-</div></div>
            </div>
        </div>
    </div>

    <div class="d2-tooltip" id="chartTooltip"></div>
</div>
@endsection

@section('custom-script')
<script>
    let NILAI_YTD = {};
    let NILAI_BULAN = {};

    const PALETTE = {
        'BC 23': '#2563eb',
        'BC 27 In': '#0ea5e9',
        'BC 27 Out': '#f97316',
        'BC 30': '#16a34a',
        'BC 41': '#7c3aed',
        'BC 25 FG': '#e11d48',
        'BC 25 Scrap': '#f59e0b'
    };

    function formatIdr(v) {
        v = Number(v) || 0;
        if (v >= 1e12) return 'Rp ' + (v / 1e12).toFixed(1) + ' T';
        if (v >= 1e9) return 'Rp ' + (v / 1e9).toFixed(1) + ' M';
        if (v >= 1e6) return 'Rp ' + (v / 1e6).toFixed(1) + ' Jt';
        if (v >= 1e3) return 'Rp ' + (v / 1e3).toFixed(0) + ' Rb';
        return 'Rp ' + Math.round(v).toLocaleString('id-ID');
    }
    function rupiah(v) { return 'Rp ' + Number(v || 0).toLocaleString('id-ID'); }
    function sum(obj) { return Object.values(obj || {}).reduce((s, v) => s + (Number(v) || 0), 0); }

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

    function renderBarList(labelYtd, labelBulan) {
        const el = document.getElementById('barList');
        el.innerHTML = '';

        const labels = Object.keys(NILAI_YTD)
            .sort((a, b) => (Number(NILAI_YTD[b]) || 0) - (Number(NILAI_YTD[a]) || 0));
        const max = Math.max(...labels.flatMap(l => [Number(NILAI_YTD[l]) || 0, Number(NILAI_BULAN[l]) || 0]), 1);

        labels.forEach(label => {
            const v1 = Number(NILAI_YTD[label]) || 0;
            const v2 = Number(NILAI_BULAN[label]) || 0;
            const color = PALETTE[label] || '#2563eb';

            const row = document.createElement('div');
            row.className = 'bar-row';
            row.innerHTML = `
                <div class="bar-head">
                    <span class="bar-name"><i class="bar-key" style="background:${color}"></i>${label}</span>
                    <span class="bar-nums"><b>${formatIdr(v1)}</b> · ${formatIdr(v2)}</span>
                </div>
                <div class="bar-track"><div class="bar-fill" data-w="${(v1 / max) * 100}" style="background:${color}"></div></div>
                <div class="bar-track"><div class="bar-fill" data-w="${(v2 / max) * 100}" style="background:#cbd5e1"></div></div>
            `;
            row.addEventListener('mousemove', (evt) => showTooltip(
                `<strong>${label}</strong><br>${labelYtd}: ${formatIdr(v1)}<br>${labelBulan}: ${formatIdr(v2)}`, evt
            ));
            row.addEventListener('mouseleave', hideTooltip);
            el.appendChild(row);
        });

        requestAnimationFrame(() => {
            el.querySelectorAll('.bar-fill').forEach(f => { f.style.width = f.dataset.w + '%'; });
        });
    }

    function renderDonut() {
        const container = document.getElementById('dbc-donut');
        try {
            const entries = Object.entries(NILAI_YTD)
                .filter(([, v]) => Number(v) > 0)
                .map(([label, value]) => ({ label, value: Number(value) }))
                .sort((a, b) => b.value - a.value);

            if (!entries.length) {
                container.innerHTML = '<div class="text-center text-muted py-5" style="font-size:.78rem;">Tidak ada data</div>';
                return;
            }

            const total = entries.reduce((s, d) => s + d.value, 0);
            const size = 190, cx = size / 2, cy = size / 2, strokeW = 26;
            const r = (size - strokeW) / 2;
            const circ = 2 * Math.PI * r;

            const svg = svgEl('svg', { width: size, height: size, viewBox: `0 0 ${size} ${size}` });
            svg.appendChild(svgEl('circle', { cx, cy, r, fill: 'none', stroke: '#f1f4f9', 'stroke-width': strokeW }));

            let offset = 0;
            const gapDeg = 2;
            entries.forEach(d => {
                const share = d.value / total;
                const gapLen = (gapDeg / 360) * circ;
                const segLen = Math.max(share * circ - gapLen, 0);
                const seg = svgEl('circle', {
                    cx, cy, r, fill: 'none',
                    stroke: PALETTE[d.label] || '#2563eb',
                    'stroke-width': strokeW,
                    'stroke-linecap': 'butt',
                    'stroke-dasharray': `${segLen} ${circ - segLen}`,
                    'stroke-dashoffset': -offset,
                    transform: `rotate(-90 ${cx} ${cy})`,
                    class: 'donut-seg', 'data-label': d.label
                });
                seg.addEventListener('mousemove', evt => showTooltip(
                    `<strong>${d.label}</strong><br>${formatIdr(d.value)} (${(share * 100).toFixed(1)}%)`, evt));
                seg.addEventListener('mouseenter', () => setActive(d.label));
                seg.addEventListener('mouseleave', () => { hideTooltip(); clearActive(); });
                svg.appendChild(seg);
                offset += share * circ;
            });

            const wrap = document.createElement('div');
            wrap.className = 'donut-svg';
            wrap.appendChild(svg);
            wrap.insertAdjacentHTML('beforeend', `
                <div class="donut-center">
                    <div class="l">Total</div>
                    <div class="v">${formatIdr(total)}</div>
                </div>`);

            const legend = document.createElement('div');
            legend.className = 'dl';
            entries.forEach(d => {
                const pct = ((d.value / total) * 100).toFixed(1);
                const item = document.createElement('div');
                item.className = 'dl-item';
                item.dataset.label = d.label;
                item.innerHTML = `
                    <span class="dl-dot" style="background:${PALETTE[d.label] || '#2563eb'}"></span>
                    <span class="dl-name">${d.label}</span>
                    <span class="dl-val">${formatIdr(d.value)}</span>
                    <span class="dl-pct">${pct}%</span>`;
                item.addEventListener('mouseenter', () => setActive(d.label));
                item.addEventListener('mouseleave', clearActive);
                legend.appendChild(item);
            });

            container.innerHTML = '';
            container.appendChild(wrap);
            container.appendChild(legend);

            function setActive(label) {
                wrap.classList.add('dim');
                svg.querySelectorAll('.donut-seg').forEach(el => el.classList.toggle('on', el.dataset.label === label));
                legend.querySelectorAll('.dl-item').forEach(el => el.classList.toggle('on', el.dataset.label === label));
            }
            function clearActive() {
                wrap.classList.remove('dim');
                svg.querySelectorAll('.donut-seg').forEach(el => el.classList.remove('on'));
                legend.querySelectorAll('.dl-item').forEach(el => el.classList.remove('on'));
            }
        } catch (err) {
            console.error('renderDonut error:', err);
            container.innerHTML = '<div class="text-center text-muted py-5" style="font-size:.78rem;">Gagal menampilkan grafik.</div>';
        }
    }

    function renderDocTable(docYtd, docBulan) {
        const body = document.getElementById('docTableBody');
        const labels = Array.from(new Set([...Object.keys(docYtd || {}), ...Object.keys(docBulan || {})]));
        body.innerHTML = '';
        labels.forEach(label => {
            const a = Number((docYtd || {})[label]) || 0;
            const b = Number((docBulan || {})[label]) || 0;
            body.insertAdjacentHTML('beforeend', `
                <tr>
                    <td class="name"><i class="bar-key" style="background:${PALETTE[label] || '#2563eb'}"></i>${label}</td>
                    <td class="num">${a.toLocaleString('id-ID')}</td>
                    <td class="num">${b.toLocaleString('id-ID')}</td>
                </tr>`);
        });
        document.getElementById('docTotalYtd').textContent = sum(docYtd).toLocaleString('id-ID');
        document.getElementById('docTotalBulan').textContent = sum(docBulan).toLocaleString('id-ID');
    }

    function renderSummary(summary) {
        NILAI_YTD = summary.nilai.ytd;
        NILAI_BULAN = summary.nilai.bulan;

        const labelYtd = summary.periode.ytd.label;
        const labelBulan = summary.periode.bulan.label;

        document.getElementById('periodeLabelSub').textContent = labelYtd + ' vs ' + labelBulan;
        document.getElementById('periodeCompareLabel').textContent = labelYtd + ' vs ' + labelBulan;
        document.getElementById('legendYtd').textContent = labelYtd;
        document.getElementById('legendBulan').textContent = labelBulan;
        document.getElementById('donutPeriodLabel').textContent = labelYtd;
        document.getElementById('kpiYtdLabel').textContent = labelYtd;
        document.getElementById('kpiBulanLabel').textContent = labelBulan;
        document.getElementById('kpiDocLabel').textContent = labelYtd;
        document.getElementById('thDocYtd').textContent = labelYtd;
        document.getElementById('thDocBulan').textContent = labelBulan;
        document.getElementById('penangguhanLabel').textContent = 'Penangguhan BC 23 — ' + labelYtd;

        document.getElementById('kpiYtdValue').textContent = formatIdr(sum(NILAI_YTD));
        document.getElementById('kpiBulanValue').textContent = formatIdr(sum(NILAI_BULAN));
        document.getElementById('kpiDocValue').textContent = sum(summary.dokumen.ytd).toLocaleString('id-ID');

        renderBarList(labelYtd, labelBulan);
        renderDonut();
        renderDocTable(summary.dokumen.ytd, summary.dokumen.bulan);

        const p = summary.penangguhan_bc23.ytd;
        document.getElementById('taxBeaMasuk').textContent = rupiah(p.bea_masuk);
        document.getElementById('taxBmt').textContent = rupiah(p.bmt);
        document.getElementById('taxPpn').textContent = rupiah(p.ppn);
        document.getElementById('taxPph').textContent = rupiah(p.pph);

        const totalTax = Number(p.bea_masuk || 0) + Number(p.bmt || 0) + Number(p.ppn || 0) + Number(p.pph || 0);
        document.getElementById('taxTotal').textContent = rupiah(totalTax);
        document.getElementById('kpiTaxValue').textContent = formatIdr(totalTax);
    }

    function setState(state) {
        const body = document.getElementById('reportBody');
        document.getElementById('reportLoading').style.display = state === 'loading' ? 'flex' : 'none';
        document.getElementById('reportError').style.display = state === 'error' ? 'block' : 'none';
        body.style.display = state === 'body' ? 'block' : 'none';
        if (state === 'body') { body.classList.remove('fade-in'); void body.offsetWidth; body.classList.add('fade-in'); }
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
