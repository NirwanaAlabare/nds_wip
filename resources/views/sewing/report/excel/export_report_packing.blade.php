<!DOCTYPE html>
<html>

<body>
    <table>
        <thead>
            <tr>
                <th colspan="6"> Report Packing</th>
            </tr>
            <tr><th colspan="6"> Periode : {{ date('d-m-Y', strtotime($startDate)) }} s/d {{ date('d-m-Y', strtotime($endDate)) }}</th></tr>
            <tr><th colspan="6"> Buyer : {{ empty($buyer) ? "Semua Buyer" : $buyer }}</th></tr>
            <tr><th colspan="6"></th></tr>

            <tr>
                <th class="text-center align-middle">Buyer</th>
                <th class="text-center align-middle">WS</th>
                <th class="text-center align-middle">Style</th>
                <th class="text-center align-middle">Color</th>
                <th class="text-center align-middle">Size</th>
                <th class="text-center align-middle">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $row)
                <tr>
                    <td>{{ $row->buyer }}</td>
                    <td>{{ $row->ws }}</td>
                    <td>{{ $row->styleno }}</td>
                    <td>{{ $row->color }}</td>
                    <td>{{ $row->size }}</td>
                    <td>{{ $row->jumlah }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
