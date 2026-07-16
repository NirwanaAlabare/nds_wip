@extends('layouts.index', ['navbar' => false, 'footer' => false, 'containerFluid' => true])

@section('custom-link')
    <style>
        body {
            background-color: #f4f6f9;
        }

        .content-wrapper {
            padding-top: 0 !important;
        }

        .content {
            padding: 0 !important;
        }

        .line-map-live-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            padding: 14px 20px;
            background-color: #fff;
            border-bottom: 1px solid #dee2e6;
        }

        .line-map-live-title {
            font-size: 1.4rem;
            font-weight: 700;
            margin: 0;
        }

        .line-map-live-clock {
            font-size: 1.1rem;
            font-weight: 600;
            font-variant-numeric: tabular-nums;
        }

        .line-map-calendar-wrapper {
            overflow: auto;
            max-height: calc(100vh - 70px);
            max-width: 100%;
            border-top: 1px solid #dee2e6;
            border-left: 1px solid #dee2e6;
        }

        .line-map-table {
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 0 !important;
            width: max-content !important;
        }

        .line-map-table th,
        .line-map-table td {
            white-space: nowrap;
            vertical-align: middle;
            border-right: 1px solid #dee2e6;
            border-bottom: 1px solid #dee2e6;
        }

        .line-map-table tbody td {
            vertical-align: top;
        }

        .line-map-line-col {
            min-width: 160px;
            position: sticky;
            left: 0;
            z-index: 2;
            background-color: #fff !important;
            box-shadow: 2px 0 4px -2px rgba(0, 0, 0, .15);
            white-space: normal !important;
            vertical-align: top !important;
        }

        .line-map-history-product-group {
            font-size: .7rem;
            color: #6c757d;
            white-space: normal;
        }

        .line-map-table thead th {
            position: sticky;
            top: 0;
            z-index: 3;
            background-color: #fff !important;
        }

        .line-map-table thead th.line-map-line-col {
            z-index: 4;
        }

        .line-map-table thead th:not(.line-map-line-col) {
            text-align: center;
            width: 1%;
        }

        .line-map-date-day {
            font-size: .7rem;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
        }

        .line-map-date-num {
            font-size: .8rem;
        }

        .line-map-table th.is-sunday .line-map-date-day,
        .line-map-table th.is-sunday .line-map-date-num {
            color: #dc3545;
        }

        .line-map-table th.is-today,
        .line-map-table td.is-today {
            background-color: #eaf2ff !important;
        }

        .line-map-table th.is-today {
            box-shadow: inset 0 -2px 0 0 #0d6efd;
        }

        .line-map-table td:not(.line-map-line-col) {
            text-align: center;
            width: 1%;
            padding: 3px 4px;
        }

        .line-map-plan-cell {
            position: relative;
        }

        .line-map-plan-cell::before {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            top: 10px;
            height: 4px;
            background-color: var(--plan-line-color, #6f42c1);
            opacity: .45;
            z-index: 0;
        }

        .line-map-plan-start::before {
            left: 50%;
            border-radius: 4px 0 0 4px;
        }

        .line-map-plan-end::before {
            right: 50%;
            border-radius: 0 4px 4px 0;
        }

        .line-map-plan-start.line-map-plan-end::before {
            left: 35%;
            right: 35%;
            border-radius: 4px;
        }

        .line-map-cell-stack {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 4px;
            position: relative;
            z-index: 1;
        }

        .line-map-box {
            min-width: 100px;
            max-width: 170px;
            border-radius: 10px;
            padding: 4px 8px;
            text-align: left;
            font-size: 10.5px;
        }

        .line-map-box-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
            margin-bottom: 2px;
        }

        .line-map-box-header .box-buyer {
            overflow: hidden;
            text-overflow: ellipsis;
            min-width: 0;
        }

        .line-map-box-row {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 8px;
            white-space: nowrap;
            line-height: 1.5;
        }

        .line-map-box-row .row-label {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            min-width: 0;
        }

        .line-map-box-row .row-qty {
            font-weight: 700;
            flex: 0 0 auto;
        }

        .line-map-box-plan {
            background-color: var(--dot-color, #6f42c1);
            color: #fff;
            cursor: default;
            user-select: none;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .15);
        }

        .line-map-box-plan .row-qty {
            color: #fff;
        }

        .line-map-box-actual {
            background-color: #fff;
            border: 2px solid #198754;
        }

        .line-map-box-actual .line-map-box-header {
            color: #146c43;
        }

        .line-map-box-actual .row-qty {
            color: #198754;
        }

        .line-map-box-actual-detail {
            cursor: pointer;
        }

        .line-map-box-actual-detail:hover {
            text-decoration: underline;
        }
    </style>
@endsection

@section('content')
    <div class="line-map-live-header">
        <h1 class="line-map-live-title"><i class="fas fa-map-marker-alt"></i> PPIC Line Map - Live View</h1>
        <div class="d-flex align-items-center gap-3">
            <small class="text-muted">Last Update:
                {{ $lastUpdated ? date('d-m-Y H:i:s', strtotime($lastUpdated)) : '-' }}</small>
            <small class="text-muted">Refresh dalam: <span id="refreshCountdown">-</span></small>
            <span class="line-map-live-clock" id="liveClock"></span>
        </div>
    </div>

    <div class="line-map-calendar-wrapper">
        <table class="table table-sm line-map-table">
            <thead>
                <tr>
                    <th class="line-map-line-col">Line</th>
                    @foreach ($calendarDates as $date)
                        <th @class([
                            'is-sunday' => strtoupper($date->status_prod) === 'LIBUR',
                            'is-today' => $date->tanggal === date('Y-m-d'),
                        ])>
                            <div class="line-map-date-day">{{ ucfirst(strtolower($date->nama_hari)) }}</div>
                            <div class="line-map-date-num">{{ date('d M', strtotime($date->tanggal)) }}</div>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($line as $ln)
                    <tr>
                        <td class="line-map-line-col">
                            <div class="fw-bold">{{ $ln->FullName ?? $ln->username }}</div>
                            @foreach (($productGroupByLine[$ln->username] ?? collect()) as $pg)
                                <div class="line-map-history-product-group">{{ $pg->product_group }}
                                    <span class="text-muted">({{ number_format($pg->tot_qty, 0, ',', '.') }})</span>
                                </div>
                            @endforeach
                        </td>
                        @foreach ($calendarDates as $date)
                            @php
                                $activeEntry = ($lineMapByLine[$ln->username] ?? collect())->first(
                                    fn($e) => $date->tanggal >= $e->tgl_start && $date->tanggal <= $e->tgl_end,
                                );
                                $planQty = $activeEntry->daily_plan[$date->tanggal] ?? null;
                                $effPct = $activeEntry->daily_efficiency[$date->tanggal] ?? null;
                                $actualEntries = $actualByLineDate[$ln->username][$date->tanggal] ?? collect();
                                $hasPlan = $activeEntry && $planQty !== null;
                                $isWithinPlanRange = (bool) $activeEntry;
                                $isPlanStart = $isWithinPlanRange && $date->tanggal === $activeEntry->tgl_start;
                                $isPlanEnd = $isWithinPlanRange && $date->tanggal === $activeEntry->tgl_end;
                                $planCellClasses = collect([
                                    $isWithinPlanRange ? 'line-map-plan-cell' : null,
                                    $isPlanStart ? 'line-map-plan-start' : null,
                                    $isPlanEnd ? 'line-map-plan-end' : null,
                                    $date->tanggal === date('Y-m-d') ? 'is-today' : null,
                                ])
                                    ->filter()
                                    ->implode(' ');
                                $planTitle = $hasPlan
                                    ? 'Range: ' .
                                        date('d M Y', strtotime($activeEntry->tgl_start)) .
                                        ' - ' .
                                        date('d M Y', strtotime($activeEntry->tgl_end)) .
                                        ($effPct !== null
                                            ? ' | Efisiensi: ' .
                                                rtrim(rtrim(number_format($effPct, 1), '0'), '.') .
                                                '%'
                                            : '')
                                    : null;
                            @endphp
                            <td class="{{ $planCellClasses }}"
                                @if ($isWithinPlanRange) style="--plan-line-color: {{ $activeEntry->style_color }};" @endif>
                                @if ($hasPlan || $actualEntries->isNotEmpty())
                                    @php
                                        $planColor = $activeEntry->style_color ?? '#6f42c1';
                                    @endphp
                                    <div class="line-map-cell-stack">
                                        @if ($hasPlan)
                                            <div class="line-map-box line-map-box-plan"
                                                style="--dot-color: {{ $planColor }};" title="{{ $planTitle }}">
                                                <div class="line-map-box-header">
                                                    <span class="box-buyer">{{ $activeEntry->buyer ?: '-' }}</span>
                                                    <span>Plan</span>
                                                </div>
                                                <div class="line-map-box-row">
                                                    <span class="row-label">{{ $activeEntry->style }}</span>
                                                    <span
                                                        class="row-qty">{{ number_format($planQty, 0, ',', '.') }}</span>
                                                </div>
                                                @if ($activeEntry->product_group)
                                                    <div class="line-map-box-row">
                                                        <span
                                                            class="row-label fst-italic">{{ $activeEntry->product_group }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                        @if ($actualEntries->isNotEmpty())
                                            <div class="line-map-box line-map-box-actual">
                                                <div class="line-map-box-header">
                                                    <span>Aktual</span>
                                                </div>
                                                @foreach ($actualEntries as $actual)
                                                    <div class="line-map-box-row line-map-box-actual-detail"
                                                        role="button"
                                                        onclick='showWsBreakdown(@json($actual->styleno), @json($actual->ws_breakdown))'>
                                                        <span class="row-label">{{ $actual->styleno ?: '-' }}</span>
                                                        <span
                                                            class="row-qty">{{ number_format($actual->tot_rfts, 0, ',', '.') }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td class="line-map-line-col text-muted">Belum ada data</td>
                        @foreach ($calendarDates as $date)
                            <td></td>
                        @endforeach
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script>
        function showWsBreakdown(styleno, wsBreakdown) {
            const rows = (wsBreakdown || []).map(row => `
                <tr>
                    <td class="text-left">${row.ws || '-'}</td>
                    <td class="text-right">${Number(row.tot_rfts || 0).toLocaleString('id-ID')}</td>
                </tr>
            `).join('');

            const total = (wsBreakdown || []).reduce((sum, row) => sum + Number(row.tot_rfts || 0), 0);

            Swal.fire({
                icon: 'info',
                title: styleno || '-',
                html: `
                    <table class="table table-sm table-bordered mb-0">
                        <thead>
                            <tr>
                                <th class="text-left">WS</th>
                                <th class="text-right">Qty</th>
                            </tr>
                        </thead>
                        <tbody>${rows || '<tr><td colspan="2" class="text-center text-muted">Tidak ada data</td></tr>'}</tbody>
                        <tfoot>
                            <tr>
                                <th class="text-left">Total</th>
                                <th class="text-right">${total.toLocaleString('id-ID')}</th>
                            </tr>
                        </tfoot>
                    </table>
                `,
                confirmButtonText: 'Tutup'
            });
        }

        function tickLiveClock() {
            const el = document.getElementById('liveClock');
            if (!el) return;
            el.textContent = new Date().toLocaleString('id-ID', {
                dateStyle: 'medium',
                timeStyle: 'medium'
            });
        }
        tickLiveClock();
        setInterval(tickLiveClock, 1000);

        // Auto-refresh data every 5 minutes so the live view stays up to date.
        const REFRESH_SECONDS = 5 * 60;
        let refreshRemaining = REFRESH_SECONDS;

        function tickRefreshCountdown() {
            const el = document.getElementById('refreshCountdown');
            if (!el) return;
            const minutes = String(Math.floor(refreshRemaining / 60)).padStart(2, '0');
            const seconds = String(refreshRemaining % 60).padStart(2, '0');
            el.textContent = `${minutes}:${seconds}`;

            if (refreshRemaining <= 0) {
                window.location.reload();
                return;
            }
            refreshRemaining--;
        }
        tickRefreshCountdown();
        setInterval(tickRefreshCountdown, 1000);
    </script>
@endsection
