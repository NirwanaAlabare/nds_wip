<!DOCTYPE html>
<html>
<head>
    <title>Pengeluaran Gudang Inputan (FABRIC)</title>
    <style>
        @page { margin: 1px; }

        @font-face {
            font-family: 'Open Sans';
            font-style: normal;
            font-weight: bold;
            src: url({{ storage_path("OpenSans-Bold.ttf") }}) format('truetype');
        }

        body {
            margin: 0.0px;
            font-family: 'Open Sans', sans-serif;
            font-weight: bold;
        }

        * {
            font-size: 8px;
        }

        img {
            width: 40px;

        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table td, table th{
            text-align: left;
            vertical-align: middle;
            padding: 0.0px;
            border: 1px solid;
        }
    </style>
</head>
<body>
    @foreach($data as $row)
    <div style=" padding: 5px; @if(!$loop->last) page-break-after: always; @endif">
        <div> PENGELUARAN GUDANG INPUTAN (FABRIC)</div><br>
        <table style="width: 100%;margin-bottom: 5px;">
            <tr>
                <th style="border: none;vertical-align: middle; width: 70px;">No BPB</th>
                <td style="border: none;vertical-align: middle;padding: 2px; width: 10px;"> : </td>
                <th style="border: none;vertical-align: middle;">{{ $row->no_bpb }}</th>
            </tr>
            <tr>
                <th style="border: none;vertical-align: middle;">Tgl BPB</th>
                <td style="border: none;vertical-align: middle;padding: 2px;"> : </td>
                <th style="border: none;vertical-align: middle;">{{ $row->tgl_bpb }}</th>
            </tr>
            {{-- <tr>
                <th style="border: none;vertical-align: middle;">Jenis Item</th>
                <td style="border: none;vertical-align: middle;padding: 2px;"> : </td>
                <th style="border: none;vertical-align: middle;">{{ $row->jenis_item }}</th>
            </tr> --}}
        </table>

        <br>
        <table style="width: 100%;margin-bottom: 5px;">
            <tr>
                <th style="padding-left: 1px;font-size: 7px;">Lokasi</th>
                <th style="padding-left: 1px;font-size: 7px;">Buyer</th>
                <th style="padding-left: 1px;font-size: 7px;">Jenis Item</th>
                <th style="padding-left: 1px;font-size: 7px;">Warna</th>
                <th style="padding-left: 1px;font-size: 7px;">Lot</th>
                <th style="padding-left: 1px;font-size: 7px;">No Roll</th>
                <th style="padding-left: 1px;font-size: 7px;">Qty</th>
                <th style="padding-left: 1px;font-size: 7px;">Satuan</th>
                <th style="padding-left: 1px;font-size: 7px;">Qty Out</th>
            </tr>
            <tr>
                <td style="padding-left: 1px; padding-right: 1px; font-size: 7px;">{{ $row->lokasi }}</td>
                <td style="padding-left: 1px; padding-right: 1px; font-size: 7px;">{{ $row->buyer }}</td>
                <td style="padding-left: 1px; padding-right: 1px; font-size: 7px;">{{ $row->jenis_item }}</td>
                <td style="padding-left: 1px; padding-right: 1px; font-size: 7px;">{{ $row->warna }}</td>
                <td style="padding-left: 1px; padding-right: 1px; font-size: 7px;">{{ $row->lot }}</td>
                <td style="padding-left: 1px; padding-right: 1px; font-size: 7px;">{{ $row->no_roll }}</td>
                <td style="padding-left: 1px; padding-right: 1px; font-size: 7px; text-align: right;">{{ number_format($row->qty_act, 2) }}</td>
                <td style="padding-left: 1px; padding-right: 1px; font-size: 7px;">{{ $row->satuan }}</td>
                <td style="padding-left: 1px; padding-right: 1px; font-size: 7px; text-align: right;">{{ number_format($row->qty_out, 2) }}</td>
            </tr>
        </table>
        
        <br>

        <table style="margin-bottom: 5px;">
            <tr>
                <td style="text-align: center; width: auto;">
                    <div style="padding-top: 25px; padding-bottom: 25px;">
                        <img src="data:image/png;base64, {!! DNS1D::getBarcodePNG($row->barcode, 'c39', 2, 70) !!}" style="width: 150px; padding-bottom: 3px;">
                        <br>
                        <span style="font-weight: bold; font-size: 15px;">
                            {{ $row->barcode }}
                        </span>
                    </div>
                </td>
            </tr>
        </table>

    </div>
    @endforeach
</body>
</html>
