<!DOCTYPE html>
<html>

<body>
    <table>
        <thead>
            <tr>
                <th colspan="13"> Report GR Panel Detail</th>
                {{-- <th colspan="100">LAPORAN DAILY COST {{ date('d-M-Y', strtotime($from)) }} -
                {{ date('d-M-Y', strtotime($to)) }}</th> --}}
            </tr>
            <tr>

                <th class="text-center align-middle">Tanggal</th>
                <th class="text-center align-middle">No. Form</th>
                <th class="text-center align-middle">Tujuan</th>
                <th class="text-center align-middle">ID Item</th>
                <th class="text-center align-middle">Item</th>
                <th class="text-center align-middle">Barcode</th>
                <th class="text-center align-middle">Panel</th>
                <th class="text-center align-middle">WS</th>
                <th class="text-center align-middle">Style</th>
                <th class="text-center align-middle">Color</th>
                <th class="text-center align-middle">Part</th>
                <th class="text-center align-middle">Size</th>
                <th class="text-center align-middle">Qty</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rawData as $row)
                <tr>
                    <td>{{ $row->tgl_form_fix }}</td>
                    <td>{{ $row->no_form }}</td>
                    <td>{{ $row->tujuan }}</td>
                    <td>{{ $row->id_item }}</td>
                    <td>{{ $row->itemdesc }}</td>
                    <td>{{ $row->barcode }}</td>
                    <td>{{ $row->nama_panel }}</td>
                    <td>{{ $row->ws }}</td>
                    <td>{{ $row->styleno }}</td>
                    <td>{{ $row->color }}</td>
                    <td>{{ $row->part }}</td>
                    <td>{{ $row->size }}</td>
                    <td class="text-end">{{ $row->qty }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
