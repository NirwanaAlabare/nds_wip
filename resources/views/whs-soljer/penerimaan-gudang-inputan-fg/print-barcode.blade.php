<!DOCTYPE html>
<html>
<head>
    <title>Penerimaan Gudang Inputan (FG)</title>
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
        <div> PENERIMAAN GUDANG INPUTAN (FG)</div><br>
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
                <th style="padding-left: 1px;font-size: 7px;">No Koli</th>
                <th style="padding-left: 1px;font-size: 7px;">Buyer</th>
                <th style="padding-left: 1px;font-size: 7px;">No WS</th>
                <th style="padding-left: 1px;font-size: 7px;">Style</th>
                <th style="padding-left: 1px;font-size: 7px;">Product Item</th>
                <th style="padding-left: 1px;font-size: 7px;">Warna</th>
                <th style="padding-left: 1px;font-size: 7px;">Size</th>
                <th style="padding-left: 1px;font-size: 7px;">Grade</th>
                <th style="padding-left: 1px;font-size: 7px;">Qty</th>
                <th style="padding-left: 1px;font-size: 7px;">Satuan</th>
                <th style="padding-left: 1px;font-size: 7px;">Keterangan</th>
                <th style="padding-left: 1px;font-size: 7px;">Lokasi</th>
            </tr>
            <tr>
                <td style="padding-left: 1px;font-size: 7px;">{{ $row->no_koli }}</td>
                <td style="padding-left: 1px;font-size: 7px;">{{ $row->buyer }}</td>
                <td style="padding-left: 1px;font-size: 7px;">{{ $row->no_ws }}</td>
                <td style="padding-left: 1px;font-size: 7px;">{{ $row->style }}</td>
                <td style="padding-left: 1px;font-size: 7px;">{{ $row->product_item }}</td>
                <td style="padding-left: 1px;font-size: 7px;">{{ $row->warna }}</td>
                <td style="padding-left: 1px;font-size: 7px;">{{ $row->size }}</td>
                <td style="padding-left: 1px;font-size: 7px;">{{ $row->grade }}</td>
                <td style="padding-left: 1px;font-size: 7px;">{{ $row->qty }}</td>
                <td style="padding-left: 1px;font-size: 7px;">{{ $row->satuan }}</td>
                <td style="padding-left: 1px;font-size: 7px;">{{ $row->keterangan }}</td>
                <td style="padding-left: 1px;font-size: 7px;">{{ $row->lokasi }}</td>
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
