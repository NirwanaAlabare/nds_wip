<!DOCTYPE html>
<html>

<body>
    <table>
        <thead>
            <tr>
                <th colspan="100">LAPORAN SUMMARY PRODUCTION FULL EARNING</th>
                {{-- <th colspan="100">LAPORAN DAILY COST {{ date('d-M-Y', strtotime($from)) }} -
                    {{ date('d-M-Y', strtotime($to)) }}</th> --}}
            </tr>
            <tr>
                <th class="text-center align-middle" scope="col">Date</th>
                <th class="text-center align-middle" scope="col">Est Earning</th>
                <th class="text-center align-middle" scope="col">Est Total Cost</th>
                <th class="text-center align-middle" scope="col">Balance</th>

                <th class="text-center align-middle" scope="col">Est Full Earning</th>
                <th class="text-center align-middle" scope="col">Est Total Cost</th>
                <th class="text-center align-middle" scope="col">Balance</th>

                <th class="text-center align-middle" scope="col">Est Earning Prod</th>
                <th class="text-center align-middle" scope="col">Est Cost Prod</th>
                <th class="text-center align-middle" scope="col">Balance</th>

                <th class="text-center align-middle" scope="col">Est Earning Mkt</th>
                <th class="text-center align-middle" scope="col">Est Cost Mkt</th>
                <th class="text-center align-middle" scope="col">Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rawData as $row)
                <tr>
                    <td>{{ $row->tanggal_fix }}</td>
                    <td class="text-end" style="color: {{ $row->sum_tot_earning_rupiah < 0 ? 'red' : 'black' }}">
                        {{ $row->sum_tot_earning_rupiah }}
                    </td>
                    <td class="text-end" style="color: {{ $row->est_tot_cost < 0 ? 'red' : 'black' }}">
                        {{ $row->est_tot_cost }}
                    </td>
                    <td class="text-end" style="color: {{ $row->blc < 0 ? 'red' : 'black' }}">
                        {{ $row->blc }}
                    </td>

                    <td class="text-end" style="color: {{ $row->sum_est_full_earning < 0 ? 'red' : 'black' }}">
                        {{ $row->sum_est_full_earning }}
                    </td>
                    <td class="text-end" style="color: {{ $row->est_tot_cost < 0 ? 'red' : 'black' }}">
                        {{ $row->est_tot_cost }}
                    </td>
                    <td class="text-end" style="color: {{ $row->blc_full_earning < 0 ? 'red' : 'black' }}">
                        {{ $row->blc_full_earning }}
                    </td>

                    <td class="text-end" style="color: {{ $row->sum_est_earning_prod < 0 ? 'red' : 'black' }}">
                        {{ $row->sum_est_earning_prod }}
                    </td>
                    <td class="text-end" style="color: {{ $row->sum_est_cost_prod < 0 ? 'red' : 'black' }}">
                        {{ $row->sum_est_cost_prod }}
                    </td>
                    <td class="text-end" style="color: {{ $row->blc_est_earn_cost_prod < 0 ? 'red' : 'black' }}">
                        {{ $row->blc_est_earn_cost_prod }}
                    </td>

                    <td class="text-end" style="color: {{ $row->sum_est_earning_mkt < 0 ? 'red' : 'black' }}">
                        {{ $row->sum_est_earning_mkt }}
                    </td>
                    <td class="text-end" style="color: {{ $row->sum_est_cost_mkt < 0 ? 'red' : 'black' }}">
                        {{ $row->sum_est_cost_mkt }}
                    </td>
                    <td class="text-end" style="color: {{ $row->blc_est_earn_cost_mkt < 0 ? 'red' : 'black' }}">
                        {{ $row->blc_est_earn_cost_mkt }}
                    </td>

                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
