<!DOCTYPE html>
<html>

<body>
    <table>
        <thead>
            <tr>
                <th colspan="100">LAPORAN EARNING</th>
                {{-- <th colspan="100">LAPORAN DAILY COST {{ date('d-M-Y', strtotime($from)) }} -
                    {{ date('d-M-Y', strtotime($to)) }}</th> --}}
            </tr>

            <tr>
                <th class="text-center align-middle" rowspan="2" scope="col" style="color: black;">Tanggal
                </th>
                <th class="text-center align-middle" rowspan="2" scope="col" style="color: black;">Line
                </th>
                <th class="text-center align-middle"rowspan="2" scope="col" style="color: black;">WS Number
                </th>
                <th class="text-center align-middle" rowspan="2" scope="col" style="color: black;">Buyer
                </th>
                <th class="text-center align-middle" rowspan="2" scope="col">Output</th>
                <th class="text-center align-middle" rowspan="2" scope="col">Mins. Prod</th>
                <th class="text-center align-middle" rowspan="2" scope="col">Mins. Avail</th>
                <th class="text-center align-middle" rowspan="2" scope="col">Eff</th>

                <th class="text-center align-middle" colspan="4" scope="col">Est Earning</th>
                <th class="text-center align-middle" colspan="5" scope="col">Est Full Earning</th>
                <th class="text-center align-middle" colspan="4" scope="col">Est Earning Production</th>
                <th class="text-center align-middle" colspan="4" scope="col">Est Earning Marketing</th>
            </tr>
            <tr>
                <th class="text-center align-middle" scope="col">Est Earning</th>
                <th class="text-center align-middle" scope="col">Est Total Cost</th>
                <th class="text-center align-middle" scope="col">Balance</th>
                <th class="text-center align-middle" scope="col">% Of Earn</th>

                <th class="text-center align-middle" scope="col">Full CM Price</th>
                <th class="text-center align-middle" scope="col">Est Full Earning</th>
                <th class="text-center align-middle" scope="col">Est Total Cost</th>
                <th class="text-center align-middle" scope="col">Balance</th>
                <th class="text-center align-middle" scope="col">% Of Earn</th>

                <th class="text-center align-middle" scope="col">Est Earning Prod</th>
                <th class="text-center align-middle" scope="col">Est Cost Prod</th>
                <th class="text-center align-middle" scope="col">Balance</th>
                <th class="text-center align-middle" scope="col">% Of Earn</th>

                <th class="text-center align-middle" scope="col">Est Earning Mkt</th>
                <th class="text-center align-middle" scope="col">Est Cost Mkt</th>
                <th class="text-center align-middle" scope="col">Balance</th>
                <th class="text-center align-middle" scope="col">% Of Earn</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rawData as $row)
                <tr>
                    <td>{{ $row->tgl_trans_fix }}</td>
                    <td>{{ $row->sewing_line }}</td>
                    <td>{{ $row->kpno }}</td>
                    <td>{{ $row->buyer }}</td>
                    <td class="text-end">{{ $row->tot_output }}</td>
                    <td class="text-end">{{ number_format($row->mins_prod, 2) }}</td>
                    <td class="text-end">{{ number_format($row->mins_avail, 2) }}</td>
                    <td class="text-end">{{ $row->eff_line }}</td>

                    <td class="text-end" style="color: {{ $row->tot_earning_rupiah < 0 ? 'red' : 'black' }}">
                        {{ number_format($row->tot_earning_rupiah, 2) }}</td>
                    <td class="text-end" style="color: {{ $row->est_tot_cost < 0 ? 'red' : 'black' }}">
                        {{ number_format($row->est_tot_cost, 2) }}</td>
                    <td class="text-end" style="color: {{ $row->blc < 0 ? 'red' : 'black' }}">
                        {{ number_format($row->blc, 2) }}</td>
                    <td class="text-end"
                        style="color: {{ strpos($row->percent_est_earn, '-') !== false ? 'red' : 'black' }}">
                        {{ number_format((float) $row->percent_est_earn, 2) }} %
                    </td>

                    <td class="text-end">{{ number_format($row->full_cm_price, 2) }}</td>
                    <td class="text-end" style="color: {{ $row->est_full_earning < 0 ? 'red' : 'black' }}">
                        {{ number_format($row->est_full_earning, 2) }}</td>
                    <td class="text-end" style="color: {{ $row->est_tot_cost < 0 ? 'red' : 'black' }}">
                        {{ number_format($row->est_tot_cost, 2) }}</td>
                    <td class="text-end" style="color: {{ $row->blc_full_earn < 0 ? 'red' : 'black' }}">
                        {{ number_format($row->blc_full_earn, 2) }}</td>
                    <td class="text-end"
                        style="color: {{ strpos($row->percent_full_earn, '-') !== false ? 'red' : 'black' }}">
                        {{ number_format((float) $row->percent_full_earn, 2) }} %
                    </td>

                    <td class="text-end" style="color: {{ $row->est_earning_prod < 0 ? 'red' : 'black' }}">
                        {{ number_format($row->est_earning_prod, 2) }}</td>
                    <td class="text-end" style="color: {{ $row->est_cost_prod < 0 ? 'red' : 'black' }}">
                        {{ number_format($row->est_cost_prod, 2) }}</td>
                    <td class="text-end" style="color: {{ $row->blc_est_cost_prod < 0 ? 'red' : 'black' }}">
                        {{ number_format($row->blc_est_cost_prod, 2) }}</td>
                    <td class="text-end"
                        style="color: {{ strpos($row->percent_est_cost_prod, '-') !== false ? 'red' : 'black' }}">
                        {{ number_format((float) $row->percent_est_cost_prod, 2) }} %
                    </td>

                    <td class="text-end" style="color: {{ $row->est_earning_mkt < 0 ? 'red' : 'black' }}">
                        {{ number_format($row->est_earning_mkt, 2) }}</td>
                    <td class="text-end" style="color: {{ $row->est_cost_mkt < 0 ? 'red' : 'black' }}">
                        {{ number_format($row->est_cost_mkt, 2) }}</td>
                    <td class="text-end" style="color: {{ $row->blc_earn_mkt < 0 ? 'red' : 'black' }}">
                        {{ number_format($row->blc_earn_mkt, 2) }}</td>
                    <td class="text-end"
                        style="color: {{ strpos($row->percent_earn_mkt, '-') !== false ? 'red' : 'black' }}">
                        {{ number_format((float) $row->percent_earn_mkt, 2) }} %
                    </td>



                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
