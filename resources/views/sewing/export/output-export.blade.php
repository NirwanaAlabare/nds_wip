<table>
    @php
        $subtitle = "";
        switch ($subtype) {
            case "" :
                $subtitle = "END-LINE";
                break;
            case "_finish" :
                $subtitle = "FINISH-LINE";
                break;
            case "_packing" :
                $subtitle = "FINISHING-LINE";
                break;
            default :
                $subtitle = "END-LINE";
                break;
        }
    @endphp
    <tr>
        <td colspan="15" style="text-align: center;">{{ $date }}</td>
    </tr>
    <tr>
        <td colspan="15" style="text-align: center; font-weight: 800;">OUTPUT {{ $subtitle }} {{ $search }}</td>
    </tr>
    {{-- Group By Line --}}
    @if ($group == 'line')
        <tr>
            <th rowspan="2" style="vertical-align: middle; text-align: center;">Line</th>
            <th rowspan="2" style="vertical-align: middle; text-align: center;">NIK</th>
            <th rowspan="2" style="vertical-align: middle; text-align: center;">Leader</th>
            <th rowspan="2" style="vertical-align: middle; text-align: center;">WS Number</th>
            <th rowspan="2" style="vertical-align: middle; text-align: center;">Style</th>
            <th colspan="5" style="text-align: center;">Output</th>
            <th colspan="3" style="text-align: center;">Rate</th>
            <th colspan="3" style="text-align: center;">Total</th>
            <th rowspan="2" style="vertical-align: middle; text-align: center;">Last Input</th>
        </tr>
        <tr>
            <th style="text-align: center;">RFT</th>
            <th style="text-align: center;">Defect</th>
            <th style="text-align: center;">Rework</th>
            <th style="text-align: center;">Reject</th>
            <th style="text-align: center;">Actual</th>
            <th style="text-align: center;">RFT</th>
            <th style="text-align: center;">Defect</th>
            <th style="text-align: center;">Reject</th>
            <th style="text-align: center;">Actual</th>
            <th style="text-align: center;">Target</th>
            <th style="text-align: center;">Efficiency</th>
        </tr>
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
                <td style="text-align: center;" colspan="14">
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
                <tr>
                    @if ($currentLine != $line->username)
                        <td rowspan="{{ $currentRowSpan }}" style="text-align: center; vertical-align: middle;">
                            <a style="text" href="http://10.10.5.62:8000/dashboard-wip/line/dashboard1/{{ $line->username }}" target="_blank">
                                {{ ucfirst(str_replace("_", " ", $line->username)) }}
                            </a>
                        </td>
                        <td rowspan="{{ $currentRowSpan }}" style="text-align: center; vertical-align: middle;">{{ $line->leader_nik }}</td>
                        <td rowspan="{{ $currentRowSpan }}" style="text-align: center; vertical-align: middle;">{{ $line->leader_name }}</td>
                    @endif
                    <td>{{ $line->kpno }}</td>
                    <td>{{ $line->styleno }}</td>
                    <td style="font-weight: 800; text-align: center;">
                        {{ $line->rft < 1 ? '0' : $line->rft }}
                    </td>
                    <td style="font-weight: 800; text-align: center;">
                        {{ $line->defect < 1 ? '0' : $line->defect }}
                    </td>
                    <td style="font-weight: 800; text-align: center;">
                        {{ $line->rework < 1 ? '0' : $line->rework }}
                    </td>
                    <td style="font-weight: 800; text-align: center;">
                        {{ $line->reject < 1 ? '0' : $line->reject }}
                    </td>
                    <td style="font-weight: 800; text-align: center; color: #082149;">
                        {{ $line->total_actual < 1 ? '0' : $line->total_actual }}
                    </td>
                    <td style="font-weight: 800; text-align: center; {{ $rateRft < 97 ? 'color: #d62718;' : 'color: #07b54d;' }}">
                        {{ $rateRft }} %
                    </td>
                    <td style="font-weight: 800; text-align: center; {{ $rateDefect > 3 ? 'color: #d62718;' : 'color: #07b54d;' }}">
                        {{ $rateDefect }} %
                    </td>
                    <td style="font-weight: 800; text-align: center; {{ $rateReject > 0 ? 'color: #d62718;' : 'color: #07b54d;' }}">
                        {{ $rateReject }} %
                    </td>
                    @if ($currentLine != $line->username)
                        @php
                            // Legacy :
                            if (date('Y-m-d H:i:s') >= $line->tgl_plan.' 16:00:00') {
                                // $cumulativeTarget = $lines->where("username", $line->username)->sum("total_target") > 0 ? $lines->where("username", $line->username)->sum("total_target") : $lines->where("username", $line->username)->sum("total_target_back_date");
                                // $cumulativeMinsAvail = $lines->where("username", $line->username)->sum("mins_avail") > 0 ? $lines->where("username", $line->username)->sum("mins_avail") : $lines->where("username", $line->username)->sum("mins_avail_back_date");
                                $cumulativeTarget = $lines->where("username", $line->username)->sum("total_target") ?? 0;
                                $cumulativeMinsAvail = $lines->where("username", $line->username)->sum("mins_avail") ?? 0;
                            } else {
                                $cumulativeTarget = $line->cumulative_target ?? 0;
                                $cumulativeMinsAvail = $line->cumulative_mins_avail ?? 0;
                            }
                            // New Version :
                                // if (($range == "custom" && date('Y-m-d H:i:s') >= $dateFrom.' 16:00:00')) {
                                //     $cumulativeTarget = $lines->where("username", $line->username)->sum("total_target") ?? 0;
                                //     $cumulativeMinsAvail = $lines->where("username", $line->username)->sum("mins_avail") ?? 0;
                                // } else {
                                //     $cumulativeTarget = $lines->where("username", $line->username)->max("cumulative_target")  ?? 0;
                                //     $cumulativeMinsAvail =  $lines->where("username", $line->username)->max("cumulative_mins_avail") ?? 0;
                                // }

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
                        <td rowspan="{{ $currentRowSpan }}" style="font-weight: 800; text-align: center; color: #082149; vertical-align: middle;">
                            {{ $currentActual }}
                        </td>
                        <td rowspan="{{ $currentRowSpan }}" style="font-weight: 800; text-align: center; color: #082149; vertical-align: middle;">
                            {{ $currentTarget }}
                        </td>
                        <td rowspan="{{ $currentRowSpan }}" style="font-weight: 800; text-align: center; {{ $currentEfficiency < 85 ? 'color: #d62718;' : 'color: #07b54d; vertical-align: middle;' }}">
                            {{ $currentEfficiency }} %
                        </td>
                        <td rowspan="{{ $currentRowSpan }}" style="text-align: center; vertical-align: middle;">
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
        @php
            $summaryEfficiency = $summaryMinsAvail > 0 ? round($summaryMinsProd/$summaryMinsAvail*100, 2) : 0;
            $targetFromEfficiency = $summaryMinsAvail > 0 ? (($summaryMinsProd/$summaryMinsAvail) > 0 ? floor($summaryActual / ($summaryMinsProd/$summaryMinsAvail)) : 0) : 0;
        @endphp
        <tr>
            <th colspan="13" style="font-weight: 800; text-align: center;">Summary</th>
            <th style="font-weight: 800; text-align: center;">{{ $summaryActual }}</th>
            <th style="font-weight: 800; text-align: center;">{{ ($targetFromEfficiency)}}</th>
            <th style="text-align: center; {{  $summaryEfficiency < 85 ? 'color: #07b54d;' : 'color: #d62718;' }}">{{ $summaryEfficiency }} %</th>
            <td style="text-align: center;">{{ $lastInput }}</td>
        </tr>
    @endif

    {{-- Group By WS --}}
    @if ($group == 'ws')
        <tr>
            <th rowspan="2" style="vertical-align: middle; text-align: center;">WS Number</th>
            <th rowspan="2" style="vertical-align: middle; text-align: center;">Style</th>
            <th rowspan="2" style="vertical-align: middle; text-align: center;">Line</th>
            <th rowspan="2" style="vertical-align: middle; text-align: center;">NIK</th>
            <th rowspan="2" style="vertical-align: middle; text-align: center;">Leader</th>
            <th colspan="5" style="text-align: center;">Output</th>
            <th colspan="3" style="text-align: center;">Rate</th>
            <th colspan="3" style="text-align: center;">Total</th>
            <th rowspan="2" style="vertical-align: middle; text-align: center;">Last Input</th>
        </tr>
        <tr>
            <th style="text-align: center;">RFT</th>
            <th style="text-align: center;">Defect</th>
            <th style="text-align: center;">Rework</th>
            <th style="text-align: center;">Reject</th>
            <th style="text-align: center;">Actual</th>
            <th style="text-align: center;">RFT</th>
            <th style="text-align: center;">Defect</th>
            <th style="text-align: center;">Reject</th>
            <th style="text-align: center;">Actual</th>
            <th style="text-align: center;">Target</th>
            <th style="text-align: center;">Efficiency</th>
        </tr>
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
                <td style="text-align: center;" colspan="14">
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
                <tr>
                    @if ($currentIdWs != $order->id_ws || $currentStyle != $order->styleno)
                        <td rowspan="{{ $currentRowSpan }}" style="vertical-align: middle;">{{ $order->kpno }}</td>
                        <td rowspan="{{ $currentRowSpan }}" style="vertical-align: middle;">{{ $order->styleno }}</td>
                    @endif
                    <td>
                        <a style="text" href="http://10.10.5.62:8000/dashboard-wip/line/dashboard1/{{ $order->username }}" target="_blank">
                            {{ ucfirst(str_replace("_", " ", $order->username)) }}
                        </a>
                    </td>
                    <td>
                        {{ $order->leader_nik }}
                    </td>
                    <td>
                        {{ $order->leader_name }}
                    </td>
                    <td style="font-weight: 800; text-align: center;">
                        {{ $order->rft < 1 ? '0' : $order->rft }}
                    </td>
                    <td style="font-weight: 800; text-align: center;">
                        {{ $order->defect < 1 ? '0' : $order->defect }}
                    </td>
                    <td style="font-weight: 800; text-align: center;">
                        {{ $order->rework < 1 ? '0' : $order->rework }}
                    </td>
                    <td style="font-weight: 800; text-align: center;">
                        {{ $order->reject < 1 ? '0' : $order->reject }}
                    </td>
                    <td style="font-weight: 800; text-align: center; color: #082149;">
                        {{ $order->total_actual < 1 ? '0' : $order->total_actual }}
                    </td>
                    <td style="font-weight: 800; text-align: center; {{ $rateRft < 97 ? 'color: #d62718;' : '' }}">
                        {{ $rateRft }} %
                    </td>
                    <td style="font-weight: 800; text-align: center; {{ $rateDefect > 3 ? 'color: #d62718;' : 'color: #07b54d;' }}">
                        {{ $rateDefect }} %
                    </td>
                    <td style="font-weight: 800; text-align: center; {{ $rateReject > 0 ? 'color: #d62718;' : 'color: #07b54d;' }}">
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
                        <td rowspan="{{ $currentRowSpan }}" style="font-weight: 800; text-align: center; color: #082149;vertical-align: middle;">
                            {{ $currentActual }}
                        </td>
                        <td rowspan="{{ $currentRowSpan }}" style="font-weight: 800; text-align: center; color: #082149;vertical-align: middle;">
                            {{ $currentTarget }}
                        </td>
                        <td rowspan="{{ $currentRowSpan }}" style="font-weight: 800; text-align: center; vertical-align: middle; {{ $currentEfficiency < 85 ? 'color: #d62718;' : 'color: #07b54d;' }}">
                            {{ $currentEfficiency }} %
                        </td>
                        <td rowspan="{{ $currentRowSpan }}" style="text-align: center; vertical-align: middle;">
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
        @php
            $summaryEfficiency = $summaryMinsAvail > 0 ? round($summaryMinsProd/$summaryMinsAvail*100, 2) : 0;
            $targetFromEfficiency = $summaryMinsAvail > 0 ? (($summaryMinsProd/$summaryMinsAvail) > 0 ? floor($summaryActual / ($summaryMinsProd/$summaryMinsAvail)) : 0) : 0;
        @endphp
        <tr>
            <th colspan="13" style="font-weight: 800; text-align: center;">Summary</th>
            <th style="font-weight: 800; text-align: center;">{{ $summaryActual }}</th>
            <th style="font-weight: 800; text-align: center;">{{ $targetFromEfficiency }}</th>
            <th style="text-align: center; {{  $summaryEfficiency < 85 ? 'color: #07b54d;' : 'color: #d62718;' }}">{{ $summaryEfficiency }} %</th>
            <td style="text-align: center;">{{ $lastInput }}</td>
        </tr>
    @endif

    {{-- Group By Style --}}
    @if ($group == 'style')
        <tr>
            <th rowspan="2" style="vertical-align: middle; text-align: center;">Style</th>
            <th rowspan="2" style="vertical-align: middle; text-align: center;">Line</th>
            <th rowspan="2" style="vertical-align: middle; text-align: center;">NIK Leader</th>
            <th rowspan="2" style="vertical-align: middle; text-align: center;">Leader</th>
            <th colspan="5" style="vertical-align: middle; text-align: center;">Output</th>
            <th colspan="3" style="vertical-align: middle; text-align: center;">Rate</th>
            <th colspan="3" style="vertical-align: middle; text-align: center;">Total</th>
            <th rowspan="2" style="vertical-align: middle; text-align: center;">Last Input</th>
        </tr>
        <tr>
            <th style="text-align: center;">RFT</th>
            <th style="text-align: center;">Defect</th>
            <th style="text-align: center;">Rework</th>
            <th style="text-align: center;">Reject</th>
            <th style="text-align: center;">Actual</th>
            <th style="text-align: center;">RFT</th>
            <th style="text-align: center;">Defect</th>
            <th style="text-align: center;">Reject</th>
            <th style="text-align: center;">Actual</th>
            <th style="text-align: center;">Target</th>
            <th style="text-align: center;">Efficiency</th>
        </tr>
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
                <td style="text-align: center;" colspan="14">
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
                <tr>
                    @if ($currentStyle != $order->styleno)
                        <td rowspan="{{ $currentRowSpan }}" style="vertical-align: middle;">{{ $order->styleno }}</td>
                    @endif
                    <td>
                        <a style="text" href="http://10.10.5.62:8000/dashboard-wip/line/dashboard1/{{ $order->username }}" target="_blank">
                            {{ ucfirst(str_replace("_", " ", $order->username)) }}
                        </a>
                    </td>
                    <td>
                        {{ $order->leader_nik }}
                    </td>
                    <td>
                        {{ $order->leader_name }}
                    </td>
                    <td style="font-weight: 800; text-align: center;">
                        {{ $order->rft < 1 ? '0' : $order->rft }}
                    </td>
                    <td style="font-weight: 800; text-align: center;">
                        {{ $order->defect < 1 ? '0' : $order->defect }}
                    </td>
                    <td style="font-weight: 800; text-align: center;">
                        {{ $order->rework < 1 ? '0' : $order->rework }}
                    </td>
                    <td style="font-weight: 800; text-align: center;">
                        {{ $order->reject < 1 ? '0' : $order->reject }}
                    </td>
                    <td style="font-weight: 800; text-align: center; color: #082149;">
                        {{ $order->total_actual < 1 ? '0' : $order->total_actual }}
                    </td>
                    <td style="font-weight: 800; text-align: center; {{ $rateRft < 97 ? 'color: #d62718;' : 'color: #07b54d;' }}">
                        {{ $rateRft }} %
                    </td>
                    <td style="font-weight: 800; text-align: center; {{ $rateDefect > 3 ? 'color: #d62718;' : 'color: #07b54d;' }}">
                        {{ $rateDefect }} %
                    </td>
                    <td style="font-weight: 800; text-align: center; {{ $rateReject > 0 ? 'color: #d62718;' : 'color: #07b54d;' }}">
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
                        <td rowspan="{{ $currentRowSpan }}" style="font-weight: 800; text-align: center; color: #082149; vertical-align: middle;">
                            {{ $currentActual }}
                        </td>
                        <td rowspan="{{ $currentRowSpan }}" style="font-weight: 800; text-align: center; color: #082149; vertical-align: middle;">
                            {{ $currentTarget }}
                        </td>
                        <td rowspan="{{ $currentRowSpan }}" style="font-weight: 800; text-align: center; vertical-align: middle; {{ $currentEfficiency < 85 ? 'color: #d62718;' : 'color: #07b54d;' }}">
                            {{ $currentEfficiency }} %
                        </td>
                        <td rowspan="{{ $currentRowSpan }}" style="text-align: center; vertical-align: middle;">
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
        @php
            $summaryEfficiency = $summaryMinsAvail > 0 ? round($summaryMinsProd/$summaryMinsAvail*100, 2) : 0;
            $targetFromEfficiency =$summaryMinsAvail > 0 ? (($summaryMinsProd/$summaryMinsAvail) > 0 ? floor($summaryActual / ($summaryMinsProd/$summaryMinsAvail)) : 0) : 0;
        @endphp
        <tr>
            <th colspan="12" style="font-weight: 800; text-align: center;">Summary</th>
            <th style="font-weight: 800; text-align: center;">{{ $summaryActual }}</th>
            <th style="font-weight: 800; text-align: center;">{{ $targetFromEfficiency }}</th>
            <th style="text-align: center; {{  $summaryEfficiency < 85 ? 'color: #d62718;' : 'color: #07b54d;' }}">{{ $summaryEfficiency }} %</th>
            <td style="text-align: center;">{{ $lastInput }}</td>
        </tr>
    @endif
</table>

<table>
    <tr>
        <td></td>
    </tr>
</table>

<table>
    <thead>
        <tr>
            <th style="text-align: center;" colspan="2">Defect Types</th>
            <th style="text-align: center;" colspan="2">Defect Areas</th>
            <th style="text-align: center;" colspan="2">Line</th>
        </tr>
    </thead>
    <tbody>
        @if ($defectTypes->count() < 1)
            <tr>
                <td colspan="6" style="text-align: center;">Data not found</td>
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
                    <td {{ $typeRowspan > 1 ? 'rowspan='.$typeRowspan : '' }} style="vertical-align: middle;">
                        {{ $type->defect_type }}
                    </td>
                    <td {{ $typeRowspan > 1 ? 'rowspan='.$typeRowspan : '' }} style="text-align: center; vertical-align: middle;" data-format="0">
                        <b>{{$type->defect_type_count}}</b>
                    </td>
                    <td {{ $lineDefectsFilteredAreaFirstCol->count() > 1 ? 'rowspan='.($lineDefectsFilteredAreaFirstCol->count()) : '' }} style="vertical-align: middle;">
                        <div>
                            {{ $defectAreasFiltered->first()->defect_area }}
                        </div>
                    </td>
                    <td {{ $lineDefectsFilteredAreaFirstCol->count() > 1 ? 'rowspan='.($lineDefectsFilteredAreaFirstCol->count()) : '' }} style="text-align: center; vertical-align: middle;" data-format="0">
                        <b>{{  $defectAreasFiltered->first()->defect_area_count }}</b>
                    </td>
                    <td>
                        {{ $firstLineDefectsFilteredArea->sewing_line }}
                    </td>
                    <td style="text-align: center;">
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
                                <td style="text-align: center;" data-format="0">
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
                                <td {{ $lineDefectAreasFilteredNextCol->count() > 1 ? 'rowspan='.$lineDefectAreasFilteredNextCol->count() : '' }} style="vertical-align: middle;">
                                    {{ $area->defect_area }}
                                </td>
                                <td {{ $lineDefectAreasFilteredNextCol->count() > 1 ? 'rowspan='.$lineDefectAreasFilteredNextCol->count() : '' }} style="text-align: center; vertical-align: middle;" data-format="0">
                                    <b>{{ $area->defect_area_count }}</b>
                                </td>
                                <td>
                                    {{ $lineDefectAreasFilteredNextCol->first()->sewing_line }}
                                </td>
                                <td style="text-align: center;" data-format="0">
                                    <b>{{ $lineDefectAreasFilteredNextCol->first()->total }}</b>
                                </td>
                            </tr>

                            @if ($lineDefectAreasFilteredNextCol->count() > 1)
                                @foreach ($lineDefectAreasFilteredNextCol as $line)
                                    @if ($loop->index > 0)
                                        <tr>
                                            <td>
                                                {{ $line->sewing_line }}
                                            </td>
                                            <td style="text-align: center;" data-format="0">
                                                <b>{{  $line->total }}</b>
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
