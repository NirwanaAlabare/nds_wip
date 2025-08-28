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
                <th rowspan="3">No PL</th>
                <th rowspan="3">Supplier</th>
                <th rowspan="3">Worksheet</th>
                <th rowspan="3">Buyer</th>
                <th rowspan="3">Style</th>
                <th rowspan="3">ID Item</th>
                <th rowspan="3">Fabric</th>
                <th rowspan="3">Color</th>
                <th rowspan="3">Lot</th>
                <th rowspan="3">Jml Roll</th>
                <th rowspan="3">% Inspect</th>
                <th rowspan="3">Jml Roll Inspect</th>
                <th rowspan="3">Group Inspect</th>

                <!-- Baris 1 untuk Width -->
                <th colspan="2">Width</th>

                <!-- Baris 1 untuk Length -->
                <th colspan="2">Length</th>

                <!-- Baris 1 untuk 4 Point System -->
                <th colspan="2" rowspan="2">4 Point System </th>

                <!-- Baris 1 untuk Decision -->
                <th colspan="8" rowspan="2">Decision Visual Inspection</th>

            </tr>

            <tr>
                <th>Inch</th>
                <th>CM</th>
                <th>Yard</th>
                <th>Meter</th>
            </tr>

            <tr>
                <!-- Baris 3 untuk Width -->
                <th>Actual Average</th>
                <th>Actual Average</th>
                <!-- Baris 3 untuk Length -->
                <th>Actual Average</th>
                <th>Actual Average</th>
                <!-- Baris 3 untuk Average Point -->
                <th>Average Point</th>
                <th>Standard</th>
                <!-- Baris 3 untuk Decision -->
                <th>Grade (Visual Defect Point)</th>
                <th>Founding Issue</th>
                <th>Rate Blanket</th>
                <th>Visual Defect Result</th>
                <th>Blanket Result</th>
                <th>Final Result</th>
                <th>Defect Found</th>
                <th>Est Reject Panel</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($data_input as $row)
                <tr>
                    <td>{{ $row->tgl_dok }}</td>
                    <td>{{ $row->no_invoice }}</td>
                    <td>{{ $row->supplier }}</td>
                    <td>{{ $row->kpno }}</td>
                    <td>{{ $row->buyer }}</td>
                    <td>{{ $row->styleno }}</td>
                    <td>{{ $row->id_item }}</td>
                    <td>{{ $row->itemdesc }}</td>
                    <td>{{ $row->color }}</td>
                    <td>{{ $row->no_lot }}</td>
                    <td>{{ $row->jml_roll }}</td>
                    <td>{{ $row->cek_inspect }}</td>
                    <td>{{ $row->jml_form }}</td>
                    <td>{{ $row->group_inspect }}</td>
                    <td>{{ $row->avg_width_inch }}</td>
                    <td>{{ $row->avg_width_cm }}</td>
                    <td>{{ $row->avg_l }}</td>
                    <td>{{ $row->avg_l_meter }}</td>
                    <td>{{ $row->avg_point }}</td>
                    <td>{{ $row->shipment }}</td>
                    <td>{{ $row->grade_visual_defect }}</td>
                    <td>{{ $row->list_founding_issue }}</td>
                    <td>{{ $row->rate }}</td>
                    <td>{{ $row->visual_defect_result }}</td>
                    <td>{{ $row->blanket_result }}</td>
                    <td>{{ $row->final_result }}</td>
                    <td>{{ $row->list_defect }}</td>
                    <td>{{ $row->est_final_reject }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>
