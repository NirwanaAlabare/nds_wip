<!DOCTYPE html>
<html>
<head>
    <title>lokasi</title>
    <style>
        @page { margin: 15px; }

        body { margin: 15px; 
                font-family: Calibri, Helvetica, Arial, sans-serif;}

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

        .table{
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
        .table2 td{
            background-color: #fff;
        }
        .table2 th {
            background-color: #fff;
        }


    </style>
</head>
<body>
    
    <div class="card-body">
    <div class="form-group row">
    <div class="col-md-12 mb-3">
        <table class="table2" style="border-bottom: 2px solid #000000; margin-bottom:5px;">
    <tr>
        <td><img src="{{ public_path('nag-logo.png') }}"  width="auto" height="auto"> </td>
        <td style="text-align: right;vertical-align: bottom;font-size: 14px;"><?=strtoupper("PT. NIRWANA ALABARE GARMENT")?></td>
    </tr>
</table>
        <div class="table-responsive">
        @foreach ($data_header as $dheader)
        <table width="100%" class="text-nowrap">
            <tr>
                <td style="width: 18%;"><b>Inspection Number</b></td>
                <td style="width: 2%;"><b>:</b></td>
                <td>{{ $dheader->no_insp }} </td>
                <td style="width: 7%;"><b>ID Item</b></td>
                <td style="width: 2%;"><b>:</b></td>
                <td>{{ $dheader->id_item }} </td>
                <td style="width: 6%;"><b>Style</b></td>
                <td style="width: 2%;"><b>:</b></td>
                <td>{{ $dheader->no_style }} </td>
            </tr>
            <tr>
                <td><b>Inspection Date</b></td>
                <td><b>:</b></td>
                <td><?= date("d F Y",strtotime($dheader->tgl_insp)) ?> </td>
                <td><b>Lot</b></td>
                <td><b>:</b></td>
                <td>{{ $dheader->no_lot }} </td>
                <td><b>Color</b></td>
                <td><b>:</b></td>
                <td>{{ $dheader->color }}</td>
            </tr>
            <tr>
                <td><b>Average Actual Point</b></td>
                <td><b>:</b></td>
                 @foreach ($avg_poin as $avgpoin)
                <td><b><{{ $avgpoin->avg_poin }}</b></td>
                @endforeach
                <td><b>Status</b></td>
                <td><b>:</b></td>
                <td><b>{{ $dheader->status }}</b></td>
            </tr>
        </table>
        @endforeach
        @foreach ($data_header as $dheader2)
        <table width="100%" class="text-nowrap">
            <tr>
                <td style="width: 18%;"><b>Fabric Name</b></td>
                <td style="width: 2%;"><b>:</b></td>
                <td>{{ $dheader2->fabricname }} </td>
            </tr>
        </table>
        <br>
        @endforeach
        </div>
    </div>

        @foreach ($data_detail as $ddetail)
    <div class="col-md-12 mb-3" style="border: solid 1px;page-break-inside: avoid;">
        <div class="table-responsive">
        <table width="100%" class="text-nowrap">
            <tr>
                <td><b>Form Number:</b> {{ $ddetail->no_form }} </td>
                <td><b>Width:</b> {{ $ddetail->width_fabric }} Inch </td>
                <td><b>Weight:</b> {{ $ddetail->weight_fabric }} Kg</td>
            </tr>
            <tr>
                <td><b>Date:</b> {{ $ddetail->tgl_form }} </td>
                <td><b>Gramage:</b> {{ $ddetail->gramage }} </td>
                <td><b>Inspector:</b> {{ $ddetail->inspektor }} </td>
            </tr>
            <tr>
                <td><b>Fabric Supplier:</b> {{ $ddetail->fabric_supp }} </td>
                <td><b>Roll:</b> {{ $ddetail->no_roll }}</td>
                <td><b>Machine No:</b> {{ $ddetail->no_mesin }}</td>
            </tr>
        </table>
        <br>
        <table class="text-nowrap" width="100%" border="1">
                <thead>
                    <tr>
                        <th class="text-center">Length</th>
                        <th class="text-center">Defect Name</th>
                        <th class="text-center">Code</th>
                        <th class="text-center">Up To 3"</th>
                        <th class="text-center">Over 3" - 6"</th>
                        <th class="text-center">Over 6" - 9"</th>
                        <th class="text-center">Over 9"</th>
                        <th class="text-center">Width</th>
                    </tr>
                </thead>
                <tbody>
        @foreach ($data_temuan as $dtemuan)
        @if( $dtemuan->no_form == $ddetail->no_form)
                    <tr>
                        <td>{{ $dtemuan->lenght_fabric }}</td>
                        <td><?= str_replace(',', '<br>',$dtemuan->nama_defect) ?></td>
                        <td><?= str_replace(',', '/',$dtemuan->kode_def) ?></td>
                        <td><?= str_replace(',', '/',$dtemuan->upto3) ?></td>
                        <td><?= str_replace(',', '/',$dtemuan->over3) ?></td>
                        <td><?= str_replace(',', '/',$dtemuan->over6) ?></td>
                        <td><?= str_replace(',', '/',$dtemuan->over9) ?></td>
                        <td>{{ $dtemuan->width_det }}</td>
                    </tr>
        @endif
        @endforeach
                </tbody>
            </table>
            <br>
        <table width="100%" class="text-nowrap">
            <tr>
                <td><b>Barcode Length:</b> {{ $ddetail->lenght_barcode }} Yard </td>
                <td><b>Remark:</b> {{ $ddetail->catatan }} </td>
            </tr>
            <tr>
                <td><b>Actual Length:</b> {{ $ddetail->lenght_actual }} Yard</td>
                @foreach ($data_sum as $dsum2)
                @if( $dsum2->no_form == $ddetail->no_form)
                @if( $dsum2->akt_poin > 20)
                <td><b>Status: REJECT</b></td>
                @else
                <td><b>Status: PASS</b> </td>
                @endif
                @endif
                @endforeach
            </tr>
        </table>
        <br>
        <table class="text-nowrap" width="100%"  border="1">
                <thead>
                    <tr>
                        <th class="text-center">Up To 3"</th>
                        <th class="text-center">Over 3" - 6"</th>
                        <th class="text-center">Over 6" - 9"</th>
                        <th class="text-center">Over 9"</th>
                        <th class="text-center">Width</th>
                        <th class="text-center">Total Point</th>
                        <th class="text-center">Actual Point</th>
                    </tr>
                </thead>
                <tbody>
        @foreach ($data_sum as $dsum)
        @if( $dsum->no_form == $ddetail->no_form)
                    <tr>
                        <td>{{ $dsum->upto3 }}</td>
                        <td>{{ $dsum->over3 }}</td>
                        <td>{{ $dsum->over6 }}</td>
                        <td>{{ $dsum->over9 }}</td>
                        <td>{{ $dsum->width_fabric }}</td>
                        <td>{{ $dsum->ttl_poin }}</td>
                        <td>{{ $dsum->akt_poin }}</td>
                    </tr>
        @endif
        @endforeach
                </tbody>
            </table>
            <br>
        </div>
    </div>
    <br>
        @endforeach



    </div>
</div>
</div>

    
</body>
</html>
