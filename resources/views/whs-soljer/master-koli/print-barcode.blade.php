<!DOCTYPE html>
<html>
<head>
    <title>MASTER KOLI</title>
    <style>
        @page { margin: 5px; }

        body { margin: 5px; }


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
    

    <table width="100%">
        {{-- <tr style="line-height: 25px;">
            <td colspan="4" style="vertical-align: middle; text-align: center;width:100%;font-size: 15px;">
             PT. NIRWANA ALABARE GARMENT
            </td>
        </tr> --}}
        <tr style="line-height: 25px;">
            <td colspan="3" style="height: 50px;"></td>
        </tr>

        <tr style="line-height: 30px;">
            <td style="width:20%;border-top: 1px solid;"></td>
            <td style="text-align:center; width:60%; font-size:18px; border-top:1px solid; padding-top:5px;">
                <span style="display:inline-block; vertical-align:middle; line-height:1;">
                <img src="{{ public_path('location-logo.png') }}" style="width:20px; height:auto; display:block;">
                </span>
                <span style="display:inline-block; vertical-align:middle; margin-left:6px; font-weight:bold;font-size: 18px;">
                    MASTER KOLI
                </span>
            </td>

            <td style="width:20%;border-top: 1px solid;"></td>
        </tr>

        <tr style="line-height: 25px;">
            <td colspan="3" style="height: 5px;"></td>
        </tr>

        <tr style="line-height:20%">
            <td style="width:20%"></td>
            <td style="vertical-align: middle; text-align: center;width:60%;margin-bottom: 10px;"></td>
            <td style="width:20%"></td>
        </tr>
        <tr>
            <td style="width:15%"></td>
            <td style="vertical-align: middle; text-align: center;width:70%;">
                <img src="data:image/png;base64,{!! DNS1D::getBarcodePNG($data->kode_koli, 'C128', 2, 80) !!}" style="width: 220px;">
            </td>
            <td style="width:15%"></td>
        </tr>
        <tr style="line-height: 25px;">
            <td colspan="3" style="height: 5px;"></td>
        </tr>
        <tr style="line-height: 25px;">
            <td style="width:20%;"></td>
            <td style="width:60%;text-align: center;font-size: 18px;font-weight:bold;">{{ $data->kode_koli }}</td>
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
    </table>
</body>
</html>
