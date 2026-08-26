<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Laporan Earning</title>
</head>

<body>
    <table>
        <thead>
            <tr>
                <th colspan="6">LAPORAN EARNING</th>
            </tr>
            <tr>
                <th rowspan="2">Tanggal</th>
                <th rowspan="2">Line</th>
                <th rowspan="2">WS Number</th>
                <th rowspan="2">Buyer</th>
                <th rowspan="2">Output</th>
                <th rowspan="2">Mins. Prod</th>
                <th rowspan="2">Mins. Avail</th>
                <th rowspan="2">Eff</th>
                <th colspan="4">Est Earning</th>
                <th colspan="5">Est Full Earning</th>
                <th colspan="4">Est Earning Production</th>
                <th colspan="4">Est Earning Marketing</th>
            </tr>
            <tr>
                <th>Est Earning</th>
                <th>Est Total Cost</th>
                <th>Balance</th>
                <th>% Of Earn</th>
                <th>Full CM Price</th>
                <th>Est Full Earning</th>
                <th>Est Total Cost</th>
                <th>Balance</th>
                <th>% Of Earn</th>
                <th>Est Earning Prod</th>
                <th>Est Cost Prod</th>
                <th>Balance</th>
                <th>% Of Earn</th>
                <th>Est Earning Mkt</th>
                <th>Est Cost Mkt</th>
                <th>Balance</th>
                <th>% Of Earn</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rawData as $row)
                @php
                    $isLibur = ($row->stat_kerja ?? null) === 'LIBUR';
                    $rowStyle = $isLibur ? 'color:#FF0000;background-color:#FDE8E8;' : '';
                    $values = [
                        (float) $row->tot_output,
                        (float) $row->mins_prod,
                        (float) $row->mins_avail,
                        (float) $row->eff_line / 100,
                        (float) $row->tot_earning_rupiah,
                        (float) $row->est_tot_cost,
                        (float) $row->blc,
                        (float) $row->percent_est_earn / 100,
                        (float) $row->full_cm_price,
                        (float) $row->est_full_earning,
                        (float) $row->est_tot_cost,
                        (float) $row->blc_full_earn,
                        (float) $row->percent_full_earn / 100,
                        (float) $row->est_earning_prod,
                        (float) $row->est_cost_prod,
                        (float) $row->blc_est_cost_prod,
                        (float) $row->percent_est_cost_prod / 100,
                        (float) $row->est_earning_mkt,
                        (float) $row->est_cost_mkt,
                        (float) $row->blc_earn_mkt,
                        (float) $row->percent_earn_mkt / 100,
                    ];
                @endphp
                <tr>
                    <td style="{{ $rowStyle }}">{{ $row->tanggal_fix }}</td>
                    <td style="{{ $rowStyle }}">{{ $row->sewing_line }}</td>
                    <td style="{{ $rowStyle }}">{{ $row->kpno }}</td>
                    <td style="{{ $rowStyle }}">{{ $row->buyer }}</td>
                    @foreach ($values as $value)
                        <td style="{{ $rowStyle ?: ($value < 0 ? 'color:#FF0000;' : '') }}">{{ $value }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
