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
                            <td rowspan="4" style="width: 15%; border: 1px solid #000; text-align: center;">
                                <img src="{{ public_path('nag-logo.png') }}" style="max-width: 80px; height: auto;">
                            </td>

                            <!-- Title Cell -->
                            <td rowspan="4"
                                style="width: 47%; border: 1px solid #000; text-align: center; vertical-align: middle; font-size: 18px; font-weight: bold;">
                                FORM SHADE BAND
                            </td>

                            <td style="width: 14%; border: 1px solid #000; border-right: none;">Kode Dok</td>
                            <td style="width: 1%; border: 1px solid #000; border-left: none; border-right: none;">
                                :
                            </td>
                            <td
                                style="width: 18%; border: 1px solid #000; border-left: none; font-size:10px;text-align: right;">
                                F.17.QA.NAG.P-02.F-02.01
                            </td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #000; border-right: none;">Revisi</td>
                            <!-- remove right border -->
                            <td style="width: 1%; border: 1px solid #000; border-left: none; border-right: none;">
                                :
                            </td>
                            <td style="border: 1px solid #000; border-left: none;"></td> <!-- remove left border -->
                        </tr>
                        <tr>
                            <td style="border: 1px solid #000; border-right: none;">Tanggal Revisi</td>
                            <td style="width: 1%; border: 1px solid #000; border-left: none; border-right: none;">:</td>
                            <td style="border: 1px solid #000; border-left: none;"></td>
                        </tr>
                        <tr>
                            <td style="border: 1px solid #000; border-right: none;">Tanggal Berlaku</td>
                            <td style="width: 1%; border: 1px solid #000; border-left: none; border-right: none;">:</td>
                            <td style="border: 1px solid #000; border-left: none; font-size:10px;text-align: right;">
                                30 September 2017</td>
                        </tr>
                        td style="border: 1px solid #000;"></td>
                        </tr>
                    </tbody>
                </table>
                <div style="height: 20px;"></div>
            </div>
            @php
                $data = $data_header[0];
            @endphp
            @php
                $tgl_update_fix = $data->tgl_update_fix ?? '-';
                $color = $data->color ?? '-';
                $supplier = $data->supplier ?? '-';
                $tot_barcode = $data->tot_barcode ?? '-';
                $buyer = $data->buyer ?? '-';
                $group = $data->group ?? '-';
                $ws = $data->kpno ?? '-';
                $result = $data->result ?? '-';
                $style = $data->styleno ?? '-';
                $id_item = $data->id_item ?? '-';
                $itemdesc = $data->itemdesc ?? '-';
            @endphp
            <table
                style="width: 100%; border-collapse: collapse; table-layout: fixed; line-height: 1.8; font-size: 11px;">
                <tbody>
                    <tr>
                        <td style="font-weight: bold; width: 15%;">Date</td>
                        <td style="width: 2%;">:</td>
                        <td style="width: 33%;">
                            {{ $tgl_update_fix }}
                        </td>
                        <td
                            style="font-weight: bold; width: 15%; text-align: right; padding-right: 0; margin-right: -2px;">
                            Color
                        </td>
                        <td style="width: 2%; text-align: center;">:</td>
                        <td style="width: 33%;">
                            {{ $color }}
                        </td>
                    </tr>

                    <tr>
                        <td style="font-weight: bold; width: 15%;">Supplier</td>
                        <td style="width: 2%;">:</td>
                        <td style="width: 33%;">
                            {{ $supplier }}
                        </td>
                        <td
                            style="font-weight: bold; width: 15%; text-align: right; padding-right: 0; margin-right: -2px;">
                            Jml Roll
                        </td>
                        <td style="width: 2%; text-align: center;">:</td>
                        <td style="width: 33%;">
                            {{ $tot_barcode }}
                        </td>
                    </tr>

                    <tr>
                        <td style="font-weight: bold; width: 15%;">Buyer</td>
                        <td style="width: 2%;">:</td>
                        <td style="width: 33%;">
                            {{ $buyer }}
                        </td>
                        <td
                            style="font-weight: bold; width: 15%; text-align: right; padding-right: 0; margin-right: -2px;">
                            Group
                        </td>
                        <td style="width: 2%; text-align: center;">:</td>
                        <td style="width: 33%;">
                            {{ $group }}
                        </td>
                    </tr>

                    <tr>
                        <td style="font-weight: bold; width: 15%;">Worksheet</td>
                        <td style="width: 2%;">:</td>
                        <td style="width: 33%;">
                            {{ $ws }}
                        </td>
                        <td
                            style="font-weight: bold; width: 15%; text-align: right; padding-right: 0; margin-right: -2px;">
                            Result
                        </td>
                        <td style="width: 2%; text-align: center;">:</td>
                        <td style="width: 33%;">
                            {{ $result }}
                        </td>
                    </tr>

                    <tr>
                        <td style="font-weight: bold; width: 15%;">Style</td>
                        <td style="width: 2%;">:</td>
                        <td style="width: 33%;">
                            {{ $style }}
                        </td>
                        <td
                            style="font-weight: bold; width: 15%; text-align: right; padding-right: 0; margin-right: -2px;">
                            ID Item
                        </td>
                        <td style="width: 2%; text-align: center;">:</td>
                        <td style="width: 33%;">
                            {{ $id_item }}
                        </td>
                    </tr>

                    <tr>
                        <td style="font-weight: bold; width: 15%;">Item</td>
                        <td style="width: 2%;">:</td>
                        <td colspan="4" style="width: 83%;">
                            {{ $itemdesc }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <div style="height: 20px;"></div>

            <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; table-layout: fixed;">
                <colgroup>
                    <col style="width: 20%;">
                    <col style="width: 10%;">
                    <col style="width: 20%;">
                    <col style="width: 20%;">
                    <col style="width: 10%;">
                    <col style="width: 20%;">
                </colgroup>

                <thead>
                    <tr>
                        <th style="border: 1px solid #000; padding: 6px;">No. PL</th>
                        <th style="border: 1px solid #000; padding: 6px;">Barcode</th>
                        <th style="border: 1px solid #000; padding: 6px;">No. Roll</th>
                        <th style="border: 1px solid #000; padding: 6px;">Lot</th>
                        <th style="border: 1px solid #000; padding: 6px;">Qty</th>
                        <th style="border: 1px solid #000; padding: 6px;">Unit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data_detail as $dd)
                        <tr>
                            <td style="border: 1px solid #000; padding: 6px; text-align: center;">
                                {{ $dd->no_invoice }}</td>
                            <td style="border: 1px solid #000; padding: 6px; text-align: center;">
                                {{ $dd->barcode }}</td>
                            <td style="border: 1px solid #000; padding: 6px; text-align: center;">
                                {{ $dd->no_roll_buyer }}</td>
                            <td style="border: 1px solid #000; padding: 6px; text-align: center;">
                                {{ $dd->no_lot }}</td>
                            <td style="border: 1px solid #000; padding: 6px; text-align: center;">
                                {{ $dd->qty_aktual }}</td>
                            <td style="border: 1px solid #000; padding: 6px; text-align: center;">
                                {{ $dd->satuan }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <table
                style="width: 100%; border: 1px solid #000; border-collapse: collapse; table-layout: fixed; margin-top: 20px;">
                <tbody>
                    <!-- Title: Gambar (centered) -->
                    <tr>
                        <td
                            style="font-weight: bold; border: 1px solid #000; padding: 10px; text-align: center; font-size: 12px;">
                            Gambar
                        </td>
                    </tr>

                    <!-- Image Row (larger & centered) -->
                    <tr>
                        <td style="border: 1px solid #000; padding: 12px; text-align: center;">
                            <img src="{{ storage_path('app/public/gambar_shade_band/' . $photo) }}"
                                style="display: block; margin: 0 auto; width: 400px; height: auto;">

                        </td>
                    </tr>

                    <!-- Title: Keterangan (centered) -->
                    <tr>
                        <td
                            style="font-weight: bold; border: 1px solid #000; padding: 10px; text-align: center; font-size: 12px;">
                            Keterangan
                        </td>
                    </tr>

                    <!-- Value: Keterangan -->
                    <tr>
                        <td style="border: 1px solid #000; padding: 10px; font-size: 12px;text-align: center;">
                            {{ $ket }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <table style="width: 100%; border-collapse: collapse; border: 1px solid #000; margin-top: 30px;">
                <thead>
                    <tr>
                        <th style="border: 1px solid #000; text-align: center; padding: 10px;">PREPARED</th>
                        <th style="border: 1px solid #000; text-align: center; padding: 10px;">ACKNOWLEDGED</th>
                        <th style="border: 1px solid #000; text-align: center; padding: 10px;">ACKNOWLEDGED</th>
                        <th style="border: 1px solid #000; text-align: center; padding: 10px;">APPROVED</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border: 1px solid #000; height: 80px;"></td>
                        <td style="border: 1px solid #000;"></td>
                        <td style="border: 1px solid #000;"></td>
                        <td style="border: 1px solid #000;"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>
