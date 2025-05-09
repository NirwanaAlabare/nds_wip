<table>
    <tr>
        <th colspan="14" style="font-weight: 800;">Defect Rate {{ $department == "_packing" ? "Packing" : "QC"}} {{ $dateFrom." s/d ".$dateTo }}</th>
    </tr>
    <tr>
        <th style="font-weight: 800;">Worksheet : {{ $ws }}</th>
    </tr>
    <tr>
        <th style="font-weight: 800;">Style : {{ $style }}</th>
    </tr>
    <tr>
        <th style="font-weight: 800;">Color : {{ $color }}</th>
    </tr>
    <tr>
        <th style="font-weight: 800;">Line : {{ $sewingLine }}</th>
    </tr>
    <tr>
        <th style="font-weight: 800; border: 1px solid #000;">Tanggal</th>
        <th style="font-weight: 800; border: 1px solid #000;">Style</th>
        <th style="font-weight: 800; border: 1px solid #000;">Buyer</th>
        <th style="font-weight: 800; border: 1px solid #000;">No. WS</th>
        <th style="font-weight: 800; border: 1px solid #000;">Color</th>
        <th style="font-weight: 800; border: 1px solid #000;">Line</th>
        <th style="font-weight: 800; border: 1px solid #000;">RFT</th>
        <th style="font-weight: 800; border: 1px solid #000;">Defect</th>
        <th style="font-weight: 800; border: 1px solid #000;">Rework</th>
        <th style="font-weight: 800; border: 1px solid #000;">Reject</th>
        <th style="font-weight: 800; border: 1px solid #000;">Output</th>
        <th style="font-weight: 800; border: 1px solid #000;">RFT Rate</th>
        <th style="font-weight: 800; border: 1px solid #000;">Defect Rate</th>
        <th style="font-weight: 800; border: 1px solid #000;">Reject Rate</th>
    </tr>
    @php
        $totalRft = 0;
        $totalDefect = 0;
        $totalRework = 0;
        $totalReject = 0;
        $totalOutput = 0;
    @endphp
    @foreach ($defectRates as $defectRate)
        <tr>
            <td style="border: 1px solid #000;">{{ $defectRate->tgl_output }}</td>
            <td style="border: 1px solid #000;">{{ $defectRate->style }}</td>
            <td style="border: 1px solid #000;">{{ $defectRate->buyer }}</td>
            <td style="border: 1px solid #000;">{{ $defectRate->ws }}</td>
            <td style="border: 1px solid #000;">{{ $defectRate->color }}</td>
            <td style="border: 1px solid #000;">{{ $defectRate->sewing_line }}</td>
            <td style="border: 1px solid #000;">{{ $defectRate->rft }}</td>
            <td style="border: 1px solid #000;">{{ $defectRate->defect }}</td>
            <td style="border: 1px solid #000;">{{ $defectRate->rework }}</td>
            <td style="border: 1px solid #000;">{{ $defectRate->reject }}</td>
            <td style="border: 1px solid #000;">{{ $defectRate->output }}</td>
            <td style="border: 1px solid #000;">{{ $defectRate->rft_rate }}</td>
            <td style="border: 1px solid #000;">{{ $defectRate->defect_rate }}</td>
            <td style="border: 1px solid #000;">{{ $defectRate->reject_rate }}</td>

            @php
                $totalRft += $defectRate->rft;
                $totalDefect += $defectRate->defect+$defectRate->rework;
                $totalReject += $defectRate->reject;
                $totalOutput += $defectRate->output;
            @endphp
        </tr>
    @endforeach
    <tr>
        <td style="font-weight: 800; border: 1px solid #000;" colspan="6">Total</td>
        <td style="font-weight: 800; border: 1px solid #000;">{{ $totalRft }}</td>
        <td style="font-weight: 800; border: 1px solid #000;">{{ $totalDefect }}</td>
        <td style="font-weight: 800; border: 1px solid #000;">{{ $totalRework }}</td>
        <td style="font-weight: 800; border: 1px solid #000;">{{ $totalReject }}</td>
        <td style="font-weight: 800; border: 1px solid #000;">{{ $totalOutput }}</td>
        <td style="font-weight: 800; border: 1px solid #000;">{{ $totalRft ? round($totalRft / $totalOutput * 100, 2) : '0' }}</td>
        <td style="font-weight: 800; border: 1px solid #000;">{{ $totalDefect ? round($totalDefect / $totalOutput * 100, 2) : '0' }}</td>
        <td style="font-weight: 800; border: 1px solid #000;">{{ $totalReject ? round($totalReject / $totalOutput * 100, 2) : '0' }}</td>
    </tr>
</table>
