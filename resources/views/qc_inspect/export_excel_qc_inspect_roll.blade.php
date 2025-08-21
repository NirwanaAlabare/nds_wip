<!DOCTYPE html>
<html>

<body>
    <table>
        <thead>
            <tr>
                <th colspan="100">FABRIC INSPECTION REPORT {{ date('d-M-Y', strtotime($from)) }} -
                    {{ date('d-M-Y', strtotime($to)) }}</th>
            </tr>
            <tr>
                <th rowspan="3">Tgl Bpb</th>
                <th rowspan="3">Tgl Inspect</th>
                <th rowspan="3">Machine</th>
                <th rowspan="3">Inspector</th>
                <th rowspan="3">Nik</th>
                <th rowspan="3">No Pl</th>
                <th rowspan="3">No Form</th>
                <th rowspan="3">Supplier</th>
                <th rowspan="3">Worksheet</th>
                <th rowspan="3">Buyer</th>
                <th rowspan="3">Style</th>
                <th rowspan="3">Id Item</th>
                <th rowspan="3">Fabric</th>
                <th rowspan="3">Color</th>
                <th rowspan="3">Lot</th>
                <th rowspan="3">Barcode</th>
                <th rowspan="3">No Roll</th>
                <th rowspan="3">Inspect Ke</th>
                <th rowspan="3">% Inspect</th>
                <th rowspan="3">Group Inspect</th>

                <th colspan="4">Weight</th>
                <th colspan="13">Width</th>
                <th colspan="7">Length</th>
                <th colspan="{{ $totalDefectCols }}">Defect Type</th>

                <th rowspan="3">Point Defect</th>
                <th colspan="2">4 Point System</th>
                <th colspan="6">Decision Visual Inspection</th>

            </tr>

            <tr>
                <th colspan="2">Kg</th>
                <th colspan="2">Lbs</th>
                <th colspan="6">Inch</th>
                <th colspan="6">CM</th>
                <th rowspan="2">Short Roll (%)</th>
                <th colspan="3">Yard</th>
                <th colspan="3">Meter</th>
                <th rowspan="2">Short Roll (%)</th>

                @foreach ($defects_group_kol as $group)
                    <th colspan="{{ $group->tot_kolom }}">{{ $group->point_defect }}</th>
                @endforeach

                <th rowspan="2">Point</th>
                <th rowspan="2">Standard</th>
                {{-- Kolom Decision Visual Inspection --}}
                <th rowspan="2">Grade (Visual Defect Point)</th>
                <th rowspan="2">Founding Issue</th>
                <th rowspan="2">Visual Defect Result</th>
                <th rowspan="2">Short Roll Result</th>
                <th rowspan="2">Founding Issue Result</th>
                <th rowspan="2">Final Result</th>
            </tr>

            <tr>
                <th>Bintex</th>
                <th>Actual</th>
                <th>Bintex</th>
                <th>Actual</th>
                <th>Bintex</th>
                <th>Front</th>
                <th>Middle</th>
                <th>Back</th>
                <th>Average</th>
                <th>Shortage</th>
                <th>Bintex</th>
                <th>Front</th>
                <th>Middle</th>
                <th>Back</th>
                <th>Average</th>
                <th>Shortage</th>
                <th>Bintex</th>
                <th>Actual</th>
                <th>Shortage</th>
                <th>Bintex</th>
                <th>Actual</th>
                <th>Shortage</th>

                @foreach ($defects as $def)
                    <th rowspan="1">{{ $def->critical_defect }}</th>
                @endforeach
            </tr>
        </thead>

        <tbody>
            @foreach ($data_input as $row)
                <tr>
                    <td>{{ $row->tgl_dok }}</td>
                    <td>{{ $row->tgl_form }}</td>
                    <td>{{ $row->no_mesin }}</td>
                    <td>{{ $row->operator }}</td>
                    <td>{{ $row->nik }}</td>
                    <td>{{ $row->no_invoice }}</td>
                    <td>{{ $row->no_form }}</td>
                    <td>{{ $row->supplier }}</td>
                    <td>{{ $row->no_ws }}</td>
                    <td>{{ $row->buyer }}</td>
                    <td>{{ $row->styleno }}</td>
                    <td>{{ $row->id_item }}</td>
                    <td>{{ $row->itemdesc }}</td>
                    <td>{{ $row->color }}</td>
                    <td>{{ $row->no_lot }}</td>
                    <td>{{ $row->barcode }}</td>
                    <td>{{ $row->no_roll_buyer }}</td>
                    <td>{{ $row->proses }}</td>
                    <td>{{ $row->cek_inspect }}</td>
                    <td>{{ $row->group_inspect }}</td>

                    <td>{{ $row->w_bintex }}</td>
                    <td>{{ $row->w_act }}</td>
                    <td>{{ $row->w_bintex_lbs }}</td>
                    <td>{{ $row->w_act_lbs }}</td>

                    <td>{{ $row->bintex_width }}</td>
                    <td>{{ $row->front }}</td>
                    <td>{{ $row->middle }}</td>
                    <td>{{ $row->back }}</td>
                    <td>{{ $row->avg_width }}</td>
                    <td>{{ $row->shortage_width }}</td>

                    <td>{{ $row->bintex_width_cm }}</td>
                    <td>{{ $row->front_cm }}</td>
                    <td>{{ $row->middle_cm }}</td>
                    <td>{{ $row->back_cm }}</td>
                    <td>{{ $row->avg_width_cm }}</td>
                    <td>{{ $row->shortage_width_cm }}</td>
                    <td>{{ $row->short_roll_percentage_width }}</td>

                    <td>{{ $row->bintex_length_act }}</td>
                    <td>{{ $row->act_length_fix }}</td>
                    <td>{{ $row->shortage_length_yard }}</td>
                    <td>{{ $row->bintex_length_meter }}</td>
                    <td>{{ $row->bintex_act_length_meter }}</td>
                    <td>{{ $row->shortage_length_meter }}</td>
                    <td>{{ $row->short_roll_percentage_length }}</td>

                    @foreach ($defects as $def)
                        <td>{{ $defectData[$row->no_form][$def->id] ?? 0 }}</td>
                    @endforeach

                    <!-- Kolom Point Defect -->
                    <td>{{ $row->sum_point_def }}</td>
                    <!-- Kolom Point System -->
                    <td>{{ $row->point_system }}</td>
                    <td>{{ $row->individu }}</td>
                    <!-- Kolom Decision Visual Inspection -->
                    <td>{{ $row->grade }}</td>
                    <td>{{ $row->founding_issue }}</td>
                    <td>{{ $row->result }}</td>
                    <td>{{ $row->short_roll_result }}</td>
                    <td>{{ $row->founding_issue_result }}</td>
                    <td>{{ $row->final_result }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>
