@extends('layouts.index', ['navbar' => false, 'footer' => false, 'containerFluid' => true])

@section('custom-link')
    <style>
        body {
            background-color: #0e1420;
        }

        .content-wrapper {
            padding-top: 0 !important;
        }

        .content {
            padding: 0 !important;
        }

        .rp-live-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            padding: 16px 26px;
            background-color: #0e1420;
            border-bottom: 1px solid #263043;
        }

        .rp-live-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin: 0;
            color: #fff;
        }

        .rp-live-title i {
            color: #4d8dff;
        }

        .rp-live-meta {
            display: flex;
            align-items: center;
            gap: 22px;
            color: #9fb0c9;
            font-size: 0.85rem;
        }

        .rp-live-clock {
            font-size: 1.05rem;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            color: #fff;
        }

        .rp-live-filter {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .rp-live-filter input[type="date"] {
            background-color: #182338;
            border: 1px solid #33415c;
            color: #fff;
            border-radius: 0.4rem;
            padding: 0.25rem 0.5rem;
            font-size: 0.95rem;
        }

        .rp-live-filter a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            background-color: #1c4fa3;
            color: #fff;
            border-radius: 0.4rem;
            cursor: pointer;
        }

        .rp-live-filter a.disabled {
            pointer-events: none;
            opacity: 0.6;
        }

        /* Same sticky-table approach as the working view: a single real table
                                                                                                                       with position: sticky for the header/footer/frozen columns, sized up
                                                                                                                       for legibility from a distance on a TV/monitor. */
        .rp-scroll {
            height: calc(100vh - 74px);
            overflow: auto;
            border-top: 1px solid #263043;
        }

        table.rp-table {
            border-collapse: separate;
            border-spacing: 0;
            table-layout: fixed;
            width: max-content;
            font-size: 0.95rem;
            font-weight: 600;
            color: #1c2333;
        }

        table.rp-table th,
        table.rp-table td {
            border: 1px solid #d5d9e2;
            padding: 0.5rem 0.65rem;
            white-space: nowrap;
            vertical-align: middle;
            text-align: center;
        }

        table.rp-table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            background: linear-gradient(180deg, #1c4fa3 0%, #123a80 100%);
            color: #fff;
        }

        table.rp-table tfoot th {
            position: sticky;
            bottom: 0;
            z-index: 2;
            font-size: 0.95rem;
            font-weight: 700;
            color: #1c2b4a;
            background-color: #eef2fa;
        }


        /* thead/tbody/tfoot all use four separate cells here (no colspan) so
                                                                                                                       every section has exactly one cell per column — mixing a colspan="4"
                                                                                                                       merged cell into a table-layout:fixed + sticky grid let the browser
                                                                                                                       compute that cell a hair narrower/wider than the four individual
                                                                                                                       frozen columns above it, cutting off / misaligning the footer edge
                                                                                                                       while scrolling. */
        table.rp-table thead th:nth-child(1),
        table.rp-table tbody td:nth-child(1),
        table.rp-table tfoot th:nth-child(1) {
            position: sticky;
            left: 0;
            text-align: left;
        }

        table.rp-table thead th:nth-child(2),
        table.rp-table tbody td:nth-child(2),
        table.rp-table tfoot th:nth-child(2) {
            position: sticky;
            left: 90px;
            text-align: left;
        }

        table.rp-table thead th:nth-child(3),
        table.rp-table tbody td:nth-child(3),
        table.rp-table tfoot th:nth-child(3) {
            position: sticky;
            left: 200px;
            text-align: left;
        }

        table.rp-table thead th:nth-child(4),
        table.rp-table tbody td:nth-child(4),
        table.rp-table tfoot th:nth-child(4) {
            position: sticky;
            left: 310px;
            text-align: left;
            box-shadow: 3px 0 6px -3px rgba(20, 30, 60, 0.35);
        }

        table.rp-table thead th:nth-child(-n+4) {
            z-index: 3;
        }

        table.rp-table tfoot th:nth-child(-n+4) {
            z-index: 3;
            background-color: #eef2fa;
        }

        table.rp-table tbody td:nth-child(-n+4) {
            z-index: 1;
            font-weight: 700;
            color: #17408b;
        }

        table.rp-table tbody tr.stripe-odd td {
            background-color: #f7f9fd;
        }

        table.rp-table tbody tr.stripe-even td {
            background-color: #fff;
        }

        .rp-badge {
            display: inline-block;
            min-width: 2.6rem;
            padding: 0.18rem 0.6rem;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 700;
            color: #1c2333;
            background-color: transparent;
        }

        .rp-badge-good {
            color: #157347;
            background-color: rgba(25, 135, 84, 0.14);
        }

        .rp-badge-bad {
            color: #b02a37;
            background-color: rgba(220, 53, 69, 0.14);
        }

        .rp-badge-neutral {
            color: #1c4fa3;
            background-color: rgba(28, 79, 163, 0.14);
        }
    </style>
@endsection

@section('content')
    <div class="rp-live-header">
        <h1 class="rp-live-title"><i class="fas fa-chart-area"></i> Hourly Output - Live View</h1>
        <div class="rp-live-meta">
            <span class="rp-live-filter">
                <label for="tgl_filter_live">Tanggal:</label>
                <input type="date" id="tgl_filter_live" value="{{ date('Y-m-d') }}">
                <a onclick="reloadLiveData()" id="btn-search-live" title="Cari"><i class="fas fa-search"></i></a>
            </span>
            <span id="last-updated">Last Updated: -</span>
            <span>Refresh dalam: <span id="refreshCountdown">-</span>s</span>
            <span class="rp-live-clock" id="liveClock"></span>
        </div>
    </div>

    <div class="rp-scroll">
        <table class="rp-table">
            <colgroup>
                <col style="width:90px">
                <col style="width:110px">
                <col style="width:110px">
                <col style="width:190px">
                <col style="width:80px">
                <col style="width:60px">
                <col style="width:70px">
                <col style="width:80px">
                <col style="width:80px">
                <col style="width:60px">
                <col style="width:95px">
                <col style="width:70px">
                <col style="width:80px">
                <col style="width:95px">
                <col style="width:95px">
                <col style="width:70px">
                <col style="width:65px">
                <col style="width:65px">
                <col style="width:65px">
                <col style="width:65px">
                <col style="width:65px">
                <col style="width:65px">
                <col style="width:65px">
                <col style="width:65px">
                <col style="width:65px">
                <col style="width:65px">
                <col style="width:65px">
                <col style="width:65px">
                <col style="width:65px">
                <col style="width:75px">
                <col style="width:100px">
                <col style="width:100px">
            </colgroup>
            <thead>
                <tr>
                    <th>Line</th>
                    <th>Chief</th>
                    <th>Leader</th>
                    <th>Style</th>
                    <th>SMV</th>
                    <th>MP</th>
                    <th>Jml Hr</th>
                    <th>Eff H-2</th>
                    <th>Eff H-1</th>
                    <th>JK</th>
                    <th>Eff 100 %</th>
                    <th>Eff</th>
                    <th>Target</th>
                    <th>Target/H</th>
                    <th>Target/J</th>
                    <th>JK Akt</th>
                    <th>1</th>
                    <th>2</th>
                    <th>3</th>
                    <th>4</th>
                    <th>5</th>
                    <th>6</th>
                    <th>7</th>
                    <th>8</th>
                    <th>9</th>
                    <th>10</th>
                    <th>11</th>
                    <th>12</th>
                    <th>13</th>
                    <th>Output</th>
                    <th>Eff Style</th>
                    <th>Eff Line</th>
                </tr>
            </thead>
            <tbody id="rp-tbody">
                <tr>
                    <td colspan="32" class="text-center">Loading...</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th>TOTAL</th>
                    <th></th>
                    <th id="f-mp"></th>
                    <th colspan="4"></th>
                    <th id="f-target100"></th>
                    <th></th>
                    <th id="f-targetoutputeff"></th>
                    <th id="f-settargetperhari"></th>
                    <th id="f-targetperjam"></th>
                    <th></th>
                    <th id="f-ojam1"></th>
                    <th id="f-ojam2"></th>
                    <th id="f-ojam3"></th>
                    <th id="f-ojam4"></th>
                    <th id="f-ojam5"></th>
                    <th id="f-ojam6"></th>
                    <th id="f-ojam7"></th>
                    <th id="f-ojam8"></th>
                    <th id="f-ojam9"></th>
                    <th id="f-ojam10"></th>
                    <th id="f-ojam11"></th>
                    <th id="f-ojam12"></th>
                    <th id="f-ojam13"></th>
                    <th id="f-totoutput" style="color: #1c4fa3;"></th>
                    <th></th>
                    <th id="f-toteff"></th>
                </tr>
            </tfoot>
        </table>
    </div>
@endsection

@section('custom-script')
    <script>
        const esc = (v) => v === null || v === undefined ? '' : v;

        const badge = (value, isGood) =>
            `<span class="rp-badge ${isGood ? 'rp-badge-good' : 'rp-badge-bad'}">${esc(value)}</span>`;

        const plain = (value) => `<span class="rp-badge">${esc(value)}</span>`;

        const qtyBadge = (value, target) => {
            const t = intVal(target);
            if (!t) return plain(value);
            return badge(value, intVal(value) >= t);
        };

        // Shift starts 07:00, and each column is one *completed* clock hour
        // after that: jam ke-1 = 07:00-08:00, jam ke-2 = 08:00-09:00, dst.
        const SHIFT_START_HOUR = 7;

        // How many hour columns are relevant right now, based on the wall
        // clock — e.g. at 13:00 (1 siang), hours 07-08 .. 12-13 have fully
        // elapsed, so this returns 6. Clamped to [0, 13].
        function currentJamKe() {
            const now = new Date();
            const jam = Math.floor(now.getHours() + now.getMinutes() / 60 - SHIFT_START_HOUR);
            return Math.max(0, Math.min(13, jam));
        }

        // Only today's date gets cut off at the current hour — a past date's
        // shift is already fully over, so all 13 columns show as-is straight
        // from the database (0 included, nothing hidden). todayYmd() is
        // defined further down in this same script.
        function hourCutoff() {
            return document.getElementById('tgl_filter_live').value === todayYmd() ? currentJamKe() : 13;
        }

        // Columns beyond the cutoff aren't rendered at all (that hour hasn't
        // happened yet); columns within the cutoff always show the database
        // value as-is, 0 included.
        const hourCell = (value, target, hourIndex, cutoff) => {
            if (hourIndex > cutoff) return '';
            return qtyBadge(value, target);
        };

        const intVal = function(i) {
            return typeof i === 'string' ?
                i.replace(/[\$,]/g, '') * 1 :
                typeof i === 'number' ?
                i : 0;
        };

        function renderRows(rows) {
            let html = '';
            const cutoff = hourCutoff();

            rows.forEach((data, i) => {
                const stripe = i % 2 === 0 ? 'stripe-even' : 'stripe-odd';

                html += `<tr class="${stripe}">` +
                    `<td>${plain(data.sewing_line)}</td>` +
                    `<td>${plain(data.nm_chief)}</td>` +
                    `<td>${plain(data.nm_leader)}</td>` +
                    `<td>${plain(data.styleno_prod)}</td>` +
                    `<td>${plain(data.smv)}</td>` +
                    `<td>${plain(data.man_power)}</td>` +
                    `<td>${plain(data.tot_days)}</td>` +
                    `<td>${badge(data.kemarin_2, data.kemarin_2_angka >= 85)}</td>` +
                    `<td>${badge(data.kemarin_1, data.kemarin_1_angka >= 85)}</td>` +
                    `<td>${plain(data.jam_kerja)}</td>` +
                    `<td>${plain(data.target_100)}</td>` +
                    `<td>${plain(data.target_effy)}</td>` +
                    `<td>${plain(data.target_output_eff)}</td>` +
                    `<td>${plain(data.set_target_perhari)}</td>` +
                    `<td>${plain(data.plan_target_perjam)}</td>` +
                    `<td>${plain(data.jam_kerja_act)}</td>` +
                    `<td>${hourCell(data.o_jam_1, data.plan_target_perjam, 1, cutoff)}</td>` +
                    `<td>${hourCell(data.o_jam_2, data.plan_target_perjam, 2, cutoff)}</td>` +
                    `<td>${hourCell(data.o_jam_3, data.plan_target_perjam, 3, cutoff)}</td>` +
                    `<td>${hourCell(data.o_jam_4, data.plan_target_perjam, 4, cutoff)}</td>` +
                    `<td>${hourCell(data.o_jam_5, data.plan_target_perjam, 5, cutoff)}</td>` +
                    `<td>${hourCell(data.o_jam_6, data.plan_target_perjam, 6, cutoff)}</td>` +
                    `<td>${hourCell(data.o_jam_7, data.plan_target_perjam, 7, cutoff)}</td>` +
                    `<td>${hourCell(data.o_jam_8, data.plan_target_perjam, 8, cutoff)}</td>` +
                    `<td>${hourCell(data.o_jam_9, data.plan_target_perjam, 9, cutoff)}</td>` +
                    `<td>${hourCell(data.o_jam_10, data.plan_target_perjam, 10, cutoff)}</td>` +
                    `<td>${hourCell(data.o_jam_11, data.plan_target_perjam, 11, cutoff)}</td>` +
                    `<td>${hourCell(data.o_jam_12, data.plan_target_perjam, 12, cutoff)}</td>` +
                    `<td>${hourCell(data.o_jam_13, data.plan_target_perjam, 13, cutoff)}</td>` +
                    `<td><span class="rp-badge rp-badge-neutral">${esc(data.tot_output)}</span></td>` +
                    `<td>${badge(data.eff_line, data.eff_line_angka >= 85)}</td>` +
                    `<td>${badge(data.eff_skrg, data.eff_skrg_angka >= 85)}</td>` +
                    `</tr>`;
            });

            document.getElementById('rp-tbody').innerHTML = html ||
                '<tr><td colspan="32" class="text-center">No data</td></tr>';
        }

        function updateFooter(rows, tot_eff_percent, tot_eff) {
            const sum = (key) => rows.reduce((a, d) => a + intVal(d[key]), 0);

            let uniqueManPower = new Map();
            rows.forEach((d) => {
                const line = String(d.sewing_line ?? '').trim();
                if (!uniqueManPower.has(line)) {
                    uniqueManPower.set(line, intVal(d.man_power));
                }
            });
            const totalManPower = Array.from(uniqueManPower.values()).reduce((a, b) => a + b, 0);

            const setBadge = (id, value, colorClass) => {
                document.getElementById(id).innerHTML =
                    `<span class="rp-badge ${colorClass || ''}">${esc(value)}</span>`;
            };

            setBadge('f-mp', totalManPower);
            setBadge('f-target100', sum('target_100'));
            setBadge('f-targetoutputeff', sum('target_output_eff'));
            setBadge('f-settargetperhari', sum('set_target_perhari'));

            const totalTargetPerjam = sum('plan_target_perjam');
            setBadge('f-targetperjam', totalTargetPerjam);

            const cutoff = hourCutoff();
            for (let i = 1; i <= 13; i++) {
                if (i > cutoff) {
                    setBadge('f-ojam' + i, '');
                    continue;
                }
                const ojamTotal = sum('o_jam_' + i);
                const colorClass = totalTargetPerjam ?
                    (ojamTotal < totalTargetPerjam ? 'rp-badge-bad' : 'rp-badge-good') : '';
                setBadge('f-ojam' + i, ojamTotal, colorClass);
            }
            setBadge('f-totoutput', sum('tot_output'), 'rp-badge-neutral');
            setBadge('f-toteff', tot_eff_percent, tot_eff >= 85 ? 'rp-badge-good' : 'rp-badge-bad');
        }

        // Built from the browser's own clock (not a date baked in from the
        // server at page load) so a TV left open past midnight rolls over to
        // the next day's data on its own instead of re-fetching yesterday's
        // date forever.
        function todayYmd() {
            const d = new Date();
            const pad = (n) => String(n).padStart(2, '0');
            return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
        }

        function reloadLiveData() {
            let now = new Date();
            let options = {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            };

            const tglFilter = document.getElementById('tgl_filter_live').value || todayYmd();
            const btnSearch = document.getElementById('btn-search-live');
            btnSearch.classList.add('disabled');
            btnSearch.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            document.getElementById('rp-tbody').innerHTML =
                '<tr><td colspan="32" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>';

            $.ajax({
                url: '{{ route('report-hourly') }}',
                type: 'GET',
                data: {
                    tgl_filter: tglFilter
                },
                success: function(json) {
                    renderRows(json.data);
                    updateFooter(json.data, json.tot_eff_percent, json.tot_eff);
                    document.getElementById('last-updated').innerText =
                        'Last Updated: ' + now.toLocaleString('en-US', options);
                },
                error: function(jqXHR) {
                    console.error(jqXHR);
                },
                complete: function() {
                    btnSearch.classList.remove('disabled');
                    btnSearch.innerHTML = '<i class="fas fa-search"></i>';
                }
            });
        }

        function tickLiveClock() {
            const el = document.getElementById('liveClock');
            if (!el) return;

            const tglFilter = document.getElementById('tgl_filter_live').value;
            const now = new Date();
            const datePart = tglFilter ?
                new Date(tglFilter + 'T00:00:00').toLocaleDateString('id-ID', {
                    dateStyle: 'medium'
                }) :
                now.toLocaleDateString('id-ID', {
                    dateStyle: 'medium'
                });
            const timePart = now.toLocaleTimeString('id-ID', {
                timeStyle: 'medium'
            });

            el.textContent = `${datePart} ${timePart}`;
        }
        tickLiveClock();
        setInterval(tickLiveClock, 1000);

        // Countdown to the next data refresh, based on an absolute deadline
        // reset every time a refresh fires (not a per-tick counter) so a
        // throttled background tab can't make it drift.
        const DATA_REFRESH_SECONDS = 3000;
        let nextDataRefreshDeadline = Date.now() + DATA_REFRESH_SECONDS * 1000;

        function tickRefreshCountdown() {
            const el = document.getElementById('refreshCountdown');
            if (!el) return;
            const remaining = Math.max(0, Math.round((nextDataRefreshDeadline - Date.now()) / 1000));
            el.textContent = remaining;
        }
        setInterval(tickRefreshCountdown, 1000);

        $(document).ready(() => {
            reloadLiveData();
            tickRefreshCountdown();
            setInterval(() => {
                nextDataRefreshDeadline = Date.now() + DATA_REFRESH_SECONDS * 1000;
                reloadLiveData();
            }, DATA_REFRESH_SECONDS * 1000);
        });

        // Full page reload every 30 minutes as a safety net: this page is
        // meant to stay open on a TV/monitor for days at a time, so a
        // periodic hard reload clears any accumulated browser memory/socket
        // state instead of relying solely on the in-page AJAX refresh.
        // Based on an absolute deadline (not a per-tick counter) because
        // browsers throttle setInterval on hidden/background tabs, which
        // would let a tick-based timer drift and delay the reload indefinitely.
        const RELOAD_SECONDS = 30 * 60;
        const reloadDeadline = Date.now() + RELOAD_SECONDS * 1000;

        setInterval(() => {
            if (Date.now() >= reloadDeadline) {
                window.location.reload();
            }
        }, 1000);
    </script>
@endsection
