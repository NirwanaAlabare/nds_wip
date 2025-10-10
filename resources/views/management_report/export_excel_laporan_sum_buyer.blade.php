<!DOCTYPE html>
<html>

<body>
    <table>
        <thead>
            <tr>
                <th colspan="100">LAPORAN SUMMARY BUYER</th>
                {{-- <th colspan="100">LAPORAN DAILY COST {{ date('d-M-Y', strtotime($from)) }} -
                    {{ date('d-M-Y', strtotime($to)) }}</th> --}}
            </tr>
            <tr>
                <th class="text-center align-middle" scope="col" style="color: black;">Buyer
                </th>
                <th class="text-center align-middle" scope="col">Qty Target</th>
                <th class="text-center align-middle" scope="col">Qty Produced</th>
                <th class="text-center align-middle" scope="col">Mins. Avail.</th>
                <th class="text-center align-middle" scope="col">Mins. Prod</th>
                <th class="text-center align-middle" scope="col">Effy</th>
                <th class="text-center align-middle" scope="col">Est Earning Prd</th>
                <th class="text-center align-middle" scope="col">Est Total Cost</th>
                <th class="text-center align-middle" scope="col">Balance</th>
                <th class="text-center align-middle" scope="col">% of Earning</th>

                <th class="text-center align-middle" scope="col">Est Full Earning</th>
                <th class="text-center align-middle" scope="col">Est Total Cost</th>
                <th class="text-center align-middle" scope="col">Balance</th>
                <th class="text-center align-middle" scope="col">% of Earning</th>

                <th class="text-center align-middle" scope="col">Est Earning Prd</th>
                <th class="text-center align-middle" scope="col">Est Cost Prd</th>
                <th class="text-center align-middle" scope="col">Balance</th>
                <th class="text-center align-middle" scope="col">% of Earning</th>

                <th class="text-center align-middle" scope="col">Est Earning Mkt</th>
                <th class="text-center align-middle" scope="col">Est Cost Prd</th>
                <th class="text-center align-middle" scope="col">Balance</th>
                <th class="text-center align-middle" scope="col">% of Earning</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rawData as $row)
                <tr>
                    <td>{{ $row->buyer }}</td>
                    <td class="text-end" style="color: {{ $row->tot_target < 0 ? 'red' : 'black' }}">
                        {{ $row->tot_target }}
                    </td>
                    <td class="text-end" style="color: {{ $row->tot_output < 0 ? 'red' : 'black' }}">
                        {{ $row->tot_output }}
                    </td>
                    <td class="text-end" style="color: {{ $row->sum_mins_avail < 0 ? 'red' : 'black' }}">
                        {{ $row->sum_mins_avail }}
                    </td>
                    <td class="text-end" style="color: {{ $row->sum_mins_prod < 0 ? 'red' : 'black' }}">
                        {{ $row->sum_mins_prod }}
                    </td>
                    <td class="text-end" style="color: {{ $row->eff < 0 ? 'red' : 'black' }}">
                        {{ $row->eff }}
                    </td>
                    <td class="text-end" style="color: {{ $row->earn_prod < 0 ? 'red' : 'black' }}">
                        {{ $row->earn_prod }}
                    </td>
                    <td class="text-end" style="color: {{ $row->est_tot_cost < 0 ? 'red' : 'black' }}">
                        {{ $row->est_tot_cost }}
                    </td>
                    <td class="text-end" style="color: {{ $row->blc < 0 ? 'red' : 'black' }}">
                        {{ $row->blc }}
                    </td>
                    <td class="text-end" style="color: {{ $row->percent_earning < 0 ? 'red' : 'black' }}">
                        {{ $row->percent_earning }}
                    </td>
                    <td class="text-end" style="color: {{ $row->sum_est_full_earning < 0 ? 'red' : 'black' }}">
                        {{ $row->sum_est_full_earning }}
                    </td>
                    <td class="text-end" style="color: {{ $row->est_tot_cost < 0 ? 'red' : 'black' }}">
                        {{ $row->est_tot_cost }}
                    </td>
                    <td class="text-end" style="color: {{ $row->blc_full_earn_cost_prod < 0 ? 'red' : 'black' }}">
                        {{ $row->blc_full_earn_cost_prod }}
                    </td>
                    <td class="text-end" style="color: {{ $row->percent_full_earning_cost < 0 ? 'red' : 'black' }}">
                        {{ $row->percent_full_earning_cost }}
                    </td>
                    <td class="text-end" style="color: {{ $row->sum_est_earning_prod < 0 ? 'red' : 'black' }}">
                        {{ $row->sum_est_earning_prod }}
                    </td>
                    <td class="text-end" style="color: {{ $row->sum_est_cost_prod < 0 ? 'red' : 'black' }}">
                        {{ $row->sum_est_cost_prod }}
                    </td>
                    <td class="text-end" style="color: {{ $row->blc_earn_cost_prod < 0 ? 'red' : 'black' }}">
                        {{ $row->blc_earn_cost_prod }}
                    </td>
                    <td class="text-end" style="color: {{ $row->percent_earn_cost_prod < 0 ? 'red' : 'black' }}">
                        {{ $row->percent_earn_cost_prod }}
                    </td>
                    <td class="text-end" style="color: {{ $row->sum_est_earning_mkt < 0 ? 'red' : 'black' }}">
                        {{ $row->sum_est_earning_mkt }}
                    </td>
                    <td class="text-end" style="color: {{ $row->sum_est_cost_mkt < 0 ? 'red' : 'black' }}">
                        {{ $row->sum_est_cost_mkt }}
                    </td>
                    <td class="text-end" style="color: {{ $row->blc_earn_cost_mkt < 0 ? 'red' : 'black' }}">
                        {{ $row->blc_earn_cost_mkt }}
                    </td>
                    <td class="text-end" style="color: {{ $row->percent_earn_cost_mkt < 0 ? 'red' : 'black' }}">
                        {{ $row->percent_earn_cost_mkt }}
                    </td>

                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
