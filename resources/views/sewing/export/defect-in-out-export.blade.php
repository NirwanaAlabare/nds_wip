<table>
    <tr>
        <td style="text-align: center;">Dari : {{ $dateFrom }}</td>
        <td style="text-align: center;">Sampai : {{ $dateTo }}</td>
        <td style="text-align: center;">{{ $outputType == "qcf" ? "QC FINISHING" : $outputType }}</td>
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
        $defectInOutListGroup = $defectInOutList->groupBy("defect_type");
        $defectInOutListSort = $defectInOutListGroup->mapWithKeys(function ($group, $key) {
            return [
                $key =>
                    [
                        'defect_type' => $key, // $key is what we grouped by, it'll be constant by each  group of rows
                        'total_defect' => $group->sum('defect_qty'),
                        'data' => $group,
                    ]
            ];
        });

        $summaryDefectQty = $defectInOutList->sum("defect_qty");
    @endphp
    @foreach ($defectInOutListSort->sortByDesc("total_defect") as $key => $value)
        <tr>
            <td style="border: 1px solid black;">{{ $key }}</td>
            <td style="border: 1px solid black;">{{ $value["total_defect"] }}</td>
            <td style="border: 1px solid black;">{{ round(($value["total_defect"]/($outputAll ? $outputAll->sum('total_output') : 1) * 100), 2) }} %</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    @endforeach
    <tr>
        <th style="border: 1px solid black;">TOTAL</th>
        <th style="border: 1px solid black;background: yellow;">{{ $summaryDefectQty }}</th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
    </tr>
    <tr></tr>
    <tr>
        <th style="font-weight: 800;" colspan="6">{{ strtoupper(str_replace("_", "", $type)) }} - LINE</th>
    </tr>
    <tr>
        <th style="border: 1px solid black;vertical-align: middle; text-align: center; font-weight: 800;">DEFECT TYPE</th>
        <th style="border: 1px solid black;vertical-align: middle; text-align: center; font-weight: 800;">LINE</th>
        <th style="border: 1px solid black;vertical-align: middle; text-align: center; font-weight: 800;">QTY</th>
        <th style="border: 1px solid black;vertical-align: middle; text-align: center; font-weight: 800;">DEFECT RATE</th>
        <th></th>
        <th></th>
    </tr>
    @if ($defectInOutList->count() < 1)
        <tr>
            <td style="border: 1px solid black;" colspan="4">
                Data not found
            </td>
            <td></td>
            <td></td>
        </tr>
    @else
        @php
            $defectInOutListGroup = $defectInOutList->groupBy("defect_type");
            $defectInOutListSort = $defectInOutListGroup->mapWithKeys(function ($group, $key) {
                return [
                    $key =>
                        [
                            'defect_type' => $key, // $key is what we grouped by, it'll be constant by each  group of rows
                            'total_defect' => $group->sum('defect_qty'),
                            'data' => $group,
                        ]
                ];
            });
        @endphp
        @foreach ($defectInOutListSort->sortByDesc("total_defect") as $key => $value)
            @php
                $defectInOutSewing = $value['data']->sortBy("FullName")->groupBy("FullName");
                $defectInOutSewingSort = $defectInOutSewing->mapWithKeys(function ($group, $key) {
                    return [
                        $key =>
                            [
                                'sewing_name' => $key, // $key is what we grouped by, it'll be constant by each  group of rows
                                'total_defect' => $group->sum('defect_qty'),
                                'data' => $group,
                            ]
                    ];
                });
            @endphp
            @foreach ($defectInOutSewingSort->sortByDesc("total_defect") as $k => $val)
                <tr>
                    <td style="border: 1px solid black;">{{ $key }}</td>
                    <td style="border: 1px solid black;">{{ $k }}</td>
                    <td style="border: 1px solid black;">{{ $val["total_defect"] }}</td>
                    <td style="border: 1px solid black;">{{ round(($val["total_defect"]/($outputAll && $outputAll->where("line", $k)->sum('total_output') > 0 ? $outputAll->where("line", $k)->sum('total_output') : 1)*100), 2) }} %</td>
                    <td></td>
                    <td></td>
                </tr>
            @endforeach
            <tr>
                <th style="border: 1px solid black;" colspan="2">TOTAL</th>
                <th style="border: 1px solid black;background: yellow;">{{ $value["total_defect"] }}</th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        @endforeach
    @endif
    <tr></tr>
    <tr>
        <th style="font-weight: 800;" colspan="6">{{ strtoupper(str_replace("_", "", $type)) }} - STYLE</th>
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
            <td style="border: 1px solid black;" colspan="6">
                Data not found
            </td>
        </tr>
    @else
        @foreach ($defectInOutListSort->sortByDesc("total_defect") as $key => $value)
            @php
                $defectInOutStyle = $value['data']->sortBy("kpno")->groupBy("kpno", "style", "color");
                $defectInOutStyleSort = $defectInOutStyle->map(function ($group) {
                    return [
                        'kpno' => $group->first()->kpno, // $key is what we grouped by, it'll be constant by each  group of rows
                        'styleno' => $group->first()->styleno, // $key is what we grouped by, it'll be constant by each  group of rows
                        'color' => $group->first()->color, // $key is what we grouped by, it'll be constant by each  group of rows
                        'total_defect' => $group->sum('defect_qty'),
                        'data' => $group,
                    ];
                });

                $totalDefect = 0;
            @endphp
            @foreach ($defectInOutStyleSort->sortByDesc("total_defect") as $val)
                <tr>
                    <td style="border: 1px solid black;">{{ $key }}</td>
                    <td style="border: 1px solid black;">{{ $val['kpno'] }}</td>
                    <td style="border: 1px solid black;">{{ $val['styleno'] }}</td>
                    <td style="border: 1px solid black;">{{ $val['color'] }}</td>
                    <td style="border: 1px solid black;">{{ $val['total_defect'] }}</td>
                    <td style="border: 1px solid black;">{{ round(($val['total_defect']/($outputAll && $outputAll->where("style", $val['styleno'])->sum('total_output') > 0 ? $outputAll->where("style", $val['styleno'])->sum('total_output') : 1)*100), 2) }} %</td>
                </tr>
            @endforeach
            <tr>
                <th style="border: 1px solid black;" colspan="4">TOTAL</th>
                <th style="border: 1px solid black;background: #ffea2b;">{{ $value['total_defect'] }}</th>
                <th></th>
            </tr>
        @endforeach
    @endif
</table>
