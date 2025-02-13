<!DOCTYPE html>
<html>
<head>
    <title>out material</title>
    <style>
        @page { margin: 10px; }

        body { margin: 10px; 
        font-family: sans-serif;}

        td {
        font-family: Helvetica, Arial, sans-serif;
        }

        tr {
        font-family: Helvetica, Arial, sans-serif;
        }

        .td1{
    border:1px solid black;
    border-top: none;
    border-bottom: none;
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
                    <td width="240px" style="margin-right:-5px;border:none;" align="left">{{ $dhead->tujuan }}</td>
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
            <td align="center" style="border:none;"><h3>Surat jalan</h3></td>
        </tr>
        <tr style="line-height: 8px;">
            <td align="center" style="border:none; font-size:12pt;">{{ $dheader->no_bppb }}</td>
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
            <td> : {{ $dheader->tgl_bppb }}</td>    
        </tr>
        <tr>
            <td>Dok. BC</td>
            <td> : {{ $dheader->dok_bc }}</td>
            <td>Tgl. Dok BC</td>
            <td> : {{ $dheader->tgl_aju }}</td>
        </tr>
        <tr>
            <td>WS Act</td>
            <td> : {{ $dheader->no_ws_aktual }}</td>
            <td>Jenis Trans</td>
            <td> : {{ $dheader->jenis_pengeluaran }}</td>
        </tr>
    </table>
    @endforeach
    <br>
    <table class="main" repeat_header="1" border="1" cellspacing="0" width="100%" 
                 style="border-collapse: collapse; width:100%; font-size: 10px;">
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
                                <td align="left">{{ $ddetail->item_desc }}</td>
                                <td align="right">{{ $ddetail->qty }}</td>
                                <td align="left">{{ $ddetail->unit }}</td>
                                <td align="right">{{ $ddetail->catatan }}</td>
                            </tr>
                    <?php $x++; ?>
                        @endforeach
                        @foreach ($dataSum as $dsum)
                            <tr>
                                <td align="center" colspan="3">Total</td>
                                <td align="right">{{ $dsum->qty_all }}</td>
                                <td align="right"></td>
                                <td align="right"></td>
                            </tr>
                        @endforeach
                    </tbody>
            </table>
            <br>
            <br>
            <table width="100%" style="page-break-inside: avoid;" cellpadding="0" cellspacing="0" border="1">
                <tr>  
      <th style="font-size: 10px;">Created By : </th>
      <th style="font-size: 10px;">Checked By : </th>
      <th style="font-size: 10px;">Approved By : </th>
      <th style="font-size: 10px;">Received By : </th>
    </tr>
    <tr>  
      <td class="td1">&nbsp;</td>
      <td class="td1">&nbsp;</td>
      <td class="td1">&nbsp;</td>
      <td class="td1">&nbsp;</td>           
    </tr>   
    <tr>  
      <td class="td1">&nbsp;</td>
      <td class="td1">&nbsp;</td>
      <td class="td1">&nbsp; </td>
      <td class="td1">&nbsp; </td>      
    </tr>   
    <tr>  
      <td class="td1">&nbsp;</td>
      <td class="td1">&nbsp;</td>
      <td class="td1">&nbsp; </td>
      <td class="td1">&nbsp;</td>
    </tr>   

    <tr style="border-collapse: collapse; border-top: none;"> 
      <td style="font-size:10px;text-align:center">(________________________) </td>
      <td style="font-size:10px;text-align:center">(________________________) </td>
      <td style="font-size:10px;text-align:center">(________________________) </td>
      <td style="font-size:10px;text-align:center">(________________________) </td>
  
    </tr>       
    <tr>  
      <td style="text-align:center;font-size:10px"></td>
      <td style="text-align:center;font-size:10px">Kabag </td>
      <td style="text-align:center;font-size:10px">Direktur </td>
      <td style="text-align:center;font-size:10px"></td>
  
  
    </tr>
            </table>
</body>
</html>
