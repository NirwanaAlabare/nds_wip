<!DOCTYPE html>
<html>

<body>
    <table>
        <thead>
            <tr>
                <th colspan="20"> Report Return Fabric Cutting</th>
            </tr>
            <tr><th colspan="20"> Periode {{ date('d-m-Y', strtotime($startDate)) }} s/d {{ date('d-m-Y', strtotime($endDate)) }}</th></tr>
            <tr><th colspan="20"></th></tr>

            <tr>
                <th class="text-center align-middle">Tanggal Keluar</th>
                <th class="text-center align-middle">No Barcode</th>
                <th class="text-center align-middle">Qty BPB</th>
                <th class="text-center align-middle">Unit</th>
                <th class="text-center align-middle">Qty Konv</th>
                <th class="text-center align-middle">Unit Konv</th>
                <th class="text-center align-middle">Rak</th>
                <th class="text-center align-middle">No BPB</th>
                <th class="text-center align-middle">No SJ</th>
                <th class="text-center align-middle">Supplier</th>
                <th class="text-center align-middle">No WS</th>
                <th class="text-center align-middle">No WS Aktual</th>
                <th class="text-center align-middle">ID Item</th>
                <th class="text-center align-middle">Style</th>
                <th class="text-center align-middle">Warna</th>
                <th class="text-center align-middle">No Lot</th>
                <th class="text-center align-middle">No Roll</th>
                <th class="text-center align-middle">No Roll Buyer</th>
                <th class="text-center align-middle">Created By</th>
                <th class="text-center align-middle">Created At</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rawData as $row)
                <tr>
                    <td>{{ $row->tanggal_keluar }}</td>
                    <td>{{ $row->no_barcode }}</td>
                    <td class="text-end">{{ $row->qty_aktual }}</td>
                    <td>{{ $row->satuan }}</td>
                    <td class="text-end">{{ $row->qty_konv }}</td>
                    <td>{{ $row->satuan_konv }}</td>
                    <td>{{ $row->rak }}</td>
                    <td>{{ $row->no_dok }}</td>
                    <td>{{ $row->no_invoice }}</td>
                    <td>{{ $row->supplier }}</td>
                    <td>{{ $row->no_ws }}</td>
                    <td>{{ $row->ws_aktual }}</td>
                    <td>{{ $row->id_item }}</td>
                    <td>{{ $row->styleno }}</td>
                    <td>{{ $row->warna }}</td>
                    <td>{{ $row->no_lot }}</td>
                    <td class="text-end">{{ $row->no_roll }}</td>
                    <td>{{ $row->no_roll_buyer }}</td>
                    <td>{{ $row->created_by }}</td>
                    <td>{{ $row->created_at }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
