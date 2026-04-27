<!DOCTYPE html>
<html>

<body>
    <table>
        <thead>
            <tr>
                <th colspan="18"> Report Defect &amp; Reject</th>
            </tr>
            <tr><th colspan="18"> Periode : {{ date('d-m-Y', strtotime($startDate)) }} s/d {{ date('d-m-Y', strtotime($endDate)) }}</th></tr>
            <tr>
                <th colspan="18">
                    Department :
                    {{
                        empty($department) ? 'All' :
                        ($department == '_end' ? 'END-LINE' :
                        ($department == '_packing' ? 'FINISHING-LINE' : ''))
                    }}
                </th>
            </tr>
            <tr><th colspan="18"> No. WS : {{ empty($ws) ? "All" : $ws }}</th></tr>
            <tr><th colspan="18"></th></tr>

            <tr>
                <th class="text-center align-middle">Kode Numbering</th>
                <th class="text-center align-middle">Buyer</th>
                <th class="text-center align-middle">No WS</th>
                <th class="text-center align-middle">Style</th>
                <th class="text-center align-middle">Color</th>
                <th class="text-center align-middle">Size</th>
                <th class="text-center align-middle">Dest</th>
                <th class="text-center align-middle">Sewing Line</th>
                <th class="text-center align-middle">Dept</th>
                <th class="text-center align-middle">Defect Type</th>
                <th class="text-center align-middle">Defect Area</th>
                <th class="text-center align-middle">Status</th>
                <th class="text-center align-middle">Tgl Defect</th>
                <th class="text-center align-middle">Tgl Rework</th>
                <th class="text-center align-middle">Proses Type</th>
                <th class="text-center align-middle">Proses Status</th>
                <th class="text-center align-middle">Tgl Proses In</th>
                <th class="text-center align-middle">Tgl Proses Out</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rawData as $row)
                <tr>
                    <td>{{ $row->kode_numbering }}</td>
                    <td>{{ $row->buyer }}</td>
                    <td>{{ $row->ws }}</td>
                    <td>{{ $row->style }}</td>
                    <td>{{ $row->color }}</td>
                    <td>{{ $row->size }}</td>
                    <td>{{ $row->dest }}</td>
                    <td>{{ $row->sewing_line }}</td>
                    <td>{{ $row->dept }}</td>
                    <td>{{ $row->defect_type }}</td>
                    <td>{{ $row->defect_area }}</td>
                    <td>{{ ucwords(strtolower($row->status)) }}</td>
                    <td>{{ $row->tgl_defect }}</td>
                    <td>{{ $row->tgl_rework }}</td>
                    <td>{{ $row->proses_type }}</td>
                    <td>{{ ucwords(strtolower($row->proses_status)) }}</td>
                    <td>{{ $row->tgl_proses_in }}</td>
                    <td>{{ $row->tgl_proses_out }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
