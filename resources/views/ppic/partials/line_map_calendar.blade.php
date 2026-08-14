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
                            'line-map-drop-target',
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
                    <td class="{{ $planCellClasses }}" data-line="{{ $ln->username }}"
                        data-date="{{ $date->tanggal }}"
                        @if ($isWithinPlanRange) data-plan-id="{{ $activeEntry->id }}" style="--plan-line-color: {{ $activeEntry->style_color }};" @endif>
                        @if ($hasPlan || $actualEntries->isNotEmpty())
                            @php
                                $planColor = $activeEntry->style_color ?? '#6f42c1';
                                $planFontColor = $activeEntry->font_color ?? '#ffffff';
                            @endphp
                            <div class="line-map-cell-stack">
                                @if ($hasPlan)
                                    <div class="line-map-box line-map-box-plan @if (!$canEditLineMap) line-map-box-plan-readonly @endif"
                                        draggable="{{ $canEditLineMap && $isPlanStart ? 'true' : 'false' }}"
                                        style="--dot-color: {{ $planColor }}; --font-color: {{ $planFontColor }};"
                                        data-id="{{ $activeEntry->id }}"
                                        data-line="{{ $activeEntry->line }}"
                                        data-date="{{ $date->tanggal }}"
                                        data-style="{{ $activeEntry->style }}"
                                        data-product-group="{{ $activeEntry->product_group }}"
                                        title="{{ $planTitle }}"
                                        @if ($canEditLineMap)
                                            data-bs-toggle="modal" data-bs-target="#newLineMapModal"
                                            onclick='openEditLineMap(@json($activeEntry->edit_payload))'
                                        @endif>
                                        <div class="line-map-box-header">
                                            <span
                                                class="box-buyer">{{ $activeEntry->buyer ?: '-' }}</span>
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
                                                <span
                                                    class="row-label">{{ $actual->styleno ?: '-' }}</span>
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