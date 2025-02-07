<table>
    <tr>
        <td style="text-align: center;">Dari : {{ $dateFrom }}</td>
        <td style="text-align: center;">Sampai : {{ $dateTo }}</td>
    </tr>
    <tr></tr>
    <tr>
        <td style="font-weight: 800;">{{ strtoupper(str_replace("_", "", $type)) }} - DEFECT TYPE</td>
    </tr>
    <tr>
        <th style="border: 1px solid black;vertical-align: middle; text-align: center; font-weight: 800;">DEFECT TYPE</th>
        <th style="border: 1px solid black;vertical-align: middle; text-align: center; font-weight: 800;">QTY</th>
        <th style="border: 1px solid black;vertical-align: middle; text-align: center; font-weight: 800;">DEFECT RATE</th>
    </tr>
    @php
        $summaryDefectQty = $defectInOutList->sum("defect_qty");
    @endphp
    @foreach ($defectInOutList->groupBy("defect_type") as $key => $value)
        <tr>
            <td style="border: 1px solid black;">{{ $key }}</td>
            <td style="border: 1px solid black;">{{ $value->sum("defect_qty") }}</td>
            <td style="border: 1px solid black;">{{ round(($value->sum("defect_qty")/($summaryDefectQty > 0 ? $summaryDefectQty : 1) * 100), 2) }} %</td>
        </tr>
    @endforeach
    <tr>
        <td style="font-weight: 800;border: 1px solid black;">TOTAL</td>
        <td style="font-weight: 800;background: #ffe70b;border: 1px solid black;">{{ $summaryDefectQty }}</td>
        <td style="border: 1px solid black;"></td>
    </tr>
    <tr></tr>
    <tr>
        <td style="font-weight: 800;">{{ strtoupper(str_replace("_", "", $type)) }} - LINE</td>
    </tr>
    <tr>
        <th style="border: 1px solid black;vertical-align: middle; text-align: center; font-weight: 800;">DEFECT TYPE</th>
        <th style="border: 1px solid black;vertical-align: middle; text-align: center; font-weight: 800;">LINE</th>
        <th style="border: 1px solid black;vertical-align: middle; text-align: center; font-weight: 800;">QTY</th>
        <th style="border: 1px solid black;vertical-align: middle; text-align: center; font-weight: 800;">DEFECT RATE</th>
    </tr>
    @if ($defectInOutList->count() < 1)
        <tr>
            <td style="text-align: center;border: 1px solid black;" colspan="4">
                Data not found
            </td>
        </tr>
    @else
        @foreach ($defectInOutList->groupBy("defect_type") as $key => $value)
            @php
                $totalDefect = 0;
            @endphp
            @foreach ($value->sortBy("FullName")->groupBy("FullName") as $k => $val)
                @php
                    $totalDefect += $val->sum("defect_qty");
                @endphp
                <tr>
                    <td style="border: 1px solid black;">{{ $key }}</td>
                    <td style="border: 1px solid black;">{{ $k }}</td>
                    <td style="border: 1px solid black;">{{ $val->sum("defect_qty") }}</td>
                    <td style="border: 1px solid black;">{{ round(($val->sum("defect_qty")/($value->sum("defect_qty") > 0 ? $value->sum("defect_qty") : 1)*100), 2) }} %</td>
                </tr>
            @endforeach
            <tr>
                <td style="font-weight: 800;border: 1px solid black;" colspan="2">TOTAL</td>
                <td style="font-weight: 800;background: #ffe70b;border: 1px solid black;">{{ $totalDefect }}</td>
                <td style="border: 1px solid black;"></td>
            </tr>
        @endforeach
    @endif
    <tr></tr>
    <tr>
        <td style="font-weight: 800;">{{ strtoupper(str_replace("_", "", $type)) }} - STYLE</td>
    </tr>
    <tr>
        <th style="border: 1px solid black;vertical-align: middle; text-align: center; font-weight: 800;">DEFECT TYPE</th>
        <th style="border: 1px solid black;vertical-align: middle; text-align: center; font-weight: 800;">WORKSHEET</th>
        <th style="border: 1px solid black;vertical-align: middle; text-align: center; font-weight: 800;">STYLE</th>
        <th style="border: 1px solid black;vertical-align: middle; text-align: center; font-weight: 800;">COLOR</th>
        <th style="border: 1px solid black;vertical-align: middle; text-align: center; font-weight: 800;">QTY</th>
        <th style="border: 1px solid black;vertical-align: middle; text-align: center; font-weight: 800;">DEFECT RATE</th>
    </tr>
    @if ($defectInOutList->count() < 1)
        <tr>
            <td style="text-align: center;border: 1px solid black;" colspan="6">
                Data not found
            </td>
        </tr>
    @else
        @foreach ($defectInOutList->groupBy("defect_type") as $key => $value)
            @php
                $totalDefect = 0;
            @endphp
            @foreach ($value->sortBy("kpno")->groupBy("kpno", "style", "color") as $val)
                @php
                    $totalDefect += $val->sum("defect_qty");
                @endphp
                <tr>
                    <td style="border: 1px solid black;">{{ $key }}</td>
                    <td style="border: 1px solid black;">{{ $val->first()->kpno }}</td>
                    <td style="border: 1px solid black;">{{ $val->first()->styleno }}</td>
                    <td style="border: 1px solid black;">{{ $val->first()->color }}</td>
                    <td style="border: 1px solid black;">{{ $val->sum("defect_qty") }}</td>
                    <td style="border: 1px solid black;">{{ round(($val->sum("defect_qty")/($value->sum("defect_qty") > 0 ? $value->sum("defect_qty") : 1)*100), 2) }} %</td>
                </tr>
            @endforeach
            <tr>
                <td style="font-weight: 800;border: 1px solid black;" colspan="4">TOTAL</td>
                <td style="font-weight: 800;background: #ffe70b;border: 1px solid black;">{{ $totalDefect }}</td>
                <td style="border: 1px solid black;"></td>
            </tr>
        @endforeach
    @endif
</table>
