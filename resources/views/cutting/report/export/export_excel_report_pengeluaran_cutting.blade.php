<!DOCTYPE html>
<html>

<body>
    <table>
        <thead>
            <tr>
                <th colspan="9"> Report Pengeluaran Cutting</th>
            </tr>
            <tr>
                <th class="text-center align-middle">Tanggal</th>
                <th class="text-center align-middle">No Form</th>
                <th class="text-center align-middle">No Cut</th>
                <th class="text-center align-middle">Worksheet</th>
                <th class="text-center align-middle">Buyer</th>
                <th class="text-center align-middle">Style</th>
                <th class="text-center align-middle">Color</th>
                <th class="text-center align-middle">Size</th>
                <th class="text-center align-middle">Panel</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rawData as $row)
                <tr>
                    <td>{{ $row->created_at }}</td>
                    <td>{{ $row->no_form }}</td>
                    <td class="text-end">{{ $row->no_cut }}</td>
                    <td>{{ $row->ws }}</td>
                    <td>{{ $row->buyer }}</td>
                    <td>{{ $row->styleno }}</td>
                    <td>{{ $row->color }}</td>
                    <td>{{ $row->size }}</td>
                    <td>{{ $row->panel }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
