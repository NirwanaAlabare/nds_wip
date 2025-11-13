<!DOCTYPE html>
<html>
<head>
  <title>All Fabric Locations</title>
  <style>
        @page { margin: 2px; }

        body { margin: 2px; }


        * {
            font-size: 13px;
        }

        img {
            width: 69px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table td, table th{
            text-align: left;
            vertical-align: middle;
            padding: 1px 3px;
            border: 0px solid;

            width: auto;
        }
    </style>
</head>
<body>
  @foreach($listLokasi as $dataLokasi)
    
    <table width="100%">
        <tr style="line-height: 25px;">
            <td colspan="4" style="vertical-align: middle; text-align: center;width:100%;font-size: 15px;">
             PT. NIRWANA ALABARE GARMENT
            </td>
        </tr>

        <tr style="line-height: 30px;">
            <td style="width:20%;border-top: 1px solid;"></td>
            <td style="text-align:center; width:60%; font-size:18px; border-top:1px solid; padding-top:5px;">
                <span style="display:inline-block; vertical-align:middle; line-height:1;">
                <img src="{{ public_path('location-logo.png') }}" style="width:20px; height:auto; display:block;">
                </span>
                <span style="display:inline-block; vertical-align:middle; margin-left:6px; font-weight:bold;font-size: 18px;">
                    FABRIC LOCATION
                </span>
            </td>

            <td style="width:20%;border-top: 1px solid;"></td>
        </tr>

        <tr style="line-height:20%">
            <td style="width:20%"></td>
            <td style="vertical-align: middle; text-align: center;width:60%;margin-bottom: 10px;"></td>
            <td style="width:20%"></td>
        </tr>
        <tr>
            <td style="width:15%"></td>
            <td style="vertical-align: middle; text-align: center;width:70%">
             <!-- <div style="text-align: center;" class="mb-2">{!!  DNS1D::getBarcodeHTML($dataLokasi->id, 'C128',2,80,'black', false); !!}</div> -->
             <img style="width: 40%" src="data:image/png;base64, {!! base64_encode(QrCode::format('svg')->size(100)->generate('http://nag.ddns.net:8080//generateQr/index.php?rak='.$dataLokasi->kode)) !!}">
             <!-- {{$dataLokasi->id}} -->
            </td>
            <td style="width:15%"></td>
        </tr>
        <tr style="line-height: 30px;">
            <td style="width:20%;"></td>
            <td style="width:60%;text-align: center;font-size: 18px;">[Scan for Stock Info]</td>
            <td style="width:20%"></td>
        </tr>
        <tr style="line-height: 25px;">
            <td style="width:20%;"></td>
            <td style="width:60%;text-align: center;font-size: 18px;font-weight:bold;">{{ $dataLokasi->kode }}</td>
            <td style="width:20%"></td>
        </tr>
        <tr style="line-height: 30px;">
            <td style="width:20%;"></td>
            <td style="width:60%;text-align: center;font-size: 18px;"></td>
            <td style="width:20%"></td>
        </tr>
        <tr>
            <td style="width:20%;border-bottom: 1px solid;"></td>
            <td style="width:20%;border-bottom: 1px solid;"></td>
            <td style="width:20%;border-bottom: 1px solid;"><div style="text-align: center;" class="mb-2"></div></td>
        </tr>
        <tr style="line-height: 15px;">
            <td style="vertical-align: middle; text-align: left;width:25%">
             <img style="width: 45%" src="data:image/png;base64, {!! base64_encode(QrCode::format('svg')->size(100)->generate($dataLokasi->kode)) !!}">
            </td>
            <td colspan="3" style="vertical-align: middle; text-align: left;width:60%;font-size: 15px;">
            </td>
        </tr>
        <!-- {!!  DNS1D::getBarcodeHTML($dataLokasi->id, 'C128',1,40,'black', false); !!} -->
    </table>
    <br>
  @endforeach
</body>
</html>
