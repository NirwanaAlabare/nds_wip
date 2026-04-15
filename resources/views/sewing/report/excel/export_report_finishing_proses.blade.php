<!DOCTYPE html>
<html>

<body>
    <table>
        <thead>
            <tr>
                <th colspan="6"> Report Finishing Proses</th>
            </tr>
            <tr><th colspan="6"> Periode : {{ date('d-m-Y', strtotime($startDate)) }} s/d {{ date('d-m-Y', strtotime($endDate)) }}</th></tr>
            <tr><th colspan="6"> Department : {{ empty($department) ? "END-LINE" : "FINISHING-LINE" }} </th></tr>
            <tr><th colspan="6"> No. WS : {{ empty($ws) ? "Semua" : $ws }}</th></tr>
            <tr><th colspan="6"></th></tr>

            <tr>
                <th class="text-center align-middle">Kode Numbering</th>
                <th class="text-center align-middle">Buyer</th>
                <th class="text-center align-middle">No WS</th>
                <th class="text-center align-middle">Style</th>
                <th class="text-center align-middle">Color</th>
                <th class="text-center align-middle">Size</th>
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
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
