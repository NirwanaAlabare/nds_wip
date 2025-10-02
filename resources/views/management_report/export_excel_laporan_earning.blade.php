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
                    <td>{{ $row->tanggal_fix }}</td>
                    <td>{{ $row->sewing_line }}</td>
                    <td>{{ $row->kpno }}</td>
                    <td>{{ $row->buyer }}</td>
                    <td class="text-end">{{ $row->tot_output }}</td>
                    <td class="text-end">{{ $row->mins_prod }}</td>
                    <td class="text-end">{{ $row->mins_avail }}</td>
                    <td class="text-end"
                        style="color: {{ strpos($row->eff_line / 100, '-') !== false ? 'red' : 'black' }}">
                        {{ $row->eff_line / 100 }}
                    </td>

                    <td class="text-end" style="color: {{ $row->tot_earning_rupiah < 0 ? 'red' : 'black' }}">
                        {{ $row->tot_earning_rupiah }}
                    </td>
                    <td class="text-end" style="color: {{ $row->est_tot_cost < 0 ? 'red' : 'black' }}">
                        {{ $row->est_tot_cost }}
                    </td>
                    <td class="text-end" style="color: {{ $row->blc < 0 ? 'red' : 'black' }}">
                        {{ $row->blc }}
                    </td>
                    <td class="text-end"
                        style="color: {{ strpos($row->percent_est_earn / 100, '-') !== false ? 'red' : 'black' }}">
                        {{ $row->percent_est_earn / 100 }}
                    </td>

                    <td class="text-end">{{ $row->full_cm_price }}</td>
                    <td class="text-end" style="color: {{ $row->est_full_earning < 0 ? 'red' : 'black' }}">
                        {{ $row->est_full_earning }}
                    </td>
                    <td class="text-end" style="color: {{ $row->est_tot_cost < 0 ? 'red' : 'black' }}">
                        {{ $row->est_tot_cost }}
                    </td>
                    <td class="text-end" style="color: {{ $row->blc_full_earn < 0 ? 'red' : 'black' }}">
                        {{ $row->blc_full_earn }}
                    </td>
                    <td class="text-end"
                        style="color: {{ strpos($row->percent_full_earn / 100, '-') !== false ? 'red' : 'black' }}">
                        {{ $row->percent_full_earn / 100 }}
                    </td>

                    <td class="text-end" style="color: {{ $row->est_earning_prod < 0 ? 'red' : 'black' }}">
                        {{ $row->est_earning_prod }}
                    </td>
                    <td class="text-end" style="color: {{ $row->est_cost_prod < 0 ? 'red' : 'black' }}">
                        {{ $row->est_cost_prod }}
                    </td>
                    <td class="text-end" style="color: {{ $row->blc_est_cost_prod < 0 ? 'red' : 'black' }}">
                        {{ $row->blc_est_cost_prod }}
                    </td>
                    <td class="text-end"
                        style="color: {{ strpos($row->percent_est_cost_prod / 100, '-') !== false ? 'red' : 'black' }}">
                        {{ $row->percent_est_cost_prod / 100 }}
                    </td>

                    <td class="text-end" style="color: {{ $row->est_earning_mkt < 0 ? 'red' : 'black' }}">
                        {{ $row->est_earning_mkt }}
                    </td>
                    <td class="text-end" style="color: {{ $row->est_cost_mkt < 0 ? 'red' : 'black' }}">
                        {{ $row->est_cost_mkt }}
                    </td>
                    <td class="text-end" style="color: {{ $row->blc_earn_mkt < 0 ? 'red' : 'black' }}">
                        {{ $row->blc_earn_mkt }}
                    </td>
                    <td class="text-end"
                        style="color: {{ strpos($row->percent_earn_mkt / 100, '-') !== false ? 'red' : 'black' }}">
                        {{ $row->percent_earn_mkt / 100 }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
