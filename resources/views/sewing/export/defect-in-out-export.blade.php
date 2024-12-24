<table>
    <tr>
        <td style="text-align: center;">Dari : {{ $dateFrom }}</td>
        <td style="text-align: center;">Sampai : {{ $dateTo }}</td>
    </tr>
    <tr>
        <td colspan="9" style="text-align: center; font-weight: 800;">{{ strtoupper(str_replace("_", "", $type)) }}</td>
    </tr>
    <tr>
        <th style="vertical-align: middle; text-align: center; font-weight: 800;">DATE IN</th>
        <th style="vertical-align: middle; text-align: center; font-weight: 800;">LINE</th>
        <th style="vertical-align: middle; text-align: center; font-weight: 800;">DEPARTMENT</th>
        <th style="vertical-align: middle; text-align: center; font-weight: 800;">WS</th>
        <th style="vertical-align: middle; text-align: center; font-weight: 800;">STYLE</th>
        <th style="vertical-align: middle; text-align: center; font-weight: 800;">COLOR</th>
        <th style="vertical-align: middle; text-align: center; font-weight: 800;">SIZE</th>
        <th style="vertical-align: middle; text-align: center; font-weight: 800;">TYPE</th>
        <th style="vertical-align: middle; text-align: center; font-weight: 800;">QTY</th>
    </tr>
    @php
        $summaryDefectQty = 0;
    @endphp
    @if ($defectInOutList->count() < 1)
        <tr>
            <td style="text-align: center;" colspan="14">
                Data not found
            </td>
        </tr>
    @else
        @foreach ($defectInOutList as $defect)
            @php
                $summaryDefectQty += $defect->defect_qty;
            @endphp
            <tr>
                <td>{{ $defect->updated_at }}</td>
                <td>{{ $defect->FullName }}</td>
                <td>{{ strtoupper($defect->output_type) }}</td>
                <td>{{ $defect->kpno }}</td>
                <td>{{ $defect->styleno }}</td>
                <td>{{ $defect->color }}</td>
                <td>{{ $defect->size }}</td>
                <td>{{ $defect->defect_type }}</td>
                <td>{{ $defect->defect_qty }}</td>
            </tr>
        @endforeach
    @endif
    <tr>
        <td colspan="8" class="fs-5 fw-bold text-center">Summary</td>
        <td class="fs-5 fw-bold text-center">{{ num($summaryDefectQty) }}</td>
    </tr>
</table>
