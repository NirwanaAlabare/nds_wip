<!DOCTYPE html>
<html>

<body>
    <table>
        <thead>
            <tr>
                <th colspan="51"> Report Mutasi WIP Sewing
                    ({{ \Carbon\Carbon::parse($start_date)->format('d-m-Y') }})
                    -
                    ({{ \Carbon\Carbon::parse($end_date)->format('d-m-Y') }})
                </th>
                {{-- <th colspan="100">LAPORAN DAILY COST {{ date('d-M-Y', strtotime($from)) }} -
                    {{ date('d-M-Y', strtotime($to)) }}</th> --}}
            </tr>
            <tr style='text-align:center; vertical-align:middle'>
                <th colspan="5" style="background-color: lightblue; text-align:center;">Jenis Produk</th>
                <th colspan="11" style="background-color: yellow; text-align:center;">Sewing</th>
                <th colspan="11" style="background-color: pink; text-align:center;">QC Finishing</th>
                <th colspan="7" style="background-color: lightsteelblue; text-align:center;">Secondary Proses
                </th>
                <th colspan="4" style="background-color: #FFE5B4; text-align:center;">Defect Sewing
                </th>
                <th colspan="4" style="background-color: #E6E6FA; text-align:center;">Defect Spotcleaning
                </th>
                <th colspan="4" style="background-color: yellow; text-align:center;">Defect Mending
                </th>
                <th colspan="5" style="background-color: pink; text-align:center;">QC Reject</th>
            </tr>
            <tr style='text-align:center; vertical-align:middle'>
                <th style="background-color: lightblue;">Buyer</th>
                <th style="background-color: lightblue;">WS</th>
                <th style="background-color: lightblue;">Style</th>
                <th style="background-color: lightblue;">Color</th>
                <th style="background-color: lightblue;">Size</th>

                <th style="background-color: yellow;">Saldo Awal</th>
                <th style="background-color: yellow;">Terima Loading</th>
                <th style="background-color: yellow;">Output Rework Sewing
                </th>
                <th style="background-color: yellow;">Output Rework
                    Spotcleaning</th>
                <th style="background-color: yellow;">Output Rework
                    Mending
                </th>
                <th style="background-color: yellow;">Defect Sewing</th>
                <th style="background-color: yellow;">Defect Spotcleaning
                </th>
                <th style="background-color: yellow;">Defect Mending</th>
                <th style="background-color: yellow;">Reject</th>
                <th style="background-color: yellow;">Output</th>
                <th style="background-color: yellow;">Saldo Akhir</th>


                <th style="background-color: pink;">Saldo Awal</th>
                <th style="background-color: pink;">Terima</th>
                <th style="background-color: pink;">Output Rework Sewing
                </th>
                <th style="background-color: pink;">Output Rework
                    Spotcleaning</th>
                <th style="background-color: pink;">Output Rework
                    Mending
                </th>
                <th style="background-color: pink;">Defect Sewing</th>
                <th style="background-color: pink;">Defect Spotcleaning
                </th>
                <th style="background-color: pink;">Defect Mending</th>
                <th style="background-color: pink;">Reject</th>
                <th style="background-color: pink;">Output</th>
                <th style="background-color: pink;">Saldo Akhir</th>

                <th style="background-color: lightsteelblue;">Saldo Awal</th>
                <th style="background-color: lightsteelblue;">Terima</th>
                <th style="background-color: lightsteelblue;">Rework</th>
                <th style="background-color: lightsteelblue;">Defect</th>
                <th style="background-color: lightsteelblue;">Reject</th>
                <th style="background-color: lightsteelblue;">Output</th>
                <th style="background-color: lightsteelblue;">Saldo Akhir
                </th>

                <th style="background-color: #FFE5B4;">Saldo Awal</th>
                <th style="background-color: #FFE5B4;">Terima</th>
                <th style="background-color: #FFE5B4;">Keluar</th>
                <th style="background-color: #FFE5B4;">Saldo Akhir
                </th>

                <th style="background-color: lavender;">Saldo Awal</th>
                <th style="background-color: lavender;">Terima</th>
                <th style="background-color: lavender;">Keluar</th>
                <th style="background-color: lavender;">Saldo Akhir
                </th>

                <th style="background-color: yellow;">Saldo Awal</th>
                <th style="background-color: yellow;">Terima</th>
                <th style="background-color: yellow;">Keluar</th>
                <th style="background-color: yellow;">Saldo Akhir
                </th>

                <th style="background-color: pink;">Saldo Awal</th>
                <th style="background-color: pink;">Terima</th>
                <th style="background-color: pink;">Keluar Sewing</th>
                <th style="background-color: pink;">Keluar Gudang Stok
                </th>
                <th style="background-color: pink;">Saldo Akhir
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rawData as $row)
                <tr>
                    <td>{{ $row->buyer }}</td>
                    <td>{{ $row->ws }}</td>
                    <td>{{ $row->styleno }}</td>
                    <td>{{ $row->color }}</td>
                    <td>{{ $row->size }}</td>

                    <td class="text-end">{{ $row->saldo_awal_loading }}</td>
                    <td class="text-end">{{ $row->qty_loading }}</td>
                    <td class="text-end">{{ $row->input_rework_sewing }}</td>
                    <td class="text-end">{{ $row->input_rework_spotcleaning }}</td>
                    <td class="text-end">{{ $row->input_rework_mending }}</td>
                    <td class="text-end">{{ $row->defect_sewing }}</td>
                    <td class="text-end">{{ $row->defect_spotcleaning }}</td>
                    <td class="text-end">{{ $row->defect_mending }}</td>
                    <td class="text-end">{{ $row->qty_sew_reject }}</td>
                    <td class="text-end">{{ $row->qty_sewing }}</td>
                    <td class="text-end">{{ $row->saldo_akhir_sewing }}</td>

                    <td class="text-end">{{ $row->saldo_awal_finishing }}</td>
                    <td class="text-end">{{ $row->qty_sewing }}</td>
                    <td class="text-end">{{ $row->input_rework_sewing_f }}</td>
                    <td class="text-end">{{ $row->input_rework_spotcleaning_f }}</td>
                    <td class="text-end">{{ $row->input_rework_mending_f }}</td>
                    <td class="text-end">{{ $row->defect_sewing_f }}</td>
                    <td class="text-end">{{ $row->defect_spotcleaning_f }}</td>
                    <td class="text-end">{{ $row->defect_mending_f }}</td>
                    <td class="text-end">{{ $row->qty_fin_reject }}</td>
                    <td class="text-end">{{ $row->qty_finishing }}</td>
                    <td class="text-end">{{ $row->saldo_akhir_finishing }}</td>

                    <td class="text-end">{{ $row->saldo_awal_secondary_proses }}</td>
                    <td class="text-end">{{ $row->total_in_sp }}</td>
                    <td class="text-end">{{ $row->rework_sp }}</td>
                    <td class="text-end">{{ $row->defect_sp }}</td>
                    <td class="text-end">{{ $row->reject_sp }}</td>
                    <td class="text-end">{{ $row->rft_sp }}</td>
                    <td class="text-end">{{ $row->saldo_akhir_secondary_proses }}</td>

                    <td class="text-end">{{ $row->saldo_awal_defect_sewing }}</td>
                    <td class="text-end">{{ $row->total_defect_sewing }}</td>
                    <td class="text-end">{{ $row->total_input_rework_sewing }}</td>
                    <td class="text-end">{{ $row->saldo_akhir_defect_sewing }}</td>

                    <td class="text-end">{{ $row->saldo_awal_defect_spotcleaning }}</td>
                    <td class="text-end">{{ $row->total_defect_spotcleaning }}</td>
                    <td class="text-end">{{ $row->total_input_rework_spotcleaning }}</td>
                    <td class="text-end">{{ $row->saldo_akhir_defect_spotcleaning }}</td>

                    <td class="text-end">{{ $row->saldo_awal_defect_mending }}</td>
                    <td class="text-end">{{ $row->total_defect_mending }}</td>
                    <td class="text-end">{{ $row->total_input_rework_mending }}</td>
                    <td class="text-end">{{ $row->saldo_akhir_mending }}</td>

                    <td class="text-end">{{ $row->saldo_awal_reject }}</td>
                    <td class="text-end">{{ $row->qty_reject_in }}</td>
                    <td class="text-end">{{ $row->qty_rejected }}</td>
                    <td class="text-end">{{ $row->qty_reworked }}</td>
                    <td class="text-end">{{ $row->saldo_akhir_qc_reject }}</td>

                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
