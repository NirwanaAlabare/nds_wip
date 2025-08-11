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

    <style>
        .light-border td,
        .light-border th {
            border: 0.5px solid #000 !important;
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
                                FORM DEFECT CARD
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
                @php
                    $tgl_dok = $data->tgl_dok_fix ?? '-';
                    $buyer = $data->buyer ?? '-';
                    $color = $data->color ?? '-';
                    $fabric = $data->itemdesc ?? '-';
                    $id_item = $data->id_item ?? '-';
                    $no_invoice = $data->no_invoice ?? '-';
                    $style = $data->styleno ?? '-';
                    $jml_roll = $data->jml_roll ?? '-';
                    $jml_lot = $data->jml_lot ?? '-';
                    $supplier = $data->supplier ?? '-';
                    $type_pch = $data->type_pch ?? '-';
                @endphp

                <table
                    style="width: 100%; margin-top: 10px; border-collapse: collapse; table-layout: auto; line-height: 1.8;">
                    <tr>
                        <!-- LEFT SIDE -->
                        <td style="width: 50%; vertical-align: top;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td style="width: 30%; font-weight: bold;">Description</td>
                                    <td style="width: 5%; font-weight: bold;">:</td>
                                    <td style="font-size: {{ strlen($fabric) > 25 ? '9px' : '11px' }};">
                                        {{ $fabric }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">Color</td>
                                    <td style="font-weight: bold;">:</td>
                                    <td style="font-size: {{ strlen($color) > 25 ? '9px' : '11px' }};">
                                        {{ $color }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">Supplier</td>
                                    <td style="font-weight: bold;">:</td>
                                    <td style="font-size: {{ strlen($supplier) > 25 ? '9px' : '11px' }};">
                                        {{ $supplier }}
                                    </td>
                                </tr>
                            </table>
                        </td>

                        <!-- RIGHT SIDE -->
                        <td style="width: 50%; vertical-align: top;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td style="width: 30%; font-weight: bold;">Buyer</td>
                                    <td style="width: 5%; font-weight: bold;">:</td>
                                    <td style="font-size: {{ strlen($buyer) > 25 ? '9px' : '11px' }};">
                                        {{ $buyer }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">Style</td>
                                    <td style="font-weight: bold;">:</td>
                                    <td style="font-size: {{ strlen($style) > 25 ? '9px' : '11px' }};">
                                        {{ $style }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-weight: bold;">Date</td>
                                    <td style="font-weight: bold;">:</td>
                                    <td style="font-size: {{ strlen($tgl_dok) > 25 ? '9px' : '11px' }};">
                                        {{ $tgl_dok }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <div style="height: 20px;"></div>

                <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; table-layout: fixed;">
                    <colgroup>
                        <col style="width: 20%;">
                        <col style="width: 20%;">
                        <col style="width: 20%;">
                        <col style="width: 20%;">
                        <col style="width: 20%;">
                    </colgroup>

                    <thead>
                        <tr>
                            <th colspan="2"
                                style="border: 1px solid #000; padding: 8px; font-weight: bold; text-align: center; background-color: #f0f0f0;">
                                Fabric Defect
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data_list as $dl)
                            <tr>
                                <td style="border: 1px solid #000; padding: 6px; text-align: center;">
                                    <img src="{{ storage_path('app/public/gambar_defect_inspect/' . $dl->photo) }}"
                                        alt="Photo" style="width: 200px; height: auto;">
                                </td>

                                <td style="border: 1px solid #000; padding: 6px; text-align: center;">
                                    {{ $dl->critical_defect }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <table style="width: 100%; border-collapse: collapse; margin-top: 30px;">
                    <tr>
                        <td style="width: 25%; border: 1px solid #000;text-align: center; vertical-align: middle;">
                            Prepared
                        </td>
                        <td style="width: 25%; border: 1px solid #000;text-align: center; vertical-align: middle;">
                            Acknowledged
                        </td>
                        <td style="width: 25%; border: 1px solid #000;text-align: center; vertical-align: middle;">
                            Acknowledged
                        </td>
                        <td style="width: 25%; border: 1px solid #000;text-align: center; vertical-align: middle;">
                            Approved
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; height: 40px;"></td>
                        <td style="border: 1px solid #000; height: 40px;"></td>
                        <td style="border: 1px solid #000; height: 40px;"></td>
                        <td style="border: 1px solid #000; height: 40px;"></td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; height: 20px;"></td>
                        <td style="border: 1px solid #000; height: 20px;"></td>
                        <td style="border: 1px solid #000; height: 20px;"></td>
                        <td style="border: 1px solid #000; height: 20px;"></td>
                    </tr>
                </table>




            </div>
        </div>
    </div>
</body>

</html>
