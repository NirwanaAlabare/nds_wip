<!DOCTYPE html>
<html>

<body>
    <table>
        <thead>
            <tr>
                <th colspan="13"> Report Sewing Finishing Proses</th>
            </tr>
            <tr><th colspan="13"> Periode : {{ date('d-m-Y', strtotime($startDate)) }} s/d {{ date('d-m-Y', strtotime($endDate)) }}</th></tr>
            <tr><th colspan="13"> Proses : {{ empty($proses) ? "All" : $proses }}</th></tr>
            <tr><th colspan="13"></th></tr>

            <tr>
                <th class="text-center align-middle">Proses</th>
                <th class="text-center align-middle">Line</th>
                <th class="text-center align-middle">Buyer</th>
                <th class="text-center align-middle">WS</th>
                <th class="text-center align-middle">Style</th>
                <th class="text-center align-middle">Color</th>
                <th class="text-center align-middle">Size</th>
                <th class="text-center align-middle">WIP</th>
                <th class="text-center align-middle">In</th>
                <th class="text-center align-middle">Defect</th>
                <th class="text-center align-middle">Rework</th>
                <th class="text-center align-middle">Reject</th>
                <th class="text-center align-middle">Output</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rawData as $row)
                @php
                    $totalWip = collect($rawData)->sum('wip');
                    $totalIn = collect($rawData)->sum('in');
                    $totalDefect = collect($rawData)->sum('defect');
                    $totalRework = collect($rawData)->sum('rework');
                    $totalReject = collect($rawData)->sum('reject');
                    $totalOutput = collect($rawData)->sum('output');
                @endphp
                <tr>
                    <td>{{ $row->proses }}</td>
                    <td>{{ $row->line }}</td>
                    <td>{{ $row->buyer }}</td>
                    <td>{{ $row->no_ws }}</td>
                    <td>{{ $row->style }}</td>
                    <td>{{ $row->color }}</td>
                    <td>{{ $row->size }}</td>
                    <td>{{ $row->wip }}</td>
                    <td>{{ $row->in }}</td>
                    <td>{{ $row->defect }}</td>
                    <td>{{ $row->rework }}</td>
                    <td>{{ $row->reject }}</td>
                    <td>{{ $row->output }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="7" class="text-center align-middle">TOTAL</th>
                <th class="text-end">{{ $totalWip }}</th>
                <th class="text-end">{{ $totalIn }}</th>
                <th class="text-end">{{ $totalDefect }}</th>
                <th class="text-end">{{ $totalRework }}</th>
                <th class="text-end">{{ $totalReject }}</th>
                <th class="text-end">{{ $totalOutput }}</th>
            </tr>
        </tfoot>
    </table>
</body>

</html>
