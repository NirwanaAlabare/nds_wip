<!DOCTYPE html>
<html>

<head>
    <title>QC Inspect</title>
    <style>
        @page {
            margin: 15px;
        }

        body {
            margin: 15px;
            font-family: Calibri, Helvetica, Arial, sans-serif;
        }

        * {
            font-size: 11px;
        }

        img {
            width: 69px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        /* table td, table th{
            text-align: left;
            vertical-align: middle;
            padding: 1px 3px;
            border: 1px solid;
            width: auto;
        }*/

        .table {
            text-align: left;
            vertical-align: middle;
            padding: 1px 3px;
            border: 1px solid;
            width: auto;
        }

        .table2 {
            border-collapse: collapse !important;
            width: 100%;
            max-width: 100%;
            font-size: 10px;
        }

        .table2 td {
            background-color: #fff;
        }

        .table2 th {
            background-color: #fff;
        }
    </style>
    <style>
        @media print {
            .page-break {
                page-break-before: always;
            }
        }

        @media print {

            table,
            tr,
            td,
            th {
                page-break-inside: avoid;
                break-inside: avoid;
            }
        }
    </style>


</head>

<body>

    <div class="card-body">
        <div class="form-group">
            <div class="col-md-12 mb-3">
                <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; table-layout: fixed;">
                    <tbody>
                        <tr>
                            <!-- Logo Cell -->
                            <td rowspan="4" style="width: 20%; border: 1px solid #000; text-align: center;">
                                <img src="{{ public_path('nag-logo.png') }}" style="max-width: 80px; height: auto;">
                            </td>

                            <!-- Title Cell -->
                            <td rowspan="4"
                                style="width: 50%; border: 1px solid #000; text-align: center; vertical-align: middle; font-size: 18px; font-weight: bold;">
                                FABRIC INSPECTION
                            </td>

                            <!-- Kode Dok (with rowspan) -->
                            <td style="width: 15%; border: 1px solid #000;">Kode Dok</td>
                            <td style="width: 15%; border: 1px solid #000;">:</td>
                            <td style="border: 1px solid #000;"></td>
                        </tr>
                        <tr>
                            <td style="width: 15%; border: 1px solid #000;">Revisi</td>
                            <td style="width: 15%; border: 1px solid #000;">:</td>
                            <td style="border: 1px solid #000;"></td>
                        </tr>
                        <tr>
                            <td style="width: 15%; border: 1px solid #000;">Tanggal Revisi</td>
                            <td style="width: 15%; border: 1px solid #000;">:</td>
                            <td style="border: 1px solid #000;"></td>
                        </tr>
                        <tr>
                            <td style="width: 15%; border: 1px solid #000;">Tanggal Berlaku</td>
                            <td style="width: 15%; border: 1px solid #000;">:</td>
                            <td style="border: 1px solid #000;"></td>
                        </tr>
                    </tbody>
                </table>


                @php
                    $data = $data_header[0];
                @endphp

                <table style="width: 100%; margin-top: 10px; table-layout: fixed; border-collapse: collapse;">
                    <tbody>
                        <tr>
                            @php
                                $tgl_dok = $data->tgl_dok_fix ?? '-';
                                $buyer = $data->buyer ?? '-';
                                $id_item = $data->id_item ?? '-';
                            @endphp

                            <td style="width: 8%; font-weight: bold;">Tgl. BPB</td>
                            <td style="width: 2%;">:</td>
                            <td
                                style="width: 24%; font-size: {{ strlen($tgl_dok) > 25 ? '9px' : '11px' }}; word-wrap: break-word;">
                                {{ $tgl_dok }}</td>

                            <td style="width: 8%; font-weight: bold;">Buyer</td>
                            <td style="width: 2%;">:</td>
                            <td
                                style="width: 24%; font-size: {{ strlen($buyer) > 25 ? '9px' : '11px' }}; word-wrap: break-word;">
                                {{ $buyer }}</td>

                            <td style="width: 8%; font-weight: bold;">ID Item</td>
                            <td style="width: 2%;">:</td>
                            <td
                                style="width: 24%; font-size: {{ strlen($id_item) > 25 ? '9px' : '11px' }}; word-wrap: break-word;">
                                {{ $id_item }}</td>
                        </tr>

                        <tr>
                            @php
                                $no_invoice = $data->no_invoice ?? '-';
                                $style = $data->styleno ?? '-';
                                $fabric = $data->itemdesc ?? '-';
                            @endphp

                            <td style="width: 8%; font-weight: bold;">No. PL</td>
                            <td style="width: 2%;">:</td>
                            <td
                                style="width: 24%; font-size: {{ strlen($no_invoice) > 25 ? '9px' : '11px' }}; word-wrap: break-word;">
                                {{ $no_invoice }}</td>

                            <td style="width: 8%; font-weight: bold;">Style</td>
                            <td style="width: 2%;">:</td>
                            <td
                                style="width: 24%; font-size: {{ strlen($style) > 25 ? '9px' : '11px' }}; word-wrap: break-word;">
                                {{ $style }}</td>

                            <td style="width: 8%; font-weight: bold;">Fabric</td>
                            <td style="width: 2%;">:</td>
                            <td
                                style="width: 24%; font-size: {{ strlen($fabric) > 25 ? '9px' : '11px' }}; word-wrap: break-word;">
                                {{ $fabric }}</td>
                        </tr>

                        <tr>
                            @php
                                $color = $data->color ?? '-';
                                $jml_roll = $data->jml_roll ?? '-';
                                $jml_lot = $data->jml_lot ?? '-';
                            @endphp

                            <td style="width: 8%; font-weight: bold;">Color</td>
                            <td style="width: 2%;">:</td>
                            <td
                                style="width: 24%; font-size: {{ strlen($color) > 25 ? '9px' : '11px' }}; word-wrap: break-word;">
                                {{ $color }}</td>

                            <td style="width: 8%; font-weight: bold;">Jml Roll</td>
                            <td style="width: 2%;">:</td>
                            <td
                                style="width: 24%; font-size: {{ strlen($jml_roll) > 25 ? '9px' : '11px' }}; word-wrap: break-word;">
                                {{ $jml_roll }}</td>

                            <td style="width: 8%; font-weight: bold;">Jml Lot</td>
                            <td style="width: 2%;">:</td>
                            <td
                                style="width: 24%; font-size: {{ strlen($jml_lot) > 25 ? '9px' : '11px' }}; word-wrap: break-word;">
                                {{ $jml_lot }}</td>
                        </tr>

                        <tr>
                            @php
                                $supplier = $data->supplier ?? '-';
                                $type_pch = $data->type_pch ?? '-';
                            @endphp

                            <td style="width: 8%; font-weight: bold;">Supplier</td>
                            <td style="width: 2%;">:</td>
                            <td
                                style="width: 24%; font-size: {{ strlen($supplier) > 25 ? '9px' : '11px' }}; word-wrap: break-word;">
                                {{ $supplier }}</td>

                            <td style="width: 8%; font-weight: bold;">Type PCH</td>
                            <td style="width: 2%;">:</td>
                            <td
                                style="width: 24%; font-size: {{ strlen($type_pch) > 25 ? '9px' : '11px' }}; word-wrap: break-word;">
                                {{ $type_pch }}</td>

                            <td style="width: 8%; font-weight: bold;">Inspect %</td>
                            <td style="width: 2%;">:</td>
                            <td
                                style="width: 24%; font-size: {{ strlen($cek_inspect) > 25 ? '9px' : '11px' }}; word-wrap: break-word;">
                                {{ $cek_inspect }} %</td>
                        </tr>
                    </tbody>
                </table>
                <div style="height: 20px;"></div>
                @foreach ($data_header_form as $index => $dhf)
                    <div class="form-section page-break">
                        <table
                            style="width: 100%; border: 1px solid #000; border-collapse: collapse; table-layout: fixed; margin-top: 10px; page-break-inside: avoid;">
                            <tbody>
                                <tr>
                                    <td style="width: 15%; font-weight: bold; padding: 4px;">No Form</td>
                                    <td style="width: 3%; padding: 4px;">:</td>
                                    <td style="width: 20%; padding: 4px;">{{ $dhf->no_form }}</td>

                                    <td style="width: 15%; font-weight: bold; padding: 4px;">Id Roll</td>
                                    <td style="width: 3%; padding: 4px;">:</td>
                                    <td style="width: 20%; padding: 4px;">{{ $dhf->barcode }}</td>

                                    <td style="width: 10%; font-weight: bold; padding: 4px;">Width</td>
                                    <td style="width: 3%; padding: 4px;">:</td>
                                    <td style="width: 14%; padding: 4px;">{{ $dhf->width }}"</td>
                                </tr>

                                <tr>
                                    <td style="width: 15%; font-weight: bold; padding: 4px;">Date</td>
                                    <td style="width: 3%; padding: 4px;">:</td>
                                    <td style="width: 20%; padding: 4px;">
                                        {{ \Carbon\Carbon::parse($dhf->tgl_form)->format('d-m-Y') }}</td>

                                    <td style="width: 15%; font-weight: bold; padding: 4px;">No Roll</td>
                                    <td style="width: 3%; padding: 4px;">:</td>
                                    <td style="width: 20%; padding: 4px;">{{ $dhf->no_roll }}</td>

                                    <td style="width: 10%; font-weight: bold; padding: 4px;">Gramage</td>
                                    <td style="width: 3%; padding: 4px;">:</td>
                                    <td style="width: 11%; padding: 4px;">{{ $dhf->gramage }}</td>
                                </tr>

                                <tr>
                                    <td style="width: 15%; font-weight: bold; padding: 4px;">Machine</td>
                                    <td style="width: 3%; padding: 4px;">:</td>
                                    <td style="width: 20%; padding: 4px;">{{ $dhf->created_by }}</td>

                                    <td style="width: 10%; font-weight: bold; padding: 4px;">Weight</td>
                                    <td style="width: 3%; padding: 4px;">:</td>
                                    <td style="width: 15%; padding: 4px;">{{ $dhf->weight }}</td>

                                    <td style="width: 8%; font-weight: bold; padding: 4px;">Inspect</td>
                                    <td style="width: 3%; padding: 4px;">:</td>
                                    <td style="width: 10%; padding: 4px;">Ke-{{ $dhf->proses }}</td>
                                </tr>

                                <tr>
                                    <td style="width: 15%; font-weight: bold; padding: 4px;">Inspector</td>
                                    <td style="width: 3%; padding: 4px;">:</td>
                                    <td style="padding: 4px;">{{ $dhf->operator }}</td>

                                    <td style="width: 10%; font-weight: bold; padding: 4px;">No. Lot</td>
                                    <td style="width: 3%; padding: 4px;">:</td>
                                    <td style="width: 15%; padding: 4px;">{{ $dhf->no_lot }}</td>

                                    <td style="width: 8%; font-weight: bold; padding: 4px;">&nbsp;</td>
                                    <td style="width: 3%; padding: 4px;">&nbsp;</td>
                                    <td style="width: 10%; padding: 4px;">&nbsp;</td>
                                </tr>

                                <!-- Nested Result Visual Inspection Table Row -->
                                <tr>
                                    <td colspan="9" style="padding: 0; border: none;">
                                        <table
                                            style="width: 100%; border: 1px solid #000; border-collapse: collapse; table-layout: fixed;">
                                            <!-- Define column widths -->
                                            <colgroup>
                                                <col style="width: 15%;">
                                                <col style="width: 25%;">
                                                <col style="width: 10%;">
                                                <col style="width: 10%;">
                                                <col style="width: 10%;">
                                                <col style="width: 10%;">
                                                <col style="width: 20%;">
                                            </colgroup>

                                            <thead>
                                                <tr>
                                                    <th colspan="7"
                                                        style="border: 1px solid #000; padding: 8px; font-weight: bold; text-align: center; background-color: #f0f0f0;">
                                                        Result Visual Inspection
                                                    </th>
                                                </tr>
                                                <tr>
                                                    <th style="border: 1px solid #000; padding: 6px;">Length</th>
                                                    <th style="border: 1px solid #000; padding: 6px;">Defect Name</th>
                                                    <th style="border: 1px solid #000; padding: 6px;">Up to 3"</th>
                                                    <th style="border: 1px solid #000; padding: 6px;">3" - 6"</th>
                                                    <th style="border: 1px solid #000; padding: 6px;">6" - 9"</th>
                                                    <th style="border: 1px solid #000; padding: 6px;">Over 9"</th>
                                                    <th style="border: 1px solid #000; padding: 6px;">Width</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($inspection_results_grouped[$dhf->no_form] ?? [] as $res)
                                                    <tr>
                                                        <td
                                                            style="border: 1px solid #000; padding: 6px; text-align: center;">
                                                            {{ $res->length }}</td>
                                                        <td
                                                            style="border: 1px solid #000; padding: 6px; word-wrap: break-word;">
                                                            {{ $res->defect_name }}</td>
                                                        <td
                                                            style="border: 1px solid #000; padding: 6px; text-align: center;">
                                                            {{ $res->up_to_3 }}</td>
                                                        <td
                                                            style="border: 1px solid #000; padding: 6px; text-align: center;">
                                                            {{ $res->{'3_6'} }}</td>
                                                        <td
                                                            style="border: 1px solid #000; padding: 6px; text-align: center;">
                                                            {{ $res->{'6_9'} }}</td>
                                                        <td
                                                            style="border: 1px solid #000; padding: 6px; text-align: center;">
                                                            {{ $res->over_9 }}</td>
                                                        <td
                                                            style="border: 1px solid #000; padding: 6px; text-align: center;">
                                                            {{ $res->width }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="7"
                                                            style="border: 1px solid #000; padding: 6px; text-align: center;">
                                                            No visual inspection data available.
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 15%; font-weight: bold; padding: 4px;">Bintex Length</td>
                                    <td style="width: 3%; padding: 4px;">:</td>
                                    <td style="padding: 4px;">{{ $dhf->bintex }}</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td style="width: 15%; font-weight: bold; padding: 4px;">Actual Length</td>
                                    <td style="width: 3%; padding: 4px;">:</td>
                                    <td style="padding: 4px;">{{ $dhf->length }}</td>
                                </tr>

                                <!-- Nested Result Summary Table Row -->
                                <tr>
                                    <td colspan="9" style="padding: 0; border: none;">
                                        <table
                                            style="width: 100%; border: 1px solid #000; border-collapse: collapse; table-layout: fixed;">
                                            <!-- Define column widths -->
                                            <colgroup>
                                                <col style="width: 15%;">
                                                <col style="width: 25%;">
                                                <col style="width: 10%;">
                                                <col style="width: 10%;">
                                                <col style="width: 10%;">
                                                <col style="width: 10%;">
                                                <col style="width: 20%;">
                                            </colgroup>

                                            <thead>
                                                <tr>
                                                    <th colspan="9"
                                                        style="border: 1px solid #000; padding: 8px; font-weight: bold; text-align: center; background-color: #f0f0f0;">
                                                        Summary
                                                    </th>
                                                </tr>
                                                <tr>
                                                    <th style="border: 1px solid #000; padding: 6px;">Up to 3"</th>
                                                    <th style="border: 1px solid #000; padding: 6px;">3" - 6"</th>
                                                    <th style="border: 1px solid #000; padding: 6px;">6" - 9"</th>
                                                    <th style="border: 1px solid #000; padding: 6px;">Over 9"</th>
                                                    <th style="border: 1px solid #000; padding: 6px;">Width</th>
                                                    <th style="border: 1px solid #000; padding: 6px;">Total Point</th>
                                                    <th style="border: 1px solid #000; padding: 6px;">Actual Point</th>
                                                    <th style="border: 1px solid #000; padding: 6px;">Max Point</th>
                                                    <th style="border: 1px solid #000; padding: 6px;">Result</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($data_summary_grouped[$dhf->no_form] ?? [] as $dsg)
                                                    <tr>
                                                        <td
                                                            style="border: 1px solid #000; padding: 6px; text-align: center;">
                                                            {{ $dsg->sum_up_to_3 }}</td>
                                                        <td
                                                            style="border: 1px solid #000; padding: 6px; text-align: center;">
                                                            {{ $dsg->sum_3_6 }}</td>
                                                        <td
                                                            style="border: 1px solid #000; padding: 6px; text-align: center;">
                                                            {{ $dsg->sum_6_9 }}</td>
                                                        <td
                                                            style="border: 1px solid #000; padding: 6px; text-align: center;">
                                                            {{ $dsg->sum_over_9 }}</td>
                                                        <td
                                                            style="border: 1px solid #000; padding: 6px; text-align: center;">
                                                            {{ $dsg->avg_width }}</td>
                                                        <td
                                                            style="border: 1px solid #000; padding: 6px; text-align: center;">
                                                            {{ $dsg->tot_point }}</td>
                                                        <td
                                                            style="border: 1px solid #000; padding: 6px; text-align: center;">
                                                            {{ $dsg->act_point }}</td>
                                                        <td
                                                            style="border: 1px solid #000; padding: 6px; text-align: center;">
                                                            {{ $dsg->individu }}</td>
                                                        <td
                                                            style="border: 1px solid #000; padding: 6px; text-align: center;">
                                                            {{ $dsg->result }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="7"
                                                            style="border: 1px solid #000; padding: 6px; text-align: center;">
                                                            No data available.
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>
                    <div style="height: 20px;"></div>
                @endforeach

            </div>

            <table style="width: 100%; margin-top: 40px; table-layout: fixed;">
                <tr>
                    <!-- SPV Signature -->
                    <td style="width: 50%; text-align: center;">
                        SPV<br><br><br>
                        ___________________________<br>
                        <span style="font-size: 12px;"></span>
                    </td>

                    <!-- QA Manager Signature -->
                    <td style="width: 50%; text-align: center;">
                        QA Manager<br><br><br>
                        ___________________________<br>
                        <span style="font-size: 12px;"></span>
                    </td>
                </tr>
            </table>

        </div>
    </div>
</body>

</html>
