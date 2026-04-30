<!DOCTYPE html>
<html>

<body>
    <table>
        <thead>
            <tr>
                <th colspan="9"> Report Pengeluaran Cutting</th>
            </tr>
            <tr><th colspan="9"> Periode {{ date('d-m-Y', strtotime($startDate)) }} s/d {{ date('d-m-Y', strtotime($endDate)) }}</th></tr>
            <tr><th colspan="9"></th></tr>

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
                <th class="text-center align-middle">Panel Status</th>
                <th class="text-center align-middle">Part</th>
                <th class="text-center align-middle">Part Status</th>
                <th class="text-center align-middle">Qty</th>
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
                    <td>{{ $row->panel_status }}</td>
                    <td>{{ $row->nama_part }}</td>
                    <td>{{ $row->part_status }}</td>
                    <td class="text-end">{{ $row->qty_dc }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
