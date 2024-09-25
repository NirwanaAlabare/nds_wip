<!DOCTYPE html>
<html>
<head>
    <title>lokasi</title>
    <style>
        @page { margin: 5px; }

        body { margin: 5px; }
/*
        html,body{
    width: 100%;
    height: 100%;
    margin-left: 0; 
    padding: 0;
    border: solid black;
    border-width: thin;
    overflow:hidden;
    display:block;
    box-sizing: border-box;
}*/

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
<body >
    <br>
    <br>
    <br>
    <table width="100%">
        <tr style="line-height: 10px;">
            <td colspan="3" style="vertical-align: top; text-align: center;width:60%;font-size: 15px;padding-top: -5px;">
             PT. NIRWANA ALABARE GARMENT
            </td>
        </tr>

        <tr style="line-height: 40px;">
            <td style="width:20%;border-top: 1px solid;"></td>
            <td style="vertical-align: top; text-align: center;width:60%;font-size: 20px;border-top: 1px solid;padding-top: -5px;">
             FABRIC LOCATION
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
             <!-- <div style="text-align: center;" class="mb-2">{!!  DNS1D::getBarcodeHTML($dataLokasi->id, 'c39',2,80,'black', false); !!}</div> -->
             <img style="width: 40%" src="data:image/png;base64, {!! base64_encode(QrCode::format('svg')->size(100)->generate('http://nag.ddns.net:8080//generateQr/index.php?rak='.$dataLokasi->kode)) !!}">
             <!-- {{$dataLokasi->id}} -->
            </td>
            <td style="width:15%"></td>
        </tr>
        <tr style="line-height: 30px;">
            <td style="width:20%;"></td>
            <td style="width:60%;text-align: center;font-size: 18px;">{{ $dataLokasi->kode }}</td>
            <td style="width:20%"></td>
        </tr>
        <tr>
            <td style="width:20%;"></td>
            <td style="width:20%;"></td>
            <td style="width:20%;"></td>
        </tr>
        <tr>
            <td style="width:20%;border-bottom: 1px solid;"></td>
            <td style="width:20%;border-bottom: 1px solid;"></td>
            <td style="width:20%;border-bottom: 1px solid;"></td>
        </tr>
    </table>
</body>
</html>
