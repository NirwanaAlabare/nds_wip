<div>
    <div class="loading-container-fullscreen" wire:loading wire:target='search, date, dateFrom, dateTo, group, filter, qcType'>
        <div class="loading-container">
            <div class="loading"></div>
        </div>
    </div>
    <h3 class="mt-3 text-center text-sb fw-bold">Output Report</h3>
    @php
        $subtitle = "";
        switch ($qcType) {
            case "" :
                $subtitle = "END-LINE";
                break;
            case "_finish" :
                $subtitle = "FINISH-LINE";
                break;
            case "_packing" :
                $subtitle = "PACKING-LINE";
                break;
            default :
                $subtitle = "END-LINE";
                break;
        }
    @endphp
    <h5 class="mb-3 text-center fw-bold">{{ $subtitle }}</h5>
    <div class="d-flex justify-content-between align-items-end gap-3">
        <div class="d-flex justify-content-start align-items-end gap-3">
            <div>
                <input type="text" class="form-control form-control-sm" name="search" id='search' wire:model.lazy='search' placeholder="Search...">
            </div>
            <div id="single-date" class="{{ $range == "custom" ? 'd-none' : '' }}">
                <input type="date" class="form-control form-control-sm" name="date" id='date' wire:model.lazy='date'>
            </div>
            <div class="{{ $range != "custom" ? 'd-none' : 'd-flex gap-3' }}" id="custom-date">
                <div>
                    <label>Dari</label>
                    <input type="date" class="form-control form-control-sm" name="datefrom" id='datefrom' wire:model.lazy='dateFrom'>
                </div>
                <div>
                    <label>Sampai</label>
                    <input type="date" class="form-control form-control-sm" name="dateto" id='dateto' wire:model.lazy='dateTo'>
                </div>
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-sb" data-bs-toggle="modal" data-bs-target="#filterModal">
                    <i class="fa-solid fa-filter fa-xs"></i>
                </button>
            </div>
        </div>
        <div>
            <button class="btn btn-sm btn-success" onclick="exportExcel(this, 'output', '{{ $this->qcType }}', '{{ $this->date }}', '{{ $this->dateFrom }}', '{{ $this->dateTo }}', '{{ $this->range }}')" id="export-excel">
                Export <i class="fa-solid fa-file-excel"></i>
            </button>
        </div>
    </div>

    {{-- Group By Line --}}
    @if ($this->group == 'line')
        <div class="row table-responsive {{ $this->group == 'line' ? '' : 'd-none' }}">
            <table class="table table-sm table-bordered mt-3">
                <thead>
                    <tr>
                        <th rowspan="2" class="align-middle text-center">Line</th>
                        <th rowspan="2" class="align-middle text-center">NIK</th>
                        <th rowspan="2" class="align-middle text-center">Leader</th>
                        <th rowspan="2" class="align-middle text-center">WS Number</th>
                        <th rowspan="2" class="align-middle text-center">Style</th>
                        <th colspan="5" class="text-center">Output</th>
                        <th colspan="3" class="text-center">Rate</th>
                        <th colspan="3" class="text-center">Total</th>
                        <th rowspan="2" class="align-middle text-center">Last Input</th>
                    </tr>
                    <tr>
                        <th class="text-center">RFT</th>
                        <th class="text-center">Defect</th>
                        <th class="text-center">Rework</th>
                        <th class="text-center">Reject</th>
                        <th class="text-center">Actual</th>
                        <th class="text-center">RFT</th>
                        <th class="text-center">Defect</th>
                        <th class="text-center">Reject</th>
                        <th class="text-center">Actual</th>
                        <th class="text-center">Target</th>
                        <th class="text-center">Efficiency</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $currentLine = '';
                        $currentRowSpan = 0;

                        $currentActual = 0;
                        $currentMinsProd = 0;
                        $currentTarget = 0;
                        $currentMinsAvail = 0;
                        $currentLastInput = 0;

                        $summaryActual = 0;
                        $summaryMinsProd = 0;
                        $summaryTarget = 0;
                        $summaryMinsAvail = 0;
                        $lastInput = 0;
                    @endphp
                    @if ($lines->count() < 1)
                        <tr>
                            <td class="text-center" colspan="14">
                                Data not found
                            </td>
                        </tr>
                    @else
                        @foreach ($lines as $line)
                            @php
                                $currentRowSpan = $lines->where("username", $line->username)->count();

                                $rateRft = $line->total_output > 0 ? round(($line->rft/$line->total_output * 100), 2) : '0';
                                $rateDefect = $line->total_output > 0 ? round((($line->defect+$line->rework)/$line->total_output * 100), 2) : '0';
                                $rateReject = $line->total_output > 0 ? round((($line->reject)/$line->total_output * 100), 2) : '0';
                            @endphp
                            <tr wire:key="{{ $loop->index }}">
                                @if ($currentLine != $line->username)
                                    <td rowspan="{{ $currentRowSpan }}" class="text-center align-middle">
                                        <a class="text-sb" href="http://10.10.5.62:8000/dashboard-wip/line/dashboard1/{{ $line->username }}" target="_blank">
                                            {{ ucfirst(str_replace("_", " ", $line->username)) }}
                                        </a>
                                    </td>
                                @endif
                                <td>{{ $line->leader_nik }}</td>
                                <td class="text-nowrap">{{ $line->leader_name }}</td>
                                <td>{{ $line->kpno }}</td>
                                <td>{{ $line->styleno }}</td>
                                <td class="fw-bold text-center">
                                    {{ $line->rft < 1 ? '0' : num($line->rft) }}
                                </td>
                                <td class="fw-bold text-center">
                                    {{ $line->defect < 1 ? '0' : num($line->defect) }}
                                </td>
                                <td class="fw-bold text-center">
                                    {{ $line->rework < 1 ? '0' : num($line->rework) }}
                                </td>
                                <td class="fw-bold text-center">
                                    {{ $line->reject < 1 ? '0' : num($line->reject) }}
                                </td>
                                <td class="fw-bold text-center text-sb">
                                    {{ $line->total_actual < 1 ? '0' : num($line->total_actual) }}
                                </td>
                                <td class="fw-bold text-center {{ $rateRft < 97 ? 'text-danger' : 'text-success' }}">
                                    {{ $rateRft }} %
                                </td>
                                <td class="fw-bold text-center {{ $rateDefect > 3 ? 'text-danger' : 'text-success' }}">
                                    {{ $rateDefect }} %
                                </td>
                                <td class="fw-bold text-center {{ $rateReject > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $rateReject }} %
                                </td>
                                @if ($currentLine != $line->username)
                                    @php
                                        if (($range == "custom" && date('Y-m-d H:i:s') >= $dateFrom.' 16:00:00') || date('Y-m-d H:i:s') >= $line->tgl_plan.' 16:00:00') {
                                            $cumulativeTarget = $lines->where("username", $line->username)->sum("total_target") ?? 0;
                                            $cumulativeMinsAvail = $lines->where("username", $line->username)->sum("mins_avail") ?? 0;
                                        } else {
                                            $cumulativeTarget = $line->cumulative_target ?? 0;
                                            $cumulativeMinsAvail = $line->cumulative_mins_avail ?? 0;
                                        }

                                        $currentActual = $lines->where("username", $line->username)->sum("total_actual") ?? 0;
                                        $currentMinsProd = $lines->where("username", $line->username)->sum("mins_prod") ?? 0;
                                        $currentTarget = $cumulativeTarget;
                                        $currentMinsAvail = $cumulativeMinsAvail;
                                        $currentLastInput = $lines->where("username", $line->username)->max("latest_output") ?? date("Y-m-d")." 00:00:00";

                                        $currentEfficiency = ($currentMinsAvail  > 0 ? round(($currentMinsProd/$currentMinsAvail)*100, 2) : 0);

                                        $summaryActual += $currentActual;
                                        $summaryMinsProd += $currentMinsProd;
                                        $summaryTarget += $currentTarget;
                                        $summaryMinsAvail += $currentMinsAvail;
                                        $lastInput < $currentLastInput && $lastInput = $currentLastInput;
                                    @endphp
                                    <td rowspan="{{ $currentRowSpan }}" class="fw-bold text-center text-sb fs-5 align-middle">
                                        {{ num($currentActual) }}
                                    </td>
                                    <td rowspan="{{ $currentRowSpan }}" class="fw-bold text-center text-sb fs-5 align-middle">
                                        {{ num($currentTarget) }}
                                    </td>
                                    <td rowspan="{{ $currentRowSpan }}" class="fw-bold text-center fs-5 align-middle {{ $currentEfficiency < 85 ? 'text-danger' : 'text-success' }}">
                                        {{ $currentEfficiency }} %
                                    </td>
                                    <td rowspan="{{ $currentRowSpan }}" class="text-center align-middle">
                                        {{ $currentLastInput }}
                                    </td>
                                @endif
                            </tr>
                            @php
                                if ($currentLine != $line->username) {
                                    $currentLine = $line->username;
                                }
                            @endphp
                        @endforeach
                    @endif
                </tbody>
                <tfoot>
                    @php
                        $summaryEfficiency = $summaryMinsAvail > 0 ? round($summaryMinsProd/$summaryMinsAvail*100, 2) : 0;
                        $targetFromEfficiency = $summaryMinsAvail > 0 ? (($summaryMinsProd/$summaryMinsAvail) > 0 ? floor($summaryActual / ($summaryMinsProd/$summaryMinsAvail)) : 0) : 0;
                    @endphp
                    <tr>
                        <th colspan="13" class="fs-5 text-center">Summary</th>
                        <th class="fs-5 text-center">{{ num($summaryActual) }}</th>
                        <th class="fs-5 text-center">{{ num($targetFromEfficiency) }}</th>
                        <th class="fs-5 text-center {{ $summaryEfficiency < 85 ? 'text-danger' : 'text-success' }}">{{ $summaryEfficiency }} %</th>
                        <td class="text-center">{{ $lastInput }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif

    {{-- Group By WS --}}
    @if ($this->group == 'ws')
        <div class="row table-responsive {{ $this->group == 'ws' ? '' : 'd-none' }}">
            <table class="table table-sm table-bordered mt-3">
                <thead>
                    <tr>
                        <th rowspan="2" class="align-middle text-center">WS Number</th>
                        <th rowspan="2" class="align-middle text-center">Style</th>
                        <th rowspan="2" class="align-middle text-center">Line</th>
                        <th rowspan="2" class="align-middle text-center">NIK</th>
                        <th rowspan="2" class="align-middle text-center">Leader</th>
                        <th colspan="5" class="text-center">Output</th>
                        <th colspan="3" class="text-center">Rate</th>
                        <th colspan="3" class="text-center">Total</th>
                        <th rowspan="2" class="align-middle text-center">Last Input</th>
                    </tr>
                    <tr>
                        <th class="text-center">RFT</th>
                        <th class="text-center">Defect</th>
                        <th class="text-center">Rework</th>
                        <th class="text-center">Reject</th>
                        <th class="text-center">Actual</th>
                        <th class="text-center">RFT</th>
                        <th class="text-center">Defect</th>
                        <th class="text-center">Reject</th>
                        <th class="text-center">Actual</th>
                        <th class="text-center">Target</th>
                        <th class="text-center">Efficiency</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $currentIdWs = '';
                        $currentStyle = '';
                        $currentRowSpan = 0;

                        $currentActual = 0;
                        $currentMinsProd = 0;
                        $currentTarget = 0;
                        $currentMinsAvail = 0;
                        $currentLastInput = 0;

                        $summaryActual = 0;
                        $summaryMinsProd = 0;
                        $summaryTarget = 0;
                        $summaryMinsAvail = 0;
                        $lastInput = 0;
                    @endphp
                    @if ($orders->count() < 1)
                        <tr>
                            <td class="text-center" colspan="14">
                                Data not found
                            </td>
                        </tr>
                    @else
                        @foreach ($orders as $order)
                            @php
                                $currentRowSpan = $orders->where("id_ws", $order->id_ws)->where("styleno", $order->styleno)->count();

                                $rateRft = $order->total_output > 0 ? round(($order->rft/$order->total_output * 100), 2) : '0';
                                $rateDefect = $order->total_output > 0 ? round((($order->defect+$order->rework)/$order->total_output * 100), 2) : '0';
                                $rateReject = $order->total_output > 0 ? round((($order->reject)/$order->total_output * 100), 2) : '0';
                            @endphp
                            <tr wire:key="{{ $loop->index }}">
                                @if ($currentIdWs != $order->id_ws || $currentStyle != $order->styleno)
                                    <td rowspan="{{ $currentRowSpan }}" class="align-middle">{{ $order->kpno }}</td>
                                    <td rowspan="{{ $currentRowSpan }}" class="align-middle">{{ $order->styleno }}</td>
                                @endif
                                <td>
                                    <a class="text-sb" href="http://10.10.5.62:8000/dashboard-wip/line/dashboard1/{{ $order->username }}" target="_blank">
                                        {{ ucfirst(str_replace("_", " ", $order->username)) }}
                                    </a>
                                </td>
                                <td>
                                    {{ $order->leader_nik }}
                                </td>
                                <td class="text-nowrap">
                                    {{ $order->leader_name }}
                                </td>
                                <td class="fw-bold text-center">
                                    {{ $order->rft < 1 ? '0' : num($order->rft) }}
                                </td>
                                <td class="fw-bold text-center">
                                    {{ $order->defect < 1 ? '0' : num($order->defect) }}
                                </td>
                                <td class="fw-bold text-center">
                                    {{ $order->rework < 1 ? '0' : num($order->rework) }}
                                </td>
                                <td class="fw-bold text-center">
                                    {{ $order->reject < 1 ? '0' : num($order->reject) }}
                                </td>
                                <td class="fw-bold text-center text-sb">
                                    {{ $order->total_actual < 1 ? '0' : num($order->total_actual) }}
                                </td>
                                <td class="fw-bold text-center {{ $rateRft < 97 ? 'text-danger' : 'text-success' }}">
                                    {{ $rateRft }} %
                                </td>
                                <td class="fw-bold text-center {{ $rateDefect > 3 ? 'text-danger' : 'text-success' }}">
                                    {{ $rateDefect }} %
                                </td>
                                <td class="fw-bold text-center {{ $rateReject > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $rateReject }} %
                                </td>
                                @if ($currentIdWs != $order->id_ws)
                                    @php
                                        // if (($range == "custom" && date('Y-m-d H:i:s') >= $dateFrom.' 16:00:00') || date('Y-m-d H:i:s') >= $order->tgl_plan.' 16:00:00') {
                                        //     $cumulativeTarget = $orders->where("id_ws", $order->id_ws)->where("styleno", $order->styleno)->sum("total_target") ?? 0;
                                        //     $cumulativeMinsAvail = $orders->where("id_ws", $order->id_ws)->where("styleno", $order->styleno)->sum("mins_avail") ?? 0;
                                        // } else {
                                        //     $cumulativeTarget = $order->cumulative_target ?? 0;
                                        //     $cumulativeMinsAvail = $order->cumulative_mins_avail ?? 0;
                                        // }
                                        $cumulativeTarget = $orders->where("id_ws", $order->id_ws)->where("styleno", $order->styleno)->sum("total_target") ?? 0;
                                        $cumulativeMinsAvail = $orders->where("id_ws", $order->id_ws)->where("styleno", $order->styleno)->sum("mins_avail") ?? 0;

                                        $currentActual = $orders->where("id_ws", $order->id_ws)->where("styleno", $order->styleno)->sum("total_actual") ?? 0;
                                        $currentMinsProd = $orders->where("id_ws", $order->id_ws)->where("styleno", $order->styleno)->sum("mins_prod") ?? 0;
                                        $currentTarget = $cumulativeTarget;
                                        $currentMinsAvail = $cumulativeMinsAvail;
                                        $currentLastInput = $orders->where("id_ws", $order->id_ws)->where("styleno", $order->styleno)->max("latest_output") ?? date("Y-m-d")." 00:00:00";

                                        $currentEfficiency = ($currentMinsAvail  > 0 ? round(($currentMinsProd/$currentMinsAvail)*100, 2) : 0);

                                        $summaryActual += $currentActual;
                                        $summaryMinsProd += $currentMinsProd;
                                        $summaryTarget += $currentTarget;
                                        $summaryMinsAvail += $currentMinsAvail;
                                        $lastInput < $currentLastInput && $lastInput = $currentLastInput;
                                    @endphp
                                    <td rowspan="{{ $currentRowSpan }}" class="fw-bold text-center text-sb fs-5 align-middle">
                                        {{ num($currentActual) }}
                                    </td>
                                    <td rowspan="{{ $currentRowSpan }}" class="fw-bold text-center text-sb fs-5 align-middle">
                                        {{ num($currentTarget) }}
                                    </td>
                                    <td rowspan="{{ $currentRowSpan }}" class="fw-bold text-center fs-5 align-middle {{ $currentEfficiency < 85 ? 'text-danger' : 'text-success' }}">
                                        {{ $currentEfficiency }} %
                                    </td>
                                    <td rowspan="{{ $currentRowSpan }}" class="text-center align-middle">
                                        {{ $currentLastInput }}
                                    </td>
                                @endif
                            </tr>
                            @php
                                if ($currentIdWs != $order->id_ws) {
                                    $currentIdWs = $order->id_ws;
                                    $currentStyle = $order->styleno;
                                }
                            @endphp
                        @endforeach
                    @endif
                </tbody>
                <tfoot>
                    @php
                        $summaryEfficiency = $summaryMinsAvail > 0 ? round($summaryMinsProd/$summaryMinsAvail*100, 2) : 0;
                        $targetFromEfficiency =$summaryMinsAvail > 0 ? (($summaryMinsProd/$summaryMinsAvail) > 0 ? floor($summaryActual / ($summaryMinsProd/$summaryMinsAvail)) : 0) : 0;
                    @endphp
                    <tr>
                        <th colspan="11" class="fs-5 text-center">Summary</th>
                        <th class="fs-5 text-center">{{ num($summaryActual) }}</th>
                        <th class="fs-5 text-center">{{ num($targetFromEfficiency) }}</th>
                        <th class="fs-5 text-center {{ $summaryEfficiency < 85 ? 'text-danger' : 'text-success' }}">{{ $summaryEfficiency }} %</th>
                        <td class="text-center">{{ $lastInput }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif

    {{-- Group By Style --}}
    @if ($this->group == 'style')
        <div class="row table-responsive {{ $this->group == 'style' ? '' : 'd-none' }}">
            <table class="table table-sm table-bordered mt-3">
                <thead>
                    <tr>
                        <th rowspan="2" class="align-middle text-center">Style</th>
                        <th rowspan="2" class="align-middle text-center">Line</th>
                        <th colspan="5" class="text-center">Output</th>
                        <th colspan="3" class="text-center">Rate</th>
                        <th colspan="3" class="text-center">Total</th>
                        <th rowspan="2" class="align-middle text-center">Last Input</th>
                    </tr>
                    <tr>
                        <th class="text-center">RFT</th>
                        <th class="text-center">Defect</th>
                        <th class="text-center">Rework</th>
                        <th class="text-center">Reject</th>
                        <th class="text-center">Actual</th>
                        <th class="text-center">RFT</th>
                        <th class="text-center">Defect</th>
                        <th class="text-center">Reject</th>
                        <th class="text-center">Actual</th>
                        <th class="text-center">Target</th>
                        <th class="text-center">Efficiency</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $currentStyle = '';
                        $currentRowSpan = 0;

                        $currentActual = 0;
                        $currentMinsProd = 0;
                        $currentTarget = 0;
                        $currentMinsAvail = 0;
                        $currentLastInput = 0;

                        $summaryActual = 0;
                        $summaryMinsProd = 0;
                        $summaryTarget = 0;
                        $summaryMinsAvail = 0;
                        $lastInput = 0;
                    @endphp
                    @if ($orders->count() < 1)
                        <tr>
                            <td class="text-center" colspan="14">
                                Data not found
                            </td>
                        </tr>
                    @else
                        @foreach ($orders as $order)
                            @php
                                $currentRowSpan = $orders->where("styleno", $order->styleno)->count();

                                $rateRft = $order->total_output > 0 ? round(($order->rft/$order->total_output * 100), 2) : '0';
                                $rateDefect = $order->total_output > 0 ? round((($order->defect+$order->rework)/$order->total_output * 100), 2) : '0';
                                $rateReject = $order->total_output > 0 ? round((($order->reject)/$order->total_output * 100), 2) : '0';
                            @endphp
                            <tr wire:key="{{ $loop->index }}">
                                @if ($currentStyle != $order->styleno)
                                    <td rowspan="{{ $currentRowSpan }}" class="align-middle">{{ $order->styleno }}</td>
                                @endif
                                <td>
                                    <a class="text-sb" href="http://10.10.5.62:8000/dashboard-wip/line/dashboard1/{{ $order->username }}" target="_blank">
                                        {{ ucfirst(str_replace("_", " ", $order->username)) }}
                                    </a>
                                </td>
                                <td>
                                    {{ $order->leader_name }}
                                </td>
                                <td class="fw-bold text-center">
                                    {{ $order->rft < 1 ? '0' : num($order->rft) }}
                                </td>
                                <td class="fw-bold text-center">
                                    {{ $order->defect < 1 ? '0' : num($order->defect) }}
                                </td>
                                <td class="fw-bold text-center">
                                    {{ $order->rework < 1 ? '0' : num($order->rework) }}
                                </td>
                                <td class="fw-bold text-center">
                                    {{ $order->reject < 1 ? '0' : num($order->reject) }}
                                </td>
                                <td class="fw-bold text-center text-sb">
                                    {{ $order->total_actual < 1 ? '0' : num($order->total_actual) }}
                                </td>
                                <td class="fw-bold text-center {{ $rateRft < 97 ? 'text-danger' : 'text-success' }}">
                                    {{ $rateRft }} %
                                </td>
                                <td class="fw-bold text-center {{ $rateDefect > 3 ? 'text-danger' : 'text-success' }}">
                                    {{ $rateDefect }} %
                                </td>
                                <td class="fw-bold text-center {{ $rateReject > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $rateReject }} %
                                </td>
                                @if ($currentStyle != $order->styleno)
                                    @php
                                        // if (($range == "custom" && date('Y-m-d H:i:s') >= $dateFrom.' 16:00:00') || date('Y-m-d H:i:s') >= $order->tgl_plan.' 16:00:00') {
                                        //     $cumulativeTarget = $orders->where("styleno", $order->styleno)->sum("total_target") ?? 0;
                                        //     $cumulativeMinsAvail = $orders->where("styleno", $order->styleno)->sum("mins_avail") ?? 0;
                                        // } else {
                                        //     $cumulativeTarget = $orders->cumulative_target ?? 0;
                                        //     $cumulativeMinsAvail = $orders->cumulative_mins_avail ?? 0;
                                        // }
                                        $cumulativeTarget = $orders->where("styleno", $order->styleno)->sum("total_target") ?? 0;
                                        $cumulativeMinsAvail = $orders->where("styleno", $order->styleno)->sum("mins_avail") ?? 0;

                                        $currentActual = $orders->where("styleno", $order->styleno)->sum("total_actual") ?? 0;
                                        $currentMinsProd = $orders->where("styleno", $order->styleno)->sum("mins_prod") ?? 0;
                                        $currentTarget = $cumulativeTarget;
                                        $currentMinsAvail = $cumulativeMinsAvail;
                                        $currentLastInput = $orders->where("styleno", $order->styleno)->max("latest_output") ?? date("Y-m-d")." 00:00:00";

                                        $currentEfficiency = ($currentMinsAvail  > 0 ? round(($currentMinsProd/$currentMinsAvail)*100, 2) : 0);

                                        $summaryActual += $currentActual;
                                        $summaryMinsProd += $currentMinsProd;
                                        $summaryTarget += $currentTarget;
                                        $summaryMinsAvail += $currentMinsAvail;
                                        $lastInput < $currentLastInput && $lastInput = $currentLastInput;
                                    @endphp
                                    <td rowspan="{{ $currentRowSpan }}" class="fw-bold text-center text-sb fs-5 align-middle">
                                        {{ num($currentActual) }}
                                    </td>
                                    <td rowspan="{{ $currentRowSpan }}" class="fw-bold text-center text-sb fs-5 align-middle">
                                        {{ num($currentTarget) }}
                                    </td>
                                    <td rowspan="{{ $currentRowSpan }}" class="fw-bold text-center fs-5 align-middle {{ $currentEfficiency < 85 ? 'text-danger' : 'text-success' }}">
                                        {{ $currentEfficiency }} %
                                    </td>
                                    <td rowspan="{{ $currentRowSpan }}" class="text-center align-middle">
                                        {{ $currentLastInput }}
                                    </td>
                                @endif
                            </tr>
                            @php
                                if ($currentStyle != $order->styleno) {
                                    $currentStyle = $order->styleno;
                                }
                            @endphp
                        @endforeach
                    @endif
                </tbody>
                <tfoot>
                    @php
                        $summaryEfficiency = $summaryMinsAvail > 0 ? round($summaryMinsProd/$summaryMinsAvail*100, 2) : 0;
                        $targetFromEfficiency =$summaryMinsAvail > 0 ? (($summaryMinsProd/$summaryMinsAvail) > 0 ? floor($summaryActual / ($summaryMinsProd/$summaryMinsAvail)) : 0) : 0;
                    @endphp
                    <tr>
                        <th colspan="10" class="fs-5 text-center">Summary</th>
                        <th class="fs-5 text-center">{{ num($summaryActual) }}</th>
                        <th class="fs-5 text-center">{{ num($targetFromEfficiency) }}</th>
                        <th class="fs-5 text-center {{ $summaryEfficiency < 85 ? 'text-danger' : 'text-success' }}">{{ $summaryEfficiency }} %</th>
                        <td class="text-center">{{ $lastInput }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endif

    {{-- Top 5 Defects --}}
    <h5 class="mt-3 text-sb text-center fw-bold">Top 5 Defects</h5>
    <div class="row table-responsive">
        <table class="table table-sm table-bordered mt-3">
            <thead>
                <tr>
                    <th class="text-center">No.</th>
                    <th class="text-center" colspan="3">Defect Types</th>
                    <th class="text-center" colspan="2">Defect Areas</th>
                    <th class="text-center" colspan="2">Line</th>
                </tr>
            </thead>
            <tbody>
                @if ($defectTypes->count() < 1)
                    <tr>
                        <td colspan="8" class="text-center">Data not found</td>
                    </tr>
                @else
                    @foreach ($defectTypes as $type)
                        @php
                            $defectAreasFiltered = $defectAreas->where("defect_type_id", $type->defect_type_id)->take(5);
                            $lineDefectsFilteredType = $lineDefects->where("defect_type_id", $type->defect_type_id);
                            $firstDefectAreasFiltered = $defectAreasFiltered->first();
                            $lineDefectsFilteredAreaFirstCol = $lineDefectsFilteredType->where('defect_area_id', $firstDefectAreasFiltered->defect_area_id)->sortByDesc('total')->take(5);
                            $firstLineDefectsFilteredArea = $lineDefectsFilteredAreaFirstCol->first();
                            $typeRowspan = 0;

                            foreach ($defectAreasFiltered as $area) {
                                $typeRowspan += $lineDefectsFilteredType->where('defect_area_id', $area->defect_area_id)->take(5)->count();
                            }
                        @endphp
                        <tr>
                            <td {{ $typeRowspan > 1 ? 'rowspan='.$typeRowspan : '' }} class="text-center align-middle">{{ $loop->iteration }}</td>
                            <td {{ $typeRowspan > 1 ? 'rowspan='.$typeRowspan : '' }} class="align-middle">
                                {{ $type->defect_type }}
                            </td>
                            <td {{ $typeRowspan > 1 ? 'rowspan='.$typeRowspan : '' }} class="text-center align-middle">
                                <b>{{$type->defect_type_count}}</b>
                            </td>
                            <td {{ $typeRowspan > 1 ? 'rowspan='.$typeRowspan : '' }} class="text-center align-middle">
                                <b>{{ $summaryActual > 0 ? round((($type->defect_type_count/$summaryActual)*100), 2) : '0' }} %</b>
                            </td>
                            <td {{ $lineDefectsFilteredAreaFirstCol->count() > 1 ? 'rowspan='.($lineDefectsFilteredAreaFirstCol->count()) : '' }} class="align-middle">
                                <div class="d-flex justify-content-between">
                                    {{ $defectAreasFiltered->first()->defect_area }}
                                </div>
                            </td>
                            <td {{ $lineDefectsFilteredAreaFirstCol->count() > 1 ? 'rowspan='.($lineDefectsFilteredAreaFirstCol->count()) : '' }} class="text-center align-middle">
                                <b>{{  $defectAreasFiltered->first()->defect_area_count }}</b>
                            </td>
                            <td>
                                {{ $firstLineDefectsFilteredArea->sewing_line }}
                            </td>
                            <td class="text-center">
                                <b>{{ $firstLineDefectsFilteredArea->total }}</b>
                            </td>
                        </tr>
                        @if ($lineDefectsFilteredAreaFirstCol->count() > 1)
                            @foreach ($lineDefectsFilteredAreaFirstCol as $line)
                                @if ($loop->index > 0)
                                    <tr>
                                        <td>
                                            {{ $line->sewing_line }}
                                        </td>
                                        <td class="text-center">
                                            <b>{{ $line->total }}</b>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        @endif

                        @if ($defectAreasFiltered->count() > 1)
                            @foreach ($defectAreasFiltered as $area)
                                @if ($loop->index > 0)
                                    @php
                                        $lineDefectAreasFilteredNextCol = $lineDefectsFilteredType->where("defect_area_id", $area->defect_area_id)->sortByDesc('total')->take(5);
                                    @endphp
                                    <tr>
                                        <td {{ $lineDefectAreasFilteredNextCol->count() > 1 ? 'rowspan='.$lineDefectAreasFilteredNextCol->count() : '' }} class="align-middle">
                                            {{ $area->defect_area }}
                                        </td>
                                        <td {{ $lineDefectAreasFilteredNextCol->count() > 1 ? 'rowspan='.$lineDefectAreasFilteredNextCol->count() : '' }} class="text-center align-middle">
                                            <b>{{ num($area->defect_area_count) }}</b>
                                        </td>
                                        <td>
                                            {{ $lineDefectAreasFilteredNextCol->first()->sewing_line }}
                                        </td>
                                        <td class="text-center">
                                            <b>{{ num($lineDefectAreasFilteredNextCol->first()->total) }}</b>
                                        </td>
                                    </tr>

                                    @if ($lineDefectAreasFilteredNextCol->count() > 1)
                                        @foreach ($lineDefectAreasFilteredNextCol as $line)
                                            @if ($loop->index > 0)
                                                <tr>
                                                    <td>
                                                        {{ $line->sewing_line }}
                                                    </td>
                                                    <td class="text-center">
                                                        <b>{{ num($line->total) }}</b>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    @endif
                                @endif
                            @endforeach
                        @endif

                    @endforeach
                @endif
            </tbody>
        </table>
    </div>

    {{-- Filter Modal --}}
    <div class="modal fade" id="filterModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h5 class="modal-title">
                        <i class="fa fa-filter"></i>
                        Filter
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="fw-bold mb-1">QC Output</label>
                        <select class="form-select" id="qc-type" wire:model="qcType">
                            <option value="">End-line</option>
                            <option value="_finish">Finish-line</option>
                            <option value="_packing">Packing-line</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold mb-1">Group By</label>
                        <div id="select-group mb-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="filter-group" id="line-group" value="line" checked="checked">
                                <label class="form-check-label" for="line-group">Line</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="filter-group" id="ws-group" value="ws">
                                <label class="form-check-label" for="ws-group">WS Number</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="filter-group" id="style-group" value="style">
                                <label class="form-check-label" for="style-group">Style</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold mb-3">Daterange</label>
                        <div id="select-range" class="mb-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="date-range" id="single-range" value="single" checked="checked">
                                <label class="form-check-label" for="single-range">Daily</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="date-range" id="custom-range" value="custom">
                                <label class="form-check-label" for="custom-range">Custom</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3 d-none">
                        <label class="mb-1">Periode</label>
                        <div id="select-period" class="mb-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="filter-period" id="daily-period" value="daily" checked="checked">
                                <label class="form-check-label" for="daily-period">1 Hari</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="filter-period" id="monthly-period" value="monthly">
                                <label class="form-check-label" for="monthly-period">1 Bulan</label>
                            </div>
                        </div>
                    </div>
                    <div class="row my-1 d-none" id="monthly-filter">
                        <div class="col-6">
                            <select class="form-select form-select-sm select2" name="filter-month" id="filter-month" model="">
                                @foreach ($months as $month)
                                    <option value="{{ $month['angka'] }}">{{ $month['nama'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <select class="form-select form-select-sm select2" name="filter-monthyear" id="filter-monthyear" model="">
                                @foreach ($years as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="my-1 d-none" id="daily-filter">
                        <input class="form-control form-control-sm" type="date" name="filter-date" id="filter-date" value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sb" id="filter-modal-button" wire:onc>
                        <i class="fa fa-check"></i>
                        Apply
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Grouping
        $("#line-group").prop("checked", true);

        $('#line-group').on('change', () => {
            console.log(document.querySelector('input[name="filter-group"]:checked').value);
        });

        $('#ws-group').on('change', () => {
            console.log(document.querySelector('input[name="filter-group"]:checked').value);
        });

        $('#style-group').on('change', () => {
            console.log(document.querySelector('input[name="filter-group"]:checked').value);
        });

    // Period
        $("#daily-period").prop("checked", true);

        $('#monthly-period').on('change', () => {
            console.log(document.querySelector('input[name="filter-period"]:checked').value);
            if (document.querySelector('input[name="filter-period"]:checked').value == 'monthly') {
                document.getElementById('monthly-filter').classList.remove('d-none');
                document.getElementById('daily-filter').classList.add('d-none');
            } else {
                document.getElementById('monthly-filter').classList.add('d-none');
                document.getElementById('daily-filter').classList.remove('d-none');
            }
        });

        $('#daily-period').on('change', () => {
            console.log(document.querySelector('input[name="filter-period"]:checked').value);
            if (document.querySelector('input[name="filter-period"]:checked').value == 'daily') {
                document.getElementById('daily-filter').classList.remove('d-none');
                document.getElementById('monthly-filter').classList.add('d-none');
            } else {
                document.getElementById('daily-filter').classList.add('d-none');
                document.getElementById('monthly-filter').classList.remove('d-none');
            }
        });

    // Range
        $("#single-range").prop("checked", true);

        $('#single-range').on('change', () => {
            console.log(document.querySelector('input[name="date-range"]:checked').value);
            if (document.querySelector('input[name="date-range"]:checked').value == 'single') {
                document.getElementById('single-date').classList.remove('d-none');
                document.getElementById('custom-date').classList.add('d-none');
            } else {
                document.getElementById('single-date').classList.add('d-none');
                document.getElementById('custom-date').classList.remove('d-none');
            }
        });

        $('#custom-range').on('change', () => {
            console.log(document.querySelector('input[name="date-range"]:checked').value);
            if (document.querySelector('input[name="date-range"]:checked').value == 'single') {
                document.getElementById('single-date').classList.remove('d-none');
                document.getElementById('custom-date').classList.add('d-none');
            } else {
                document.getElementById('single-date').classList.add('d-none');
                document.getElementById('custom-date').classList.remove('d-none');
            }
        })

        document.getElementById('filterModal').addEventListener('hidden.bs.modal', event => {
            filter();
        })

        $('#filter-modal-button').on('click', () => {
            filter();
        });

        async function filter() {
            let group = document.querySelector('input[name="filter-group"]:checked').value;
            let period = document.querySelector('input[name="filter-period"]:checked').value;
            let range = document.querySelector('input[name="date-range"]:checked').value;
            await @this.filter(group, period, range);
            $("#"+group+"-group").prop("checked", true);
            $("#"+period+"-period").prop("checked", true);
            $("#"+range+"-range").prop("checked", true);
        }

        function exportExcel(elm, type, subtype, date, dateFrom, dateTo, range) {
            elm.setAttribute('disabled', 'true');
            elm.innerText = "";
            let loading = document.createElement('div');
            loading.classList.add('loading-small');
            elm.appendChild(loading);

            iziToast.info({
                title: 'Exporting...',
                message: 'Data sedang di export. Mohon tunggu...',
                position: 'topCenter'
            });

            if (type == 'output') {
                $.ajax({
                    url: "{{ url("/report/output/export") }}",
                    type: 'post',
                    data: {
                        subtype : subtype,
                        date : date,
                        dateFrom : dateFrom,
                        dateTo : dateTo,
                        range : range
                    },
                    xhrFields: { responseType : 'blob' },
                    success: function(res) {
                        elm.removeAttribute('disabled');
                        elm.innerText = "Export ";
                        let icon = document.createElement('i');
                        icon.classList.add('fa-solid');
                        icon.classList.add('fa-file-excel');
                        elm.appendChild(icon);

                        iziToast.success({
                            title: 'Success',
                            message: 'Data berhasil di export.',
                            position: 'topCenter'
                        });

                        var blob = new Blob([res]);
                        var link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = date+" Output Report.xlsx";
                        link.click();
                    }, error: function (jqXHR) {
                        elm.removeAttribute('disabled');
                        elm.innerText = "Export ";
                        let icon = document.createElement('i');
                        icon.classList.add('fa-solid');
                        icon.classList.add('fa-file-excel');
                        elm.appendChild(icon);

                        let res = jqXHR.responseJSON;
                        let message = '';
                        console.log(res.message);
                        for (let key in res.errors) {
                            message += res.errors[key]+' ';
                            document.getElementById(key).classList.add('is-invalid');
                        };
                        iziToast.error({
                            title: 'Error',
                            message: message,
                            position: 'topCenter'
                        });
                    }
                });
            }
        }
    </script>
@endpush
