<!DOCTYPE html>
<html>

<body>
    <table>
        <thead>
            <tr>
                <th colspan="100"> Report Penerimaan Fabric Cutting</th>
                {{-- <th colspan="100">LAPORAN DAILY COST {{ date('d-M-Y', strtotime($from)) }} -
                    {{ date('d-M-Y', strtotime($to)) }}</th> --}}
            </tr>
            <tr>

                <th class="text-center align-middle">No. BPPB</th>
                <th class="text-center align-middle">Tgl. BPPB</th>
                <th class="text-center align-middle">No. Req</th>
                <th class="text-center align-middle">Barcode</th>
                <th class="text-center align-middle">No. Roll</th>
                <th class="text-center align-middle">No. Roll Buyer</th>
                <th class="text-center align-middle">No. Lot</th>
                <th class="text-center align-middle">ID Item</th>
                <th class="text-center align-middle">No. WS</th>
                <th class="text-center align-middle">No. WS Act</th>
                <th class="text-center align-middle">Style</th>
                <th class="text-center align-middle">Nama Barang</th>
                <th class="text-center align-middle">Warna</th>
                <th class="text-center align-middle">Qty Out</th>
                <th class="text-center align-middle">Satuan</th>
                <th class="text-center align-middle">Qty Konv</th>
                <th class="text-center align-middle">Satuan Konv</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rawData as $row)
                <tr>
                    <td>{{ $row->no_bppb }}</td>
                    <td>{{ $row->tgl_bppb_fix }}</td>
                    <td>{{ $row->no_req }}</td>
                    <td>{{ $row->id_roll }}</td>
                    <td class="text-end">{{ $row->no_roll }}</td>
                    <td class="text-end">{{ $row->no_roll_buyer }}</td>
                    <td class="text-end">{{ $row->no_lot }}</td>
                    <td class="text-end">{{ $row->id_item }}</td>
                    <td class="text-end">{{ $row->no_ws }}</td>
                    <td class="text-end">{{ $row->no_ws_aktual }}</td>
                    <td class="text-end">{{ $row->styleno }}</td>
                    <td class="text-end">{{ $row->itemdesc }}</td>
                    <td class="text-end">{{ $row->color }}</td>
                    <td class="text-end">{{ $row->qty_out }}</td>
                    <td class="text-end">{{ $row->satuan }}</td>
                    <td class="text-end">{{ $row->qty_out_konversi }}</td>
                    <td class="text-end">{{ $row->satuan_konversi }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
