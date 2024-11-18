<table>
    <tr>
        <th colspan="16">{{ ($line ? strtoupper(str_replace("_", " ", $line)) : 'Line') }} WIP</th>
    </tr>
    <tr>
        <th colspan="16">{{ $dateFrom." - ".$dateTo }}</th>
    </tr>
    <tr>
        <th style="font-weight: 800;">Line</th>
        <th style="font-weight: 800;">Last Shipment Date</th>
        <th style="font-weight: 800;">No. WS</th>
        <th style="font-weight: 800;">Style</th>
        <th style="font-weight: 800;">Color</th>
        <th style="font-weight: 800;">Size</th>
        <th style="font-weight: 800;">Dest</th>
        <th style="font-weight: 800;">Qty Loading</th>
        <th style="font-weight: 800;">WIP Sewing Line</th>
        <th style="font-weight: 800;">Reject</th>
        <th style="font-weight: 800;">Defect</th>
        <th style="font-weight: 800;">Qty Output</th>
        <th style="font-weight: 800;">WIP Steam</th>
        <th style="font-weight: 800;">Qty Packing Line</th>
        <th style="font-weight: 800;">WIP Packing</th>
        <th style="font-weight: 800;">Qty Transfer Garment</th>
    </tr>
    @php
        $totalLoading = 0;
        $totalReject = 0;
        $totalDefect = 0;
        $totalOutput = 0;
        $totalOutputPacking = 0;
        $totalWipSewing = 0;
        $totalWipSteam = 0;
        $totalWipPacking = 0;
        $totalTransferGarment = 0;
    @endphp
    @foreach ($data as $d)
        @php
            $reject = $dataReject->where("line_id", $d->line_id)->where("so_det_id", $d->id_so_det)->first();
            $defect = $dataDefect->where("line_id", $d->line_id)->where("so_det_id", $d->id_so_det)->first();
            $output = $dataOutput->where("line_id", $d->line_id)->where("so_det_id", $d->id_so_det)->first();
            $outputPacking = $dataOutputPacking->where("line_id", $d->line_id)->where("so_det_id", $d->id_so_det)->first();

            $totalLoading += ($d->loading_qty ? $d->loading_qty : 0);
            $totalWipSewing += ($d->loading_qty ? $d->loading_qty : 0) - (($reject ? ($reject->total_output ? $reject->total_output : 0) : 0) + ($defect ? ($defect->total_output ? $defect->total_output : 0) : 0) + ($output ? ($output->total_output ? $output->total_output : 0) : 0));
            $totalReject += $reject ? ($reject->total_output ? $reject->total_output : 0) : 0;
            $totalDefect += $defect ? ($defect->total_output ? $defect->total_output : 0) : 0;
            $totalOutput += $output ? ($output->total_output ? $output->total_output : 0) : 0;
            $totalWipSteam += ($output ? ($output->total_output ? $output->total_output : 0) : 0) - ($outputPacking ? ($outputPacking->total_output ? $outputPacking->total_output : 0) : 0);
            $totalOutputPacking += $outputPacking ? ($outputPacking->total_output ? $outputPacking->total_output : 0) : 0;
            $totalWipPacking += ($outputPacking ? ($outputPacking->total_output ? $outputPacking->total_output : 0) : 0) - ($d->total_transfer_garment ? $d->total_transfer_garment : 0);
            $totalTransferGarment += ($d->total_transfer_garment ? $d->total_transfer_garment : 0);
        @endphp
        <tr>
            <td>{{ $d->nama_line }}</td>
            <td>{{ $d->tanggal }}</td>
            <td>{{ $d->ws }}</td>
            <td>{{ $d->styleno }}</td>
            <td>{{ $d->color }}</td>
            <td>{{ $d->size }}</td>
            <td>{{ $d->dest }}</td>
            <td>{{ ($d->loading_qty ? $d->loading_qty : 0) }}</td>
            <td>{{ ($d->loading_qty ? $d->loading_qty : 0) - (($reject ? ($reject->total_output ? $reject->total_output : 0) : 0) + ($defect ? ($defect->total_output ? $defect->total_output : 0) : 0) + ($output ? ($output->total_output ? $output->total_output : 0) : 0)) }}</td>
            <td>{{ ($reject ? ($reject->total_output ? $reject->total_output : 0) : 0) }}</td>
            <td>{{ ($defect ? ($defect->total_output ? $defect->total_output : 0) : 0) }}</td>
            <td>{{ ($output ? ($output->total_output ? $output->total_output : 0) : 0) }}</td>
            <td>{{ ($output ? ($output->total_output ? $output->total_output : 0) : 0) - ($outputPacking ? ($outputPacking->total_output ? $outputPacking->total_output : 0) : 0) }}</td>
            <td>{{ ($outputPacking ? ($outputPacking->total_output ? $outputPacking->total_output : 0) : 0) }}</td>
            <td>{{ ($outputPacking ? ($outputPacking->total_output ? $outputPacking->total_output : 0) : 0) - ($d->total_transfer_garment ? $d->total_transfer_garment : 0) }}</td>
            <td>{{ ($d->total_transfer_garment ? $d->total_transfer_garment : 0) }}</td>
        </tr>
    @endforeach
    <tr>
        <td style="font-weight: bold;" colspan="7">TOTAL</td>
        <td style="font-weight: bold;">{{ $totalLoading }}</td>
        <td style="font-weight: bold;">{{ $totalWipSewing }}</td>
        <td style="font-weight: bold;">{{ $totalReject }}</td>
        <td style="font-weight: bold;">{{ $totalDefect }}</td>
        <td style="font-weight: bold;">{{ $totalOutput }}</td>
        <td style="font-weight: bold;">{{ $totalWipSteam }}</td>
        <td style="font-weight: bold;">{{ $totalOutputPacking }}</td>
        <td style="font-weight: bold;">{{ $totalWipPacking }}</td>
        <td style="font-weight: bold;">{{ $totalTransferGarment }}</td>
    </tr>
</table>
