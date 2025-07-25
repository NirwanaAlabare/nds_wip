<!DOCTYPE html>
<html>
<head>
    <title>IN Fabric</title>
    <style>
        @page { margin: 15px; }

        body { margin: 15px;
        font-family: sans-serif;}

        td {
        font-family: Helvetica, Arial, sans-serif;
        }

        tr {
        font-family: Helvetica, Arial, sans-serif;
        }

        /** {
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
            padding: 1px 1px;
            border: 0px solid;
            width: auto;
        }*/
    </style>
</head>
<body>
    <table width="100%" style="border:none;font-size: 9px;">
                @foreach ($dataHead as $dhead)
                <tr>
                    <td width="400px" style="margin-right:-5px;border:none;font-size: 11px;" align="left">Dicetak : {{ $dhead->tgl_cetak }}</td>
                    <td width="240px" style="margin-right:-5px;border:none;" align="left"></td>
                </tr>
                <tr>
                    <td width="400px" style="margin-right:-5px;border:none;font-size: 12px;" align="left"><b>PT. Nirwana Alabare Garment </b></td>
                    <td width="240px" style="margin-right:-5px;border:none;" align="left">{{ $dhead->tgl_dok }}</td>
                </tr>
                <tr>
                    <td width="400px" style="margin-right:-5px;border:none;" align="left">Jl. Raya Rancaekek - Majalaya No. 289</td>
                    <td width="240px" style="margin-right:-5px;border:none;" align="left">{{ $dhead->supplier }}</td>
                </tr>
                <tr>
                    <td width="400px" style="margin-right:-5px;border:none;" align="left">Desa. Solokan Jeruk</td>
                    <td width="240px" style="margin-right:-5px;border:none;" align="left">{{ $dhead->alamat }}</td>
                </tr>
                <tr>
                    <td width="400px" style="margin-right:-5px;border:none;" align="left">Kec. Solokan Jeruk Bandung Jawa Barat</td>
                    <td width="240px" style="margin-right:-5px;border:none;" align="left"></td>
                </tr>
                <tr>
                    <td width="400px" style="margin-right:-5px;border:none;" align="left">Telp.</td>
                    <td width="240px" style="margin-right:-5px;border:none;" align="left"></td>
                </tr>
                @endforeach
    </table>
    @foreach ($dataHeader as $dheader)
    <table width="100%" style="border:none;">
        <tr style="line-height: 8px;">
            <td align="center" style="border:none;"><h3>Bukti Penerimaan Barang</h3></td>
        </tr>
        <tr style="line-height: 8px;">
            <td align="center" style="border:none; font-size:14pt;">{{ $dheader->no_dok }}</td>
        </tr>
    </table>
    <table width="100%" style="border:none; font-size:9pt">
        <tr>
            <td width="10%"></td>
            <td></td>
            <td width="10%">SJ # / Inv #</td>
            <td> : {{ $dheader->no_invoice }}</td>
        </tr>
        <tr>
            <td>No PO</td>
            <td> : {{ $dheader->no_po }}</td>
            <td>Tgl. BPB</td>
            <td> : {{ $dheader->tgl_dok }}</td>
        </tr>
        <tr>
            <td>Dok. BC</td>
            <td> : {{ $dheader->type_bc }} {{ $dheader->no_aju }}</td>
            <td>Tgl. Dok BC</td>
            <td> : {{ $dheader->tgl_aju }}</td>
        </tr>
    </table>
    @endforeach
    <br>
    <table class="main" repeat_header="1" border="1" cellspacing="0" width="100%"
                 style="border-collapse: collapse; width:100%; font-size: 11px;">
           <thead>
              <tr class="head">
                 <td align="center">No.</td>
                        <td align="center">WS #</td>
                        <td align="center">Nama Barang</td>
                        <td align="center">Jumlah</td>
                        <td align="center">Satuan</td>
                        <td align="center">Keterangan</td>
                    </tr>
                    </thead>

                   <tbody>
                    <?php $x = 1; ?>
                        @foreach ($dataDetail as $ddetail)
                            <tr>
                                <td align="center"><?= $x; ?></td>
                                <td align="left">{{ $ddetail->no_ws }}</td>
                                <td align="left">{{ $ddetail->desc_item }}</td>
                                <td align="right">{{ $ddetail->qty }}</td>
                                <td align="left">{{ $ddetail->unit }}</td>
                                <td align="right">{{ $ddetail->deskripsi }}</td>
                            </tr>
                    <?php $x++; ?>
                        @endforeach
                        @foreach ($dataSum as $dsum)
                            <tr>
                                <td align="center" colspan="3">Total</td>
                                <td align="right">{{ $dsum->qty_all }}</td>
                                <td colspan="2" align="right"></td>
                            </tr>
                        @endforeach
                    </tbody>
            </table>
            <table width="70%" style="border:none;font-size: 11px;">
                <tr>
                    <td width="240px" style="margin-right:-5px;border:none;" align="left">Created By.</td>
                    <td width="240px" style="margin-right:-5px;border:none;" align="left">Approved By.</td>
                    <td width="240px" style="margin-right:-5px;border:none;" align="left">Received By.</td>
                </tr>
                <br>
                <br>
                @foreach ($dataUser as $duser)
                <tr>
                    <td width="240px" style="margin-right:-5px;border:none;" align="left">{{ $duser->created_by }}</td>
                    <td width="240px" style="margin-right:-5px;border:none;" align="left">{{ $duser->approved_by }}</td>
                    <td width="240px" style="margin-right:-5px;border:none;" align="left"></td>
                </tr>
                <tr>
                    <td width="240px" style="margin-right:-5px;border:none;" align="left">{{ $duser->created_at }}</td>
                    <td width="240px" style="margin-right:-5px;border:none;" align="left">{{ $duser->approved_date }}</td>
                    <td width="240px" style="margin-right:-5px;border:none;" align="left"></td>
                </tr>
                @endforeach
            </table>
</body>
</html>
