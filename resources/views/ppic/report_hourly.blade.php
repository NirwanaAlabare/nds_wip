@extends('layouts.index')

@section('custom-link')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        /* Plain HTML table with CSS sticky header/footer + sticky frozen columns.
           No DataTables/FixedColumns here on purpose: FixedColumns clones the table
           to build the frozen panel, and that clone can drift a pixel out of sync
           with the scrolling body/header, producing the misaligned-header seam.
           A single real table with position: sticky avoids the clone entirely. */
        .rp-scroll {
            max-height: 560px;
            overflow: auto;
            border: 1px solid #e3e7f0;
            border-radius: 0.6rem;
        }

        table.rp-table {
            border-collapse: separate;
            border-spacing: 0;
            table-layout: fixed;
            width: max-content;
            font-size: 1.05rem;
            font-weight: 600;
            color: #2b2f3a;
        }

        table.rp-table th,
        table.rp-table td {
            border: 1px solid #d5d9e2;
            padding: 0.55rem 0.7rem;
            white-space: nowrap;
            vertical-align: middle;
            text-align: center;
        }

        table.rp-table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            font-size: 0.9rem;
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
            font-size: 1.35rem;
            font-weight: 700;
            color: #1c2b4a;
            background-color: #eef2fa;
        }

        /* Frozen columns: Line, Chief, Leader, Style (indices 0-3).
           Left offsets are cumulative sums of the preceding column widths below.
           thead/tbody/tfoot all use four separate cells here (no colspan) so
           every section has exactly one cell per column — mixing a colspan="4"
           merged cell into a table-layout:fixed + sticky grid let the browser
           compute that cell a hair narrower/wider than the four individual
           frozen columns above it, which is what cut off / misaligned the
           footer edge while scrolling. */
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
            left: 70px;
            text-align: left;
        }

        table.rp-table thead th:nth-child(3),
        table.rp-table tbody td:nth-child(3),
        table.rp-table tfoot th:nth-child(3) {
            position: sticky;
            left: 160px;
            text-align: left;
        }

        table.rp-table thead th:nth-child(4),
        table.rp-table tbody td:nth-child(4),
        table.rp-table tfoot th:nth-child(4) {
            position: sticky;
            left: 250px;
            text-align: left;
            box-shadow: 3px 0 6px -3px rgba(20, 30, 60, 0.18);
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

        /* Sticky cells need their own opaque background — the parent <tr>'s
           background does not show through a sticky-positioned cell reliably
           while scrolling, so every td gets its stripe color explicitly. */
        table.rp-table tbody tr.stripe-odd td {
            background-color: #f7f9fd;
        }

        table.rp-table tbody tr.stripe-even td {
            background-color: #fff;
        }

        table.rp-table tbody tr:hover td {
            background-color: #e8f0ff !important;
        }

        .rp-badge {
            display: inline-block;
            min-width: 2.6rem;
            padding: 0.18rem 0.6rem;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 700;
        }

        /* Per-hour totals in the footer: under target reads red, meeting/beating it
           reads blue. The tbody per-hour cells use real .rp-badge pills instead
           (same treatment as the Eff/Eff Line columns), so this only targets tfoot. */
        table.rp-table tfoot th.rp-qty-low {
            color: #e5484d;
        }

        table.rp-table tfoot th.rp-qty-good {
            color: #2f6fed;
        }

        .rp-badge-good {
            color: #157347;
            background-color: rgba(25, 135, 84, 0.12);
        }

        .rp-badge-bad {
            color: #b02a37;
            background-color: rgba(220, 53, 69, 0.12);
        }

        .rp-badge-neutral {
            color: #1c4fa3;
            background-color: rgba(28, 79, 163, 0.12);
        }

        /* Keep the footer's own background (matches the rest of tfoot) instead
           of the good/bad tint used for inline badges — only the font color
           should reflect the eff threshold here. `table.rp-table tfoot th`
           sets a fixed text color with higher specificity than .rp-badge-*,
           so it must be overridden explicitly on the #id here too. */
        #f-toteff.rp-badge-good,
        #f-toteff.rp-badge-bad {
            background-color: #eef2fa !important;
        }

        #f-toteff.rp-badge-good {
            color: #157347 !important;
        }

        #f-toteff.rp-badge-bad {
            color: #b02a37 !important;
        }

        .rp-toolbar {
            background-color: #f8f9fc;
            border: 1px solid #eef0f6;
            border-radius: 0.6rem;
            padding: 0.75rem 1rem;
        }

        .rp-toolbar .btn {
            border-radius: 999px;
        }

        .card.card-sb {
            box-shadow: 0 4px 18px rgba(20, 30, 60, 0.07);
            border-radius: 0.75rem;
            overflow: hidden;
        }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title fw-bold mb-0"><i class="fas fa-chart-area"></i> Hourly Output</h5>
            <a href="{{ route('report-hourly-live') }}" target="_blank" class="btn btn-outline-light btn-sm">
                <i class="fas fa-tv"></i> Live View
            </a>
        </div>
        <div class="card-body">
            <div class="rp-toolbar d-flex justify-content-between align-items-end gap-3 mb-3">
                <div class="d-flex align-items-end gap-3">
                    <div>
                        <label class="form-label mb-1"><small><b>Tgl Filter</b></small></label>
                        <input type="date" class="form-control form-control " id="tgl_filter" name="tgl_filter"
                            value="{{ date('Y-m-d') }}">
                    </div>
                    <div>
                        <a onclick="dataTableReload()" id="btn-search" class="btn btn-primary position-relative">
                            <i class="fas fa-search fa-sm"></i>
                        </a>
                    </div>
                    <div>
                        <div id="last-updated" class="text-muted" style="font-size: small;"></div>
                    </div>
                </div>
                <div>
                    <a class="btn btn-success position-relative" data-bs-toggle="modal"
                        data-bs-target="#exportModal">
                        <i class="fas fa-file-excel fa-sm"></i> Export
                    </a>
                </div>
            </div>

            <div class="rp-scroll">
                <table class="rp-table">
                    <colgroup>
                        <col style="width:70px">
                        <col style="width:90px">
                        <col style="width:90px">
                        <col style="width:160px">
                        <col style="width:70px">
                        <col style="width:50px">
                        <col style="width:60px">
                        <col style="width:70px">
                        <col style="width:70px">
                        <col style="width:50px">
                        <col style="width:80px">
                        <col style="width:60px">
                        <col style="width:70px">
                        <col style="width:80px">
                        <col style="width:80px">
                        <col style="width:60px">
                        <col style="width:55px">
                        <col style="width:55px">
                        <col style="width:55px">
                        <col style="width:55px">
                        <col style="width:55px">
                        <col style="width:55px">
                        <col style="width:55px">
                        <col style="width:55px">
                        <col style="width:55px">
                        <col style="width:55px">
                        <col style="width:55px">
                        <col style="width:55px">
                        <col style="width:55px">
                        <col style="width:75px">
                        <col style="width:75px">
                        <col style="width:75px">
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
                            <th>Eff</th>
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
                            <th>Total</th>
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
                            <th id="f-totoutput" style="font-size: 1.35rem; color: #1c4fa3;"></th>
                            <th colspan="2" id="f-toteff" style="font-size: 40px; font-weight: bold;"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Report Hourly --}}
    <div class="modal" id="exportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-sb">
                    <h5 class="modal-title">Export</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Periode</label>
                        <select class="form-select" name="periode" id="periode" onchange="checkPeriod()">
                            <option value="daily">Daily</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>
                    <div id="daily-container">
                        <input type="date" class="form-control" id="tanggal_export">
                    </div>
                    <div id="monthly-container">
                        <div class="d-flex gap-3">
                            <div class="w-50">
                                <select class="form-control select2bs4" id="bulan_export" name="bulan_export[]" multiple>
                                    @foreach ($months as $month)
                                        <option value="{{ $month['angka'] }}">{{ $month['nama'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-50">
                                <select class="form-control select2bs4" id="tahun_export" name="tahun_export">
                                    @foreach ($years as $year)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Close</button>
                    <button type="button" onclick="export_excel_hourly()" class="btn btn-success"><i
                            class="fa fa-file-excel"></i> Export</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <script>
        // Select2 Autofocus
        $(document).on('select2:open', () => {
            document.querySelector('.select2-search__field').focus();
        });

        // Initialize Select2 Elements
        $('.select2').select2();

        // Initialize Select2BS4 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4',
            containerCssClass: 'rounded'
        });
    </script>
    <script>
        const esc = (v) => v === null || v === undefined ? '' : v;

        const badge = (value, isGood) =>
            `<span class="rp-badge ${isGood ? 'rp-badge-good' : 'rp-badge-bad'}">${esc(value)}</span>`;

        const qtyBadge = (value, target) => {
            const t = intVal(target);
            if (!t) return esc(value);
            return badge(value, intVal(value) >= t);
        };

        const intVal = function(i) {
            return typeof i === 'string' ?
                i.replace(/[\$,]/g, '') * 1 :
                typeof i === 'number' ?
                i : 0;
        };

        function renderRows(rows) {
            let html = '';

            rows.forEach((data, i) => {
                const stripe = i % 2 === 0 ? 'stripe-even' : 'stripe-odd';

                html += `<tr class="${stripe}">`
                    + `<td>${esc(data.sewing_line)}</td>`
                    + `<td>${esc(data.nm_chief)}</td>`
                    + `<td>${esc(data.nm_leader)}</td>`
                    + `<td>${esc(data.styleno_prod)}</td>`
                    + `<td>${esc(data.smv)}</td>`
                    + `<td>${esc(data.man_power)}</td>`
                    + `<td>${esc(data.tot_days)}</td>`
                    + `<td>${badge(data.kemarin_2, data.kemarin_2_angka >= 85)}</td>`
                    + `<td>${badge(data.kemarin_1, data.kemarin_1_angka >= 85)}</td>`
                    + `<td>${esc(data.jam_kerja)}</td>`
                    + `<td>${esc(data.target_100)}</td>`
                    + `<td>${esc(data.target_effy)}</td>`
                    + `<td>${esc(data.target_output_eff)}</td>`
                    + `<td>${esc(data.set_target_perhari)}</td>`
                    + `<td>${esc(data.plan_target_perjam)}</td>`
                    + `<td>${esc(data.jam_kerja_act)}</td>`
                    + `<td>${qtyBadge(data.o_jam_1, data.plan_target_perjam)}</td>`
                    + `<td>${qtyBadge(data.o_jam_2, data.plan_target_perjam)}</td>`
                    + `<td>${qtyBadge(data.o_jam_3, data.plan_target_perjam)}</td>`
                    + `<td>${qtyBadge(data.o_jam_4, data.plan_target_perjam)}</td>`
                    + `<td>${qtyBadge(data.o_jam_5, data.plan_target_perjam)}</td>`
                    + `<td>${qtyBadge(data.o_jam_6, data.plan_target_perjam)}</td>`
                    + `<td>${qtyBadge(data.o_jam_7, data.plan_target_perjam)}</td>`
                    + `<td>${qtyBadge(data.o_jam_8, data.plan_target_perjam)}</td>`
                    + `<td>${qtyBadge(data.o_jam_9, data.plan_target_perjam)}</td>`
                    + `<td>${qtyBadge(data.o_jam_10, data.plan_target_perjam)}</td>`
                    + `<td>${qtyBadge(data.o_jam_11, data.plan_target_perjam)}</td>`
                    + `<td>${qtyBadge(data.o_jam_12, data.plan_target_perjam)}</td>`
                    + `<td>${qtyBadge(data.o_jam_13, data.plan_target_perjam)}</td>`
                    + `<td><span class="rp-badge rp-badge-neutral">${esc(data.tot_output)}</span></td>`
                    + `<td>${badge(data.eff_line, data.eff_line_angka >= 85)}</td>`
                    + `<td>${badge(data.eff_skrg, data.eff_skrg_angka >= 85)}</td>`
                    + `</tr>`;
            });

            document.getElementById('rp-tbody').innerHTML = html || '<tr><td colspan="32" class="text-center">No data</td></tr>';
        }

        function updateFooter(rows, tot_eff_percent, tot_eff) {
            const sum = (key) => rows.reduce((a, d) => a + intVal(d[key]), 0);

            // Unique man power per sewing_line (a line can repeat across rows,
            // once per style). Uses a Map (not a plain object) so a line value
            // like "constructor" or "toString" can't collide with a property
            // already on Object.prototype and get skipped by an `in` check.
            let uniqueManPower = new Map();
            rows.forEach((d) => {
                const line = String(d.sewing_line ?? '').trim();
                if (!uniqueManPower.has(line)) {
                    uniqueManPower.set(line, intVal(d.man_power));
                }
            });
            const totalManPower = Array.from(uniqueManPower.values()).reduce((a, b) => a + b, 0);

            document.getElementById('f-mp').textContent = totalManPower;
            document.getElementById('f-target100').textContent = sum('target_100');
            document.getElementById('f-targetoutputeff').textContent = sum('target_output_eff');
            document.getElementById('f-settargetperhari').textContent = sum('set_target_perhari');

            const totalTargetPerjam = sum('plan_target_perjam');
            document.getElementById('f-targetperjam').textContent = totalTargetPerjam;

            for (let i = 1; i <= 13; i++) {
                const ojamTotal = sum('o_jam_' + i);
                const cell = document.getElementById('f-ojam' + i);
                cell.textContent = ojamTotal;
                cell.classList.remove('rp-qty-low', 'rp-qty-good');
                if (totalTargetPerjam) {
                    cell.classList.add(ojamTotal < totalTargetPerjam ? 'rp-qty-low' : 'rp-qty-good');
                }
            }
            document.getElementById('f-totoutput').textContent = sum('tot_output');

            const effCell = document.getElementById('f-toteff');
            effCell.textContent = tot_eff_percent;
            effCell.classList.remove('rp-badge-good', 'rp-badge-bad');
            effCell.classList.add(tot_eff >= 85 ? 'rp-badge-good' : 'rp-badge-bad');
        }

        function dataTableReload() {
            let tgl_filter = $('#tgl_filter').val();

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
            document.getElementById('last-updated').innerText =
                'Last Updated: ' + now.toLocaleString('en-US', options);

            const btnSearch = document.getElementById('btn-search');
            btnSearch.classList.add('disabled');
            btnSearch.innerHTML = '<i class="fas fa-spinner fa-spin fa-sm"></i>';
            document.getElementById('rp-tbody').innerHTML =
                '<tr><td colspan="32" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>';

            $.ajax({
                url: '{{ route('report-hourly') }}',
                type: 'GET',
                data: {
                    tgl_filter: tgl_filter
                },
                success: function(json) {
                    renderRows(json.data);
                    updateFooter(json.data, json.tot_eff_percent, json.tot_eff);
                },
                error: function(jqXHR) {
                    console.error(jqXHR);
                },
                complete: function() {
                    btnSearch.classList.remove('disabled');
                    btnSearch.innerHTML = '<i class="fas fa-search fa-sm"></i>';
                }
            });
        }

        $(document).ready(() => {
            checkPeriod();
            dataTableReload();
            setInterval(dataTableReload, 300000);
        });

        function notif() {
            alert("Maaf, Fitur belum tersedia!");
        }

        function checkPeriod() {
            let period = document.getElementById("periode");
            let dailyContainer = document.getElementById("daily-container");
            let monthlyContainer = document.getElementById("monthly-container");

            if (period && dailyContainer && monthlyContainer) {
                if (period.value == "daily") {
                    dailyContainer.classList.remove("d-none");
                    monthlyContainer.classList.add("d-none");
                }

                if (period.value == "monthly") {
                    dailyContainer.classList.add("d-none");
                    monthlyContainer.classList.remove("d-none");
                }
            }
        }

        async function export_excel_hourly() {
            Swal.fire({
                title: 'Please Wait...',
                html: 'Exporting Data...  <br><br> <b>0</b>s elapsed...',
                didOpen: () => {
                    Swal.showLoading();

                    let estimatedTime = 0;
                    const estimatedTimeElement = Swal.getPopup().querySelector("b");
                    estimatedTimeInterval = setInterval(() => {
                        estimatedTime++;
                        estimatedTimeElement.textContent = estimatedTime;
                    }, 1000);
                },
                allowOutsideClick: false,
            });

            if ($("#periode").val() == "daily") {
                $.ajax({
                    type: "get",
                    url: '{{ route('export-excel-hourly') }}',
                    data: {
                        tgl_filter: $('#tanggal_export').val()
                    },
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function(response) {
                        swal.close();

                        if (response) {
                            Swal.fire({
                                title: 'Data Sudah Di Export!',
                                icon: "success",
                                showConfirmButton: true,
                                allowOutsideClick: false
                            });
                            var blob = new Blob([response]);
                            var link = document.createElement('a');
                            link.href = window.URL.createObjectURL(blob);
                            link.download = "Laporan Hourly " + $('#tgl_filter').val() + ".xlsx";
                            link.click();
                        }
                    },
                    error: function(jqXHR) {
                        console.error(jqXHR);

                        swal.close();
                    }
                });
            } else {
                let months = $('#bulan_export').val();
                let year = $('#tahun_export').val();

                if (months.length > 0) {
                    await downloadMonthlyReports(months, year);
                }
            }
        }

        async function downloadMonthlyReports(months, year) {
            const startTime = performance.now();

            for (const month of months) {
                await new Promise((resolve, reject) => {
                    $.ajax({
                        type: "get",
                        url: '{{ route('export-excel-hourly-monthly') }}',
                        data: {
                            month,
                            year
                        },
                        xhrFields: {
                            responseType: 'blob'
                        },
                        success: function(response) {
                            const blob = new Blob([response]);
                            const link = document.createElement('a');
                            link.href = window.URL.createObjectURL(blob);
                            link.download = "Laporan Hourly " + year + "-" + month + ".xlsx";
                            link.click();
                            resolve(); // 👈 signal that this one is done
                        },
                        error: function(jqXHR) {
                            console.error(jqXHR);
                            reject(jqXHR);
                        }
                    });
                });
            }

            const endTime = performance.now();
            const elapsedMs = endTime - startTime;
            const elapsedTime = formatTimer(elapsedMs);

            swal.close(); // safely called after all exports complete
            Swal.fire({
                title: 'Data Sudah Di Export!',
                icon: "success",
                html: elapsedTime + " elapsed.",
                showConfirmButton: true,
                allowOutsideClick: false
            });
        }

        function export_excel_tracking() {
            let buyer = document.getElementById("cbobuyer").value;
            Swal.fire({
                title: 'Please Wait...',
                html: 'Exporting Data...',
                didOpen: () => {
                    Swal.showLoading()
                },
                allowOutsideClick: false,
            });

            $.ajax({
                type: "get",
                url: '{{ route('export_excel_tracking') }}',
                data: {
                    buyer: buyer
                },
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(response) {
                    {
                        swal.close();
                        Swal.fire({
                            title: 'Data Sudah Di Export!',
                            icon: "success",
                            showConfirmButton: true,
                            allowOutsideClick: false
                        });
                        var blob = new Blob([response]);
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = "Laporan Tracking " + buyer + ".xlsx";
                        link.click();

                    }
                },
            });
        }
    </script>
@endsection
