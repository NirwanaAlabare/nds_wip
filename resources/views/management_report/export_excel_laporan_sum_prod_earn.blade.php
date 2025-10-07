<!DOCTYPE html>
<html>

<body>
    <table>
        <thead>
            <tr>
                <th colspan="100">LAPORAN SUMMARY PRODUCTION EARNING</th>
                {{-- <th colspan="100">LAPORAN DAILY COST {{ date('d-M-Y', strtotime($from)) }} -
                    {{ date('d-M-Y', strtotime($to)) }}</th> --}}
            </tr>
            <tr>
                <th class="text-center align-middle" scope="col">Date</th>
                <th class="text-center align-middle" scope="col">Est Earning</th>
                <th class="text-center align-middle" scope="col">Est Total Cost</th>
                <th class="text-center align-middle" scope="col">Balance</th>

                <th class="text-center align-middle" scope="col">Total Min</th>
                <th class="text-center align-middle" scope="col">Est Earning / Min</th>
                <th class="text-center align-middle" scope="col">Est Cost / Min</th>
                <th class="text-center align-middle" scope="col">Balance</th>

                <th class="text-center align-middle" scope="col">No Of MP</th>
                <th class="text-center align-middle" scope="col">Est Earning / MP</th>
                <th class="text-center align-middle" scope="col">Est Cost / MP</th>
                <th class="text-center align-middle" scope="col">Balance</th>

                <th class="text-center align-middle" scope="col">Rate</th>
                <th class="text-center align-middle" scope="col">Est Earning</th>
                <th class="text-center align-middle" scope="col">Est Total Cost</th>
                <th class="text-center align-middle" scope="col">Balance</th>

                <th class="text-center align-middle" scope="col">Total Min</th>
                <th class="text-center align-middle" scope="col">Est Earning / Min</th>
                <th class="text-center align-middle" scope="col">Est Cost / Min</th>
                <th class="text-center align-middle" scope="col">Balance</th>

                <th class="text-center align-middle" scope="col">No Of MP</th>
                <th class="text-center align-middle" scope="col">Est Earning / MP</th>
                <th class="text-center align-middle" scope="col">Est Cost / MP</th>
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

                    <td class="text-end" style="color: {{ $row->sewing_absen_menit < 0 ? 'red' : 'black' }}">
                        {{ $row->sewing_absen_menit }}
                    </td>
                    <td class="text-end" style="color: {{ $row->est_earn_per_min < 0 ? 'red' : 'black' }}">
                        {{ $row->est_earn_per_min }}
                    </td>
                    <td class="text-end" style="color: {{ $row->est_cost_per_min < 0 ? 'red' : 'black' }}">
                        {{ $row->est_cost_per_min }}
                    </td>
                    <td class="text-end" style="color: {{ $row->blc_earn_and_cost < 0 ? 'red' : 'black' }}">
                        {{ $row->blc_earn_and_cost }}
                    </td>

                    <td class="text-end" style="color: {{ $row->tot_man_power < 0 ? 'red' : 'black' }}">
                        {{ $row->tot_man_power }}
                    </td>
                    <td class="text-end"
                        style="color: {{ $row->est_earn_per_min_all_man_power < 0 ? 'red' : 'black' }}">
                        {{ $row->est_earn_per_min_all_man_power }}
                    </td>
                    <td class="text-end"
                        style="color: {{ $row->est_cost_per_min_all_man_power < 0 ? 'red' : 'black' }}">
                        {{ $row->est_cost_per_min_all_man_power }}
                    </td>
                    <td class="text-end"
                        style="color: {{ $row->blc_earn_and_cost_all_man_power < 0 ? 'red' : 'black' }}">
                        {{ $row->blc_earn_and_cost_all_man_power }}
                    </td>

                    <td class="text-end" style="color: {{ $row->kurs_tengah < 0 ? 'red' : 'black' }}">
                        {{ $row->kurs_tengah }}
                    </td>
                    <td class="text-end" style="color: {{ $row->est_earning_rate < 0 ? 'red' : 'black' }}">
                        {{ $row->est_earning_rate }}
                    </td>
                    <td class="text-end" style="color: {{ $row->est_tot_cost_rate < 0 ? 'red' : 'black' }}">
                        {{ $row->est_tot_cost_rate }}
                    </td>
                    <td class="text-end" style="color: {{ $row->blc_est_earning_cost_rate < 0 ? 'red' : 'black' }}">
                        {{ $row->blc_est_earning_cost_rate }}
                    </td>

                    <td class="text-end" style="color: {{ $row->sewing_absen_menit < 0 ? 'red' : 'black' }}">
                        {{ $row->sewing_absen_menit }}
                    </td>
                    <td class="text-end" style="color: {{ $row->est_earning_rate_per_min < 0 ? 'red' : 'black' }}">
                        {{ $row->est_earning_rate_per_min }}
                    </td>
                    <td class="text-end" style="color: {{ $row->est_cost_rate_per_min < 0 ? 'red' : 'black' }}">
                        {{ $row->est_cost_rate_per_min }}
                    </td>
                    <td class="text-end" style="color: {{ $row->blc_rate_per_min < 0 ? 'red' : 'black' }}">
                        {{ $row->blc_rate_per_min }}
                    </td>

                    <td class="text-end" style="color: {{ $row->tot_man_power < 0 ? 'red' : 'black' }}">
                        {{ $row->tot_man_power }}
                    </td>
                    <td class="text-end"
                        style="color: {{ $row->est_earning_rate_per_man_power < 0 ? 'red' : 'black' }}">
                        {{ $row->est_earning_rate_per_man_power }}
                    </td>
                    <td class="text-end" style="color: {{ $row->est_cost_rate_per_man_power < 0 ? 'red' : 'black' }}">
                        {{ $row->est_cost_rate_per_man_power }}
                    </td>
                    <td class="text-end" style="color: {{ $row->blc_rate_per_man_power < 0 ? 'red' : 'black' }}">
                        {{ $row->blc_rate_per_man_power }}
                    </td>

                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
